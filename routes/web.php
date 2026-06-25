<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Doctor;
use App\Http\Controllers\Promoter;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// Short map URL redirect (public, no auth)
Route::get('/map/{code}', function (string $code) {
    $user = \App\Models\User::where('muessise_xerite_code', $code)->first();
    if ($user && $user->muessise_xerite) {
        return redirect($user->muessise_xerite);
    }
    abort(404);
})->name('map.redirect');

// Home / Landing page
Route::get('/', [HomeController::class, 'index'])->name('home');

// Demo
Route::get('/demo', [DemoController::class, 'start'])->name('demo.start')->middleware('guest');
Route::post('/demo/exit', [DemoController::class, 'exit'])->name('demo.exit')->middleware('auth');

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
    Route::delete('subscriptions/{subscription}', [Admin\SubscriptionController::class, 'destroy'])->name('subscriptions.destroy');

    Route::get('payments', [Admin\SubscriptionController::class, 'payments'])->name('payments.index');

    // Promotorlar və promo kodlar
    Route::resource('promoters', Admin\PromoterController::class)->parameters(['promoters' => 'promoter']);
    Route::resource('promo-codes', Admin\PromoCodeController::class)->parameters(['promo-codes' => 'promoCode'])->except(['show']);
    Route::get('payouts', [Admin\PayoutController::class, 'index'])->name('payouts.index');
    Route::patch('payouts/{payout}/paid', [Admin\PayoutController::class, 'markPaid'])->name('payouts.paid');
    Route::patch('payouts/{payout}/reject', [Admin\PayoutController::class, 'reject'])->name('payouts.reject');

    Route::get('sms-logs', [Admin\SmsLogController::class, 'index'])->name('sms-logs.index');

    Route::get('settings/sms-templates', [Admin\SettingController::class, 'smsTemplates'])->name('settings.sms-templates');
    Route::put('settings/sms-templates', [Admin\SettingController::class, 'saveSmsTemplates'])->name('settings.sms-templates.save');

    Route::get('settings/smtp', [Admin\SettingController::class, 'smtpSettings'])->name('settings.smtp');
    Route::put('settings/smtp', [Admin\SettingController::class, 'saveSmtpSettings'])->name('settings.smtp.save');

    Route::get('settings/terms', [Admin\SettingController::class, 'terms'])->name('settings.terms');
    Route::put('settings/terms', [Admin\SettingController::class, 'saveTerms'])->name('settings.terms.save');

    Route::get('cron-log', [Admin\SettingController::class, 'cronLog'])->name('cron-log');

    Route::get('profile', [Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [Admin\ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [Admin\ProfileController::class, 'updatePassword'])->name('profile.password');
});

// Doctor routes
Route::prefix('panel')->name('panel.')->middleware(['auth', 'role:doctor'])->group(function () {
    Route::get('/dashboard', [Doctor\DashboardController::class, 'index'])->name('dashboard');

    Route::get('patients/search', [Doctor\PatientController::class, 'search'])->name('patients.search');
    Route::resource('patients', Doctor\PatientController::class)->middleware('subscription');

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
});

// Promoter routes
Route::prefix('promoter')->name('promoter.')->middleware(['auth', 'role:promoter'])->group(function () {
    Route::get('/dashboard', [Promoter\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/codes', [Promoter\DashboardController::class, 'codes'])->name('codes');
    Route::get('/redemptions', [Promoter\DashboardController::class, 'redemptions'])->name('redemptions');
    Route::get('/payouts', [Promoter\PayoutController::class, 'index'])->name('payouts.index');
    Route::post('/payouts', [Promoter\PayoutController::class, 'store'])->name('payouts.store');
});
