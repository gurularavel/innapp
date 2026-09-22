<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Doctor;
use App\Http\Controllers\Promoter;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

// Short map URL redirect (public, no auth). Only a map host is ever redirected
// to — the link is printed in SMS under our domain, so it must not become an
// open redirector for whatever a clinic owner typed in.
Route::get('/map/{code}', function (string $code) {
    $clinic = \App\Models\Clinic::where('map_code', $code)->first();
    if ($clinic && \App\Rules\MapUrl::allowed($clinic->map_url)) {
        return redirect()->away($clinic->map_url);
    }
    abort(404);
})->name('map.redirect');

// Browser-sent CSP violation reports (no auth, no CSRF — see bootstrap/app.php).
Route::post(\App\Support\Csp::REPORT_PATH, \App\Http\Controllers\CspReportController::class)
    ->middleware('throttle:60,1')
    ->name('csp.report');

// Home / Landing page
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::post('/inquiry', [InquiryController::class, 'store'])->name('inquiry.store')->middleware('throttle:5,1');

// Demo
Route::get('/demo', [DemoController::class, 'start'])->name('demo.start')->middleware('guest');
Route::post('/demo', [DemoController::class, 'store'])->name('demo.create')
    ->middleware(['guest', 'throttle:10,1']);
Route::post('/demo/exit', [DemoController::class, 'exit'])->name('demo.exit')->middleware('auth');

// Promotor qeydiyyatı (açıq qeydiyyat — kod avtomatik yaradılır)
Route::get('/promoter/register', [\App\Http\Controllers\Auth\PromoterRegistrationController::class, 'create'])
    ->name('promoter.register')->middleware('guest');
Route::post('/promoter/register', [\App\Http\Controllers\Auth\PromoterRegistrationController::class, 'store'])
    ->middleware(['guest', 'throttle:10,1']);

