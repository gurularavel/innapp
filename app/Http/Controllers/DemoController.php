<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\TreatmentType;
use App\Models\User;
use App\Models\DoctorWorkingHours;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoController extends Controller
{
    public function start()
    {
        // Clean up expired demos first
        $this->cleanupExpired();

        $user = User::create([
            'name'             => 'Demo',
            'surname'          => 'İstifadəçi',
            'email'            => 'demo_' . Str::random(10) . '@demo.innapp.az',
            'password'         => Hash::make(Str::random(32)),
            'role'             => 'doctor',
            'is_active'        => true,
            'is_demo'          => true,
            'demo_expires_at'  => now()->addHours(2),
            'muessise_adi'     => 'Demo Klinika',
        ]);

        $this->seedDemoData($user);

        Auth::login($user);
        session(['demo_user_id' => $user->id]);

        return redirect()->route('panel.dashboard')
            ->with('success', 'Demo rejimə xoş gəldiniz! 2 saat ərzində bütün funksiyaları sınaya bilərsiniz.');
    }

    public function exit(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($user?->is_demo) {
            self::deleteDemo($user);
        }

        return redirect()->route('register');
    }

    private function seedDemoData(User $user): void
    {
        // Treatment types
        $types = [
            ['name' => 'Müayinə',          'price' => 20,  'duration_minutes' => 20, 'color' => '#4a6fa5'],
            ['name' => 'Plomba',            'price' => 60,  'duration_minutes' => 45, 'color' => '#2d8b8b'],
            ['name' => 'Kanal müalicəsi',   'price' => 120, 'duration_minutes' => 60, 'color' => '#e76f51'],
            ['name' => 'Diş çəkilməsi',     'price' => 50,  'duration_minutes' => 30, 'color' => '#c1666b'],
            ['name' => 'Ağartma',           'price' => 180, 'duration_minutes' => 90, 'color' => '#a8dadc'],
        ];

        $createdTypes = [];
        foreach ($types as $t) {
            $createdTypes[] = TreatmentType::create(array_merge($t, ['doctor_id' => $user->id]));
        }

        // Patients
        $patients = [
            ['name' => 'Nigar',   'surname' => 'Əliyeva',     'phone' => '+994501234501', 'gender' => 'female', 'birth_date' => '1990-05-12'],
            ['name' => 'Tural',   'surname' => 'Həsənov',     'phone' => '+994501234502', 'gender' => 'male',   'birth_date' => '1985-03-22'],
            ['name' => 'Sevinc',  'surname' => 'Məmmədli',    'phone' => '+994501234503', 'gender' => 'female', 'birth_date' => '1995-11-08'],
            ['name' => 'Elnur',   'surname' => 'Quliyev',     'phone' => '+994501234504', 'gender' => 'male',   'birth_date' => '1988-07-30'],
            ['name' => 'Aytən',   'surname' => 'Hüseynova',   'phone' => '+994501234505', 'gender' => 'female', 'birth_date' => '1992-01-15'],
            ['name' => 'Rauf',    'surname' => 'İsmayılov',   'phone' => '+994501234506', 'gender' => 'male',   'birth_date' => '1979-09-04'],
            ['name' => 'Günel',   'surname' => 'Babayeva',    'phone' => '+994501234507', 'gender' => 'female', 'birth_date' => '1998-06-20'],
            ['name' => 'Kamran',  'surname' => 'Nəsirov',     'phone' => '+994501234508', 'gender' => 'male',   'birth_date' => '1983-12-17'],
            ['name' => 'Leyla',   'surname' => 'Rzayeva',     'phone' => '+994501234509', 'gender' => 'female', 'birth_date' => '2000-04-03'],
            ['name' => 'Orxan',   'surname' => 'Cəfərov',     'phone' => '+994501234510', 'gender' => 'male',   'birth_date' => '1975-08-25'],
        ];

        $createdPatients = [];
        foreach ($patients as $p) {
            $createdPatients[] = Patient::create(array_merge($p, ['doctor_id' => $user->id]));
        }

        // Appointments: today, yesterday (completed), tomorrow, next week
        $appointments = [
            // Today
            ['patient' => 0, 'type' => 0, 'offset' => 0, 'hour' => '09:00', 'status' => 'confirmed'],
            ['patient' => 1, 'type' => 1, 'offset' => 0, 'hour' => '10:30', 'status' => 'confirmed'],
            ['patient' => 2, 'type' => 2, 'offset' => 0, 'hour' => '12:00', 'status' => 'pending'],
            ['patient' => 3, 'type' => 3, 'offset' => 0, 'hour' => '14:00', 'status' => 'pending'],
            ['patient' => 4, 'type' => 4, 'offset' => 0, 'hour' => '15:30', 'status' => 'confirmed'],
            // Yesterday (completed)
            ['patient' => 5, 'type' => 0, 'offset' => -1, 'hour' => '09:00', 'status' => 'completed'],
            ['patient' => 6, 'type' => 1, 'offset' => -1, 'hour' => '11:00', 'status' => 'completed'],
            ['patient' => 7, 'type' => 2, 'offset' => -1, 'hour' => '14:00', 'status' => 'completed'],
            // Tomorrow
            ['patient' => 8, 'type' => 3, 'offset' => 1, 'hour' => '10:00', 'status' => 'confirmed'],
            ['patient' => 9, 'type' => 4, 'offset' => 1, 'hour' => '13:00', 'status' => 'pending'],
            // Day after tomorrow
            ['patient' => 0, 'type' => 1, 'offset' => 2, 'hour' => '11:00', 'status' => 'pending'],
            ['patient' => 2, 'type' => 2, 'offset' => 3, 'hour' => '09:30', 'status' => 'pending'],
        ];

        foreach ($appointments as $a) {
            $date = now()->addDays($a['offset'])->format('Y-m-d');
            Appointment::create([
                'doctor_id'         => $user->id,
                'patient_id'        => $createdPatients[$a['patient']]->id,
                'treatment_type_id' => $createdTypes[$a['type']]->id,
                'scheduled_at'      => $date . ' ' . $a['hour'] . ':00',
                'duration_minutes'  => $createdTypes[$a['type']]->duration_minutes,
                'status'            => $a['status'],
                'notes'             => null,
                'reminder_sent'     => false,
            ]);
        }

        // Working hours: Mon–Fri 09:00–18:00
        foreach (range(1, 5) as $day) {
            DoctorWorkingHours::create([
                'doctor_id'  => $user->id,
                'day_of_week' => $day,
                'start_time' => '09:00',
                'end_time'   => '18:00',
                'is_working' => true,
            ]);
        }
    }

    private function cleanupExpired(): void
    {
        $expired = User::where('is_demo', true)
            ->where('demo_expires_at', '<', now())
            ->get();

        foreach ($expired as $u) {
            $this->deleteDemo($u);
        }
    }

    public static function deleteDemo(User $user): void
    {
        $user->appointments()->delete();
        $user->patients()->delete();
        $user->treatmentTypes()->delete();
        $user->workingHours()->delete();
        $user->breaks()->delete();
        $user->subscriptions()->delete();
        $user->delete();
    }
}
