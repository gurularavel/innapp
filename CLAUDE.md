# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

**InnApp** — a multi-tenant appointment/CRM SaaS for service businesses in Azerbaijan (Laravel 12, MySQL). It began as a dental-clinic app and was generalised in May 2026: UI copy says "müəssisə / mütəxəssis / randevu", not "klinika / həkim / stomatoloji". Only the demo seeder still contains dental sample data.

All user-facing text is **Azerbaijani**. Code comments and identifiers are English.

## Commands

```bash
php artisan serve                     # dev server
composer dev                          # serve + queue listener + pail + vite together

php artisan test                      # full suite
php artisan test --filter=GreetingTest            # one class
php artisan test --filter=test_patient_without_a_phone  # one test
composer test                         # config:clear + test

php artisan migrate                   # local db is MySQL `stom`; tests use in-memory sqlite
php artisan migrate:status
```

Scheduled work (see `routes/console.php`; the host runs `schedule:run`):

```bash
php artisan reminders:send                        # appointment reminders, every 15 min
php artisan greetings:send --force --dry-run      # birthday/holiday greetings, hourly
php artisan demo:cleanup                          # expired demo accounts, hourly
php artisan promo:release-commissions             # pending → available, hourly
php artisan sms:test / whatsapp:test              # manual gateway checks
```

**Do not bother with `npm run build` for app UI.** Every real layout (`layouts/admin`, `doctor`, `promoter`, `public`) loads Bootstrap 5, Bootstrap Icons, FullCalendar, flatpickr, tom-select and IMask from **jsDelivr CDN**. Vite/Tailwind only serve the leftover Breeze layouts (`layouts/app`, `layouts/guest`), and the npm dependencies are vestigial.

Deployment is FTP-on-push via `.github/workflows/main.yml` — **every push to any branch deploys to production**. The repo root `.htaccess` rewrites into `public/`.

## Architecture

### The clinic is the tenant

`Clinic` owns everything. A solo specialist is simply a clinic with one member, so there is no separate "single user" mode and billing/scoping/permissions stay uniform.

- Tenant tables all carry `clinic_id`: `patients`, `treatment_types`, `appointments`, `patient_visits`, `sms_logs`, `subscription_payments`, `doctor_subscriptions`.
- **`doctor_id` is not ownership.** Its meaning is per-table (assigned staff / note author / who registered the record) and it is `nullOnDelete` — removing a staff member must never delete the clinic's history. Scope queries by `clinic_id`, never by `doctor_id`.
- Convenience relations on `User` are clinic-scoped by design: `$user->patients()`, `->treatmentTypes()`, `->clinicAppointments()`, `->subscriptions()` all key off `clinic_id`. Only `$user->appointments()` is personal (`doctor_id`).

### Roles and panels

`users.role`: `super_admin`, `owner`, `doctor`, `receptionist`, `promoter`. The three middle ones are "clinic members" (`User::isClinicMember()`) and share one panel.

| Prefix | Route names | Middleware | Views |
|---|---|---|---|
| `/admin` | `admin.*` | `role:super_admin` | `admin/` |
| `/panel` | `panel.*` | `role:owner,doctor,receptionist` | **`doctor/`** |
| `/promoter` | `promoter.*` | `role:promoter` | `promoter/` |

Note the mismatch: panel routes are `panel.*` but their views still live under `resources/views/doctor/`. Same for `Http/Controllers/Doctor/`.

Visibility rule repeated across Dashboard/Calendar/Appointments: **owners and receptionists see the whole clinic; a specialist sees only their own diary.** Owner-only actions (staff, billing, messaging settings) are enforced with `canManageClinic()` inside controllers, not by route middleware.

### Seat billing

One active package: `price_per_seat` (20 ₼) × every active account. `DoctorSubscription.seats` is what was paid for; `Clinic::usedSeats()` is what exists. `CheckSubscription` middleware (applied to `patients` and `appointments` routes) blocks the panel when there is no active subscription **or** when `seatsExceeded()`. Demo users bypass it.