// Auth routes (Breeze)
require __DIR__.'/auth.php';

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:super_admin'])->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', Admin\DoctorController::class)->parameters(['users' => 'doctor']);
    Route::patch('users/{doctor}/toggle-status', [Admin\DoctorController::class, 'toggleStatus'])->name('users.toggle-status');

    Route::resource('admins', Admin\AdminUserController::class)->parameters(['admins' => 'admin'])->except(['show']);

    Route::resource('specialties', Admin\SpecialtyController::class);
    Route::post('specialties/{specialty}/fields', [Admin\SpecialtyController::class, 'addField'])->name('specialties.fields.add');
    Route::patch('specialties/{specialty}/fields/{field}', [Admin\SpecialtyController::class, 'updateField'])->name('specialties.fields.update');
    Route::delete('specialties/{specialty}/fields/{field}', [Admin\SpecialtyController::class, 'removeField'])->name('specialties.fields.remove');

    Route::resource('packages', Admin\PackageController::class);

    Route::get('subscriptions', [Admin\SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('subscriptions/create', [Admin\SubscriptionController::class, 'create'])->name('subscriptions.create');
    Route::post('subscriptions', [Admin\SubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::get('subscriptions/{subscription}/edit', [Admin\SubscriptionController::class, 'edit'])->name('subscriptions.edit');
    Route::put('subscriptions/{subscription}', [Admin\SubscriptionController::class, 'update'])->name('subscriptions.update');
    Route::post('subscriptions/{subscription}/extend', [Admin\SubscriptionController::class, 'extend'])->name('subscriptions.extend');
    Route::delete('subscriptions/{subscription}', [Admin\SubscriptionController::class, 'destroy'])->name('subscriptions.destroy');

    Route::get('payments', [Admin\SubscriptionController::class, 'payments'])->name('payments.index');

    // Promotorlar və promo kodlar
    Route::resource('promoters', Admin\PromoterController::class)->parameters(['promoters' => 'promoter']);
    Route::resource('promo-codes', Admin\PromoCodeController::class)->parameters(['promo-codes' => 'promoCode'])->except(['show']);
    Route::get('payouts', [Admin\PayoutController::class, 'index'])->name('payouts.index');
    Route::patch('payouts/{payout}/paid', [Admin\PayoutController::class, 'markPaid'])->name('payouts.paid');
    Route::patch('payouts/{payout}/reject', [Admin\PayoutController::class, 'reject'])->name('payouts.reject');

    Route::get('sms-logs', [Admin\SmsLogController::class, 'index'])->name('sms-logs.index');
    Route::get('inquiries', [Admin\InquiryController::class, 'index'])->name('inquiries.index');
    Route::get('inquiries/{inquiry}', [Admin\InquiryController::class, 'show'])->name('inquiries.show');
    Route::put('inquiries/{inquiry}', [Admin\InquiryController::class, 'update'])->name('inquiries.update');
    Route::delete('inquiries/{inquiry}', [Admin\InquiryController::class, 'destroy'])->name('inquiries.destroy');

    Route::get('settings/sms-templates', [Admin\SettingController::class, 'smsTemplates'])->name('settings.sms-templates');
    Route::put('settings/sms-templates', [Admin\SettingController::class, 'saveSmsTemplates'])->name('settings.sms-templates.save');

    // Təbrik mesajları: qlobal şablonlar + platforma bayram təqvimi
    Route::get('settings/greetings', [Admin\SettingController::class, 'greetings'])->name('settings.greetings');
    Route::put('settings/greetings', [Admin\SettingController::class, 'saveGreetings'])->name('settings.greetings.save');
    Route::resource('holidays', Admin\HolidayController::class)->except(['show']);

    Route::get('settings/whatsapp', [Admin\SettingController::class, 'whatsapp'])->name('settings.whatsapp');
    Route::put('settings/whatsapp', [Admin\SettingController::class, 'saveWhatsapp'])->name('settings.whatsapp.save');

    Route::get('settings/smtp', [Admin\SettingController::class, 'smtpSettings'])->name('settings.smtp');
    Route::put('settings/smtp', [Admin\SettingController::class, 'saveSmtpSettings'])->name('settings.smtp.save');
    Route::post('settings/smtp/test', [Admin\SettingController::class, 'sendTestMail'])->name('settings.smtp.test');

    Route::get('settings/terms', [Admin\SettingController::class, 'terms'])->name('settings.terms');
    Route::put('settings/terms', [Admin\SettingController::class, 'saveTerms'])->name('settings.terms.save');

    Route::get('settings/security', [Admin\SettingController::class, 'security'])->name('settings.security');
    Route::put('settings/security', [Admin\SettingController::class, 'saveSecurity'])->name('settings.security.save');
    Route::put('settings/csp', [Admin\SettingController::class, 'saveCsp'])->name('settings.csp.save');

    Route::get('settings/promo', [Admin\SettingController::class, 'promoSettings'])->name('settings.promo');
    Route::put('settings/promo', [Admin\SettingController::class, 'savePromoSettings'])->name('settings.promo.save');

    Route::get('cron-log', [Admin\SettingController::class, 'cronLog'])->name('cron-log');

    Route::get('profile', [Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [Admin\ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [Admin\ProfileController::class, 'updatePassword'])->name('profile.password');
});

// Clinic panel — owner, specialists and receptionists share the same panel
Route::prefix('panel')->name('panel.')->middleware(['auth', 'role:owner,doctor,receptionist'])->group(function () {
    Route::get('/dashboard', [Doctor\DashboardController::class, 'index'])->name('dashboard');

    // Staff management (clinic owner only — enforced inside the controller)
    Route::resource('staff', Doctor\StaffController::class)->except(['show']);
    Route::patch('staff/{staff}/toggle-status', [Doctor\StaffController::class, 'toggleStatus'])->name('staff.toggle-status');

    Route::get('patients/search', [Doctor\PatientController::class, 'search'])->name('patients.search');
    Route::resource('patients', Doctor\PatientController::class)->middleware('subscription');

    // Patient uploads are private: streamed here after the clinic check, never from public/storage.
    Route::get('files/patients/{patient}/photo', [Doctor\PatientFileController::class, 'photo'])->name('files.photo');
    Route::get('files/patients/{patient}/fields/{field}', [Doctor\PatientFileController::class, 'customFile'])->name('files.custom');
    Route::get('files/visits/{file}', [Doctor\PatientFileController::class, 'visitFile'])->name('files.visit');

    Route::prefix('patients/{patient}/visits')->name('patients.visits.')->group(function () {
        Route::get('create', [Doctor\PatientVisitController::class, 'create'])->name('create');
        Route::post('/', [Doctor\PatientVisitController::class, 'store'])->name('store');
        Route::get('{visit}/edit', [Doctor\PatientVisitController::class, 'edit'])->name('edit');
        Route::patch('{visit}', [Doctor\PatientVisitController::class, 'update'])->name('update');
        Route::delete('{visit}', [Doctor\PatientVisitController::class, 'destroy'])->name('destroy');
        Route::delete('files/{file}', [Doctor\PatientVisitController::class, 'destroyFile'])->name('files.destroy');
    });

    Route::resource('treatment-types', Doctor\TreatmentTypeController::class);

    Route::get('appointments/available-slots', [Doctor\AppointmentController::class, 'availableSlots'])->name('appointments.available-slots');
    Route::resource('appointments', Doctor\AppointmentController::class)->middleware('subscription');
    Route::patch('appointments/{appointment}/status', [Doctor\AppointmentController::class, 'updateStatus'])->name('appointments.update-status');

    Route::get('/calendar', [Doctor\CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [Doctor\CalendarController::class, 'events'])->name('calendar.events');

    Route::get('/subscription', [Doctor\SubscriptionController::class, 'index'])->name('subscription.index');
    Route::get('/subscription/success', [Doctor\SubscriptionController::class, 'success'])->name('subscription.success');
    Route::get('/subscription/callback', [Doctor\SubscriptionController::class, 'callback'])->name('subscription.callback');
    Route::get('/subscription/checkout/{package}', [Doctor\SubscriptionController::class, 'checkout'])->name('subscription.checkout');
    Route::post('/subscription/checkout/{package}', [Doctor\SubscriptionController::class, 'pay'])->name('subscription.pay');

    Route::get('/reports/revenue', [Doctor\ReportController::class, 'revenue'])->name('reports.revenue');

    Route::get('/profile', [Doctor\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [Doctor\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [Doctor\ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('profile/working-hours', [Doctor\ProfileController::class, 'workingHours'])->name('profile.working-hours');
    Route::put('profile/working-hours', [Doctor\ProfileController::class, 'saveWorkingHours'])->name('profile.working-hours.save');
    Route::get('sms-templates', [Doctor\ProfileController::class, 'smsTemplates'])->name('sms-templates.index');
    Route::put('sms-templates', [Doctor\ProfileController::class, 'saveSmsTemplates'])->name('sms-templates.save');

    // Klinikanın öz WhatsApp bağlantısı (yalnız sahib — kontrollerdə yoxlanılır)
    Route::get('whatsapp', [Doctor\WhatsappController::class, 'edit'])->name('whatsapp.edit');
    Route::put('whatsapp', [Doctor\WhatsappController::class, 'update'])->name('whatsapp.save');
    Route::post('whatsapp/test', [Doctor\WhatsappController::class, 'test'])->name('whatsapp.test')
        ->middleware('throttle:5,10'); // a test button, not a bulk sender
    Route::delete('whatsapp', [Doctor\WhatsappController::class, 'disconnect'])->name('whatsapp.disconnect');

    // Ad günü və bayram təbrikləri
    Route::get('greetings', [Doctor\GreetingController::class, 'index'])->name('greetings.index');
    Route::put('greetings', [Doctor\GreetingController::class, 'save'])->name('greetings.save');
    Route::get('greetings/holidays/create', [Doctor\GreetingController::class, 'createHoliday'])->name('greetings.holidays.create');
    Route::post('greetings/holidays', [Doctor\GreetingController::class, 'storeHoliday'])->name('greetings.holidays.store');
    Route::get('greetings/holidays/{holiday}/edit', [Doctor\GreetingController::class, 'editHoliday'])->name('greetings.holidays.edit');
    Route::put('greetings/holidays/{holiday}', [Doctor\GreetingController::class, 'updateHoliday'])->name('greetings.holidays.update');
    Route::delete('greetings/holidays/{holiday}', [Doctor\GreetingController::class, 'destroyHoliday'])->name('greetings.holidays.destroy');
});

// Promoter routes
Route::prefix('promoter')->name('promoter.')->middleware(['auth', 'role:promoter'])->group(function () {
    Route::get('/dashboard', [Promoter\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/codes', [Promoter\DashboardController::class, 'codes'])->name('codes');
    Route::get('/redemptions', [Promoter\DashboardController::class, 'redemptions'])->name('redemptions');
    Route::get('/payouts', [Promoter\PayoutController::class, 'index'])->name('payouts.index');
    Route::post('/payouts', [Promoter\PayoutController::class, 'store'])->name('payouts.store');
});
