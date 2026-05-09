<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LicensesAgreements;
use Illuminate\Support\Facades\Log;

class AutoExpireLicenses extends Command
{
    protected $signature = 'licenses:auto-expire';
    protected $description = 'Automatically update license status to expired if end date is earlier than the current date';

    public function handle() {

        $today = now()->toDateString();
        $count = LicensesAgreements::where('endDate', '<', $today)
            ->where('status', 1)
            ->update(['status' => 4]);

        if ($count > 0) {
            Log::info("AutoExpireLicenses: {$count} licenses updated to Expired.");
            $this->info("{$count} licenses updated to Expired.");
        } else {
            $this->info("No licenses to expire today.");
        }
    }
}
