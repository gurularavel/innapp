<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Doctor;
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

    Route::resource('doctors', Admin\DoctorController::class);
    Route::patch('doctors/{doctor}/toggle-status', [Admin\DoctorController::class, 'toggleStatus'])->name('doctors.toggle-status');

    Route::resource('specialties', Admin\SpecialtyController::class);

    Route::resource('packages', Admin\PackageController::class);

    Route::get('subscriptions', [Admin\SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('subscriptions/create', [Admin\SubscriptionController::class, 'create'])->name('subscriptions.create');
    Route::post('subscriptions', [Admin\SubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::delete('subscriptions/{subscription}', [Admin\SubscriptionController::class, 'destroy'])->name('subscriptions.destroy');

    Route::get('sms-logs', [Admin\SmsLogController::class, 'index'])->name('sms-logs.index');

    Route::get('settings/sms-templates', [Admin\SettingController::class, 'smsTemplates'])->name('settings.sms-templates');
    Route::put('settings/sms-templates', [Admin\SettingController::class, 'saveSmsTemplates'])->name('settings.sms-templates.save');

    Route::get('settings/smtp', [Admin\SettingController::class, 'smtpSettings'])->name('settings.smtp');
    Route::put('settings/smtp', [Admin\SettingController::class, 'saveSmtpSettings'])->name('settings.smtp.save');

    Route::get('cron-log', [Admin\SettingController::class, 'cronLog'])->name('cron-log');
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
