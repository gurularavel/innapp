<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Doctor;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('doctor.dashboard');
    }
    return redirect()->route('login');
});

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
});

// Doctor routes
Route::prefix('doctor')->name('doctor.')->middleware(['auth', 'role:doctor'])->group(function () {
    Route::get('/dashboard', [Doctor\DashboardController::class, 'index'])->name('dashboard');

    Route::get('patients/search', [Doctor\PatientController::class, 'search'])->name('patients.search');
    Route::resource('patients', Doctor\PatientController::class)->middleware('subscription');

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
});
