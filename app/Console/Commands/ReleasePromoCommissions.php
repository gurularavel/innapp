<?php

namespace App\Console\Commands;

use App\Models\PromoRedemption;
use Illuminate\Console\Command;

class ReleasePromoCommissions extends Command
{
    protected $signature = 'promo:release-commissions';

    protected $description = 'Hold müddəti bitmiş promo komissiyalarını "pending"dən "available" statusuna keçirir';

    public function handle(): int
    {
        $count = PromoRedemption::where('status', 'pending')
            ->whereNotNull('available_at')
            ->where('available_at', '<=', now())
            ->update(['status' => 'available']);

        $this->info("{$count} komissiya 'available' statusuna keçirildi.");

        return self::SUCCESS;
    }
}
