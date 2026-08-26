<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Every existing doctor becomes the owner of their own one-person clinic,
     * so nothing changes for them functionally — they simply gain the ability
     * to invite colleagues later.
     */
    private array $tables = [
        'patients',
        'treatment_types',
        'appointments',
        'patient_visits',
        'sms_logs',
        'subscription_payments',
        'doctor_subscriptions',
    ];

    public function up(): void
    {
        $doctors = DB::table('users')
            ->whereIn('role', ['doctor', 'owner'])
            ->whereNull('clinic_id')
            ->get();

        foreach ($doctors as $doctor) {
            $name = trim((string) $doctor->muessise_adi) !== ''
                ? $doctor->muessise_adi
                : trim($doctor->name . ' ' . $doctor->surname);

            $clinicId = DB::table('clinics')->insertGetId([
                'name'                     => $name ?: 'Müəssisə',
                'address'                  => $doctor->muessise_unvani ?? null,
                'phone'                    => $doctor->phone ?? null,
                'map_url'                  => $doctor->muessise_xerite ?? null,
                'map_code'                 => $doctor->muessise_xerite_code ?? null,
                'notify_channel'           => $doctor->notify_channel ?? 'sms',
                'sms_appointment_template' => $doctor->sms_appointment_template ?? null,
                'sms_reminder_template'    => $doctor->sms_reminder_template ?? null,
                'sms_copy_to_self'         => $doctor->sms_copy_to_self ?? 0,
                'owner_id'                 => $doctor->id,
                'is_active'                => $doctor->is_active ?? 1,
                'created_at'               => $doctor->created_at ?? now(),
                'updated_at'               => now(),
            ]);

            DB::table('users')->where('id', $doctor->id)->update([
                'clinic_id'          => $clinicId,
                'role'               => 'owner',
                'takes_appointments' => 1,
                'updated_at'         => now(),
            ]);

            // Everything this doctor owned now belongs to their clinic.
            foreach ($this->tables as $table) {
                DB::table($table)->where('doctor_id', $doctor->id)->update(['clinic_id' => $clinicId]);
            }
        }
    }

    public function down(): void
    {
        DB::table('users')->whereNotNull('clinic_id')->update([
            'role'      => 'doctor',
            'clinic_id' => null,
        ]);

        foreach ($this->tables as $table) {
            DB::table($table)->update(['clinic_id' => null]);
        }

        DB::table('clinics')->delete();
    }
};
