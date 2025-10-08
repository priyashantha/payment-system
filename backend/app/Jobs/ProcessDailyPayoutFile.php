<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDailyPayoutFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $totalCustomers = 0;

        Customer::whereHas('payments', function ($q) {
            $q->where('status', 'pending')->whereDate('created_at', today());
        })
            ->orderBy('id')
            ->chunkById(500, function ($customers) use (&$totalCustomers) {
                $chunkData = $customers->map(function ($customer) {
                    $paymentIds = $customer->payments()
                        ->where('status', 'pending')
                        ->whereDate('created_at', today())
                        ->pluck('id')
                        ->toArray();

                    return [
                        'customer_id' => $customer->id,
                        'payment_ids' => $paymentIds,
                    ];
                })->filter(fn ($data) => !empty($data['payment_ids']));

                if ($chunkData->isNotEmpty()) {
                    ProcessDailyPayoutChunk::dispatch($chunkData->values()->toArray());
                    Log::info('Dispatched payout chunk with ' . count($chunkData) . ' customers.');
                }

                $totalCustomers += $chunkData->count();
            });

        Log::info('Payout job completed successfully — total customers processed: ' . $totalCustomers);
    }
}
