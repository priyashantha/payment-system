<?php

namespace App\Console\Commands;

use App\Jobs\ProcessDailyPayoutFile;
use Illuminate\Console\Command;

class DailyPayoutCommand extends Command
{
    protected $signature = 'payouts:run';
    protected $description = 'Run daily payouts and send invoices';

    public function handle()
    {
        $this->info('Dispatching DailyPayoutJob...');
        ProcessDailyPayoutFile::dispatch();
        $this->info('DailyPayoutJob dispatched successfully!');
    }
}
