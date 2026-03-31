<?php

namespace App\Console\Commands;

use App\Http\Controllers\DemoController;
use App\Models\User;
use Illuminate\Console\Command;

class CleanupDemoUsers extends Command
{
    protected $signature = 'demo:cleanup';
    protected $description = 'Müddəti bitmiş demo istifadəçiləri və onların məlumatlarını sil';

    public function handle(): void
    {
        $expired = User::where('is_demo', true)
            ->where('demo_expires_at', '<', now())
            ->get();

        if ($expired->isEmpty()) {
            $this->info('Silinəcək demo istifadəçi yoxdur.');
            return;
        }

        foreach ($expired as $user) {
            DemoController::deleteDemo($user);
            $this->line("Silindi: {$user->email}");
        }

        $this->info("Cəmi {$expired->count()} demo hesab silindi.");
    }
}
