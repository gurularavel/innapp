<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\PatientVisit;
use App\Models\PatientVisitFile;
use App\Models\TreatmentType;
use App\Models\User;
use App\Models\DoctorWorkingHours;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
                'doctor_id'   => $user->id,
                'day_of_week' => $day,
                'start_time'  => '09:00',
                'end_time'    => '18:00',
                'is_working'  => true,
            ]);
        }

        // Visit history with X-ray images
        $this->seedVisitHistory($user, $createdPatients);
    }

    private function seedVisitHistory(User $user, array $patients): void
    {
        // Source X-ray files (stored in storage/app/public/patients/visits/demo/)
        $xrays = [
            'patients/visits/demo/xray1.jpg',
            'patients/visits/demo/xray2.jpg',
            'patients/visits/demo/xray3.jpg',
            'patients/visits/demo/xray4.jpg',
            'patients/visits/demo/xray5.jpg',
        ];

        // Check which demo files actually exist
        $xrays = array_values(array_filter($xrays, fn($p) => Storage::disk('public')->exists($p)));

        if (empty($xrays)) {
            return;
        }

        $visitsData = [
            // Patient 0 – Nigar Əliyeva: 2 visits
            [
                'patient' => 0,
                'offset'  => -60,
                'hour'    => '10:00',
                'title'   => 'Müayinə və rentgen',
                'notes'   => 'İlkin müayinə aparıldı. 16-cı dişdə karies müəyyən edildi. Rentgen şəkli çəkildi, müalicə planı hazırlandı.',
                'xrays'   => [0, 1],
            ],
            [
                'patient' => 0,
                'offset'  => -14,
                'hour'    => '11:00',
                'title'   => 'Plomba',
                'notes'   => '16-cı dişin müalicəsi tamamlandı. Kompozit plomba qoyuldu. Növbəti yoxlama 6 aydan sonra tövsiyə edildi.',
                'xrays'   => [2],
            ],

            // Patient 1 – Tural Həsənov: 3 visits
            [
                'patient' => 1,
                'offset'  => -90,
                'hour'    => '09:30',
                'title'   => 'Rentgen müayinəsi',
                'notes'   => 'Ağrı şikayəti ilə müraciət etdi. Panoramik rentgen çəkildi. 26-cı dişdə pulpit müəyyənləşdirildi.',
                'xrays'   => [1, 2],
            ],
            [
                'patient' => 1,
                'offset'  => -85,
                'hour'    => '10:00',
                'title'   => 'Kanal müalicəsi — 1-ci seans',
                'notes'   => 'Kanal müalicəsinə başlanıldı. Pulpa çıxarıldı, kanallar genişləndirildi. Müvəqqəti doldurucu qoyuldu.',
                'xrays'   => [0],
            ],
            [
                'patient' => 1,
                'offset'  => -75,
                'hour'    => '10:00',
                'title'   => 'Kanal müalicəsi — 2-ci seans',
                'notes'   => 'Kanallar obturatsiya edildi. Daimi plomba qoyuldu. Kontrol rentgen çəkildi — nəticə qənaətbəxşdir.',
                'xrays'   => [3, 4],
            ],

            // Patient 3 – Elnur Quliyev: 2 visits
            [
                'patient' => 3,
                'offset'  => -45,
                'hour'    => '14:00',
                'title'   => 'Diş çəkilməsi',
                'notes'   => '48-ci (ağıl dişi) çəkilmə əməliyyatı həyata keçirildi. Ağrısız anesteziya tətbiq olundu. Resept verildi.',
                'xrays'   => [2, 3],
            ],
            [
                'patient' => 3,
                'offset'  => -10,
                'hour'    => '15:00',
                'title'   => 'Yoxlama müayinəsi',
                'notes'   => 'Post-operativ yoxlama aparıldı. Sağalma prosesi normaldır. Heç bir ağırlaşma müşahidə edilmədi.',
                'xrays'   => [4],
            ],

            // Patient 5 – Rauf İsmayılov: 2 visits
            [
                'patient' => 5,
                'offset'  => -120,
                'hour'    => '09:00',
                'title'   => 'İlkin müayinə + rentgen',
                'notes'   => 'Periapical rentgen çəkildi. 11 və 21-ci dişlərdə diş daşı müəyyənləşdirildi. Parodontoloji müalicə tövsiyə edildi.',
                'xrays'   => [0, 1],
            ],
            [
                'patient' => 5,
                'offset'  => -30,
                'hour'    => '11:30',
                'title'   => 'Diş daşı təmizlənməsi',
                'notes'   => 'Ultrasəs cihazı ilə diş daşı təmizləndi. Ağartma proseduru tətbiq edildi. Evdə qulluq qaydaları izah edildi.',
                'xrays'   => [2],
            ],

            // Patient 7 – Kamran Nəsirov: 1 visit
            [
                'patient' => 7,
                'offset'  => -7,
                'hour'    => '14:00',
                'title'   => 'Bitewing rentgen',
                'notes'   => 'Profilaktik müayinə. Bitewing rentgen çəkildi. Hər iki tərəfdə erkən karies əlamətləri aşkar edildi. Fluorid terapiyası başlandı.',
                'xrays'   => [3, 4],
            ],
        ];

        foreach ($visitsData as $v) {
            $patient = $patients[$v['patient']];
            $visitedAt = now()->addDays($v['offset'])->setTimeFromTimeString($v['hour'] . ':00');

            $visit = PatientVisit::create([
                'patient_id' => $patient->id,
                'doctor_id'  => $user->id,
                'visited_at' => $visitedAt,
                'title'      => $v['title'],
                'notes'      => $v['notes'],
            ]);

            foreach ($v['xrays'] as $xrayIdx) {
                if (!isset($xrays[$xrayIdx])) {
                    continue;
                }

                $srcPath = $xrays[$xrayIdx];
                $ext     = pathinfo($srcPath, PATHINFO_EXTENSION) ?: 'jpg';
                $newName = 'patients/visits/' . Str::random(32) . '.' . $ext;

                // Copy the demo file to a unique path so deletion works correctly
                Storage::disk('public')->copy($srcPath, $newName);

                PatientVisitFile::create([
                    'patient_visit_id' => $visit->id,
                    'file_path'        => $newName,
                    'original_name'    => 'rentgen_' . ($xrayIdx + 1) . '.jpg',
                    'mime_type'        => 'image/jpeg',
                ]);
            }
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
        // Delete visit files from storage before cascading deletes
        foreach ($user->patients as $patient) {
            foreach ($patient->visits as $visit) {
                foreach ($visit->files as $file) {
                    Storage::disk('public')->delete($file->file_path);
                }
            }
        }

        $user->appointments()->delete();
        $user->patients()->delete();
        $user->treatmentTypes()->delete();
        $user->workingHours()->delete();
        $user->breaks()->delete();
        $user->subscriptions()->delete();
        $user->delete();
    }
}