Payment flow: `Doctor\SubscriptionController::pay()` → `KapitalBankService::createOrder()` → redirect to HPP → `callback()` re-verifies status through the API (never trusts the callback's `STATUS` param) → activate subscription → record promoter commission.

### Messaging pipeline

```
caller → NotificationService (picks channels for the clinic)
           ├── SmsService      → Posta Güvercini API  ─┐
           └── WhatsAppService → Meta Cloud API        ├→ sms_logs (channel column)
                    ↑                                  ┘
              MessageBuilder (resolves template + fills placeholders)
```

- Channel is a **clinic-wide** setting (`clinics.notify_channel`: `sms` / `whatsapp` / `both`). If the admin has not configured WhatsApp, `NotificationService` silently drops it and falls back to SMS — a preference must never silence notifications.
- **Template priority is always: clinic's own text » admin default in `settings` » hardcoded fallback in `MessageBuilder::GLOBAL_DEFAULTS`.** Follow this for any new message type.
- Placeholders: appointments use `{ad_soyad} {xidmet} {mutexessis} {tarix} {saat} {muessise} {xerite}`; greetings use their own set (`{ad} {yas} {bayram}` …), declared as constants on `MessageBuilder`. `{xerite}` resolves to a short link `/map/{code}` that redirects to `clinics.map_url`.
- Templates are capped at **160 characters** everywhere, with a live counter and placeholder-insert buttons in the UI.
- WhatsApp only delivers free-form text inside Meta's 24-hour window; outside it an approved **template name** (configured per message type in Ayarlar » WhatsApp) is required. Both drivers fall back to a log-only mode when unconfigured, so nothing throws in dev.
- `sms_logs` holds WhatsApp messages too — the name is legacy. `receiver_id` is the gateway's message id.

### Greetings (birthday / holiday)

`GreetingService` + `greetings:send`. Opt-in per clinic (`birthday_greetings_enabled`, `holiday_greetings_enabled` — both default false). The scheduler ticks hourly; the command acts only at `Setting::get('greetings_send_hour')`.

Holidays are two-level: rows with `holidays.clinic_id = null` are the admin's platform-wide calendar (every clinic may disable or reword one via `clinic_holiday_settings`), rows with a `clinic_id` belong to that clinic alone. `year = null` means "repeats annually"; a filled `year` pins a moving date (Ramazan/Qurban) to one year.

Double-send protection is `sms_logs.reference` (`birthday:Y-m-d`, `holiday:{id}:{year}`) + `patient_id`. **An attempt counts whether or not it succeeded**, so a bad number cannot cause a retry storm. Demo clinics and clinics without an active subscription never send.

### Promoter / promo codes

A customer binds a promo code at registration (`users.signup_promo_code_id`). The discount applies only to their **first** payment, but the commission is **lifetime** — every later renewal credits the promoter from that original code, even after it expires. `promo_codes.used_count` counts customers, not payments. Commissions sit `pending` for 14 days (refund risk) before `promo:release-commissions` makes them `available`.

### Other subsystems

- **Settings**: key/value table via `Setting::get()/set()` with a 10-minute cache. Secrets (WhatsApp token, SMTP password) are stored `encrypt()`ed. New admin-tunable values belong here, not in `.env`.
- **Dynamic patient fields**: `specialties` → `specialty_fields` → `patient_field_values`. `Specialty::resolvedFields()` merges built-in patient columns (`SpecialtyField::coreFields()`) with admin-configured extras; the patient form is rendered from that.
- **Demo mode**: `/demo` creates a throwaway clinic + owner + realistic seed data, expiring in 2 hours. `CheckDemoExpiry` (in the `web` group) logs the user out and deletes everything. Demo phone numbers look real — **never let demo data reach a gateway**.
- **Logging**: dedicated channels `sms` (`storage/logs/sms.log`) and `cron` (daily, 30 days). The admin "Cron / SMS Test" screen tails `cron.log`.

## Conventions

- Controllers validate inline with `$request->validate()`; there are no form request classes outside `Auth`. Shared validation goes in a trait under `Http/Controllers/Concerns/`.
- Blade partials are `_name.blade.php` in the section folder; `resources/views/holidays/` holds fragments shared by both panels.
- Flash messages use `success` / `error` / `warning` session keys; layouts render them.
- New scheduled commands log to `Log::channel('cron')` with a started/finished pair.

## Known rough edges

- `php artisan test` has **10 pre-existing failures** — leftover Breeze scaffolding tests (`ProfileTest`, `Auth\*`, `Feature\ExampleTest`) that hit removed routes (`dashboard`, `/profile`) or lack `RefreshDatabase`. They are unrelated to app code; don't treat them as regressions.
- `Doctor\AppointmentController` builds its conflict check with raw `DATE_ADD(... INTERVAL ...)`, which is **MySQL-only** and will fail under the sqlite test connection.
- `Doctor\SubscriptionController::callback()` notifies a hardcoded admin phone number; it belongs in `settings`.
- Migrations that alter enums guard on `Schema::getConnection()->getDriverName() === 'mysql'` because sqlite has no enum — follow that pattern when extending one.
