<?php

namespace App\Jobs;

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

        Payment::where('status', 'pending')
            ->whereDate('created_at', today())
            ->orderBy('id')
            ->chunkById(50000, function ($paymentsChunk) use (&$totalCustomers) {
                // Group by customer within this chunk
                $grouped = $paymentsChunk->groupBy('customer_id')->map(function ($group) {
                    return [
                        'customer_id' => $group->first()->customer_id,
                        'payment_ids' => $group->pluck('id')->toArray(),
                    ];
                })->values();

                // Further break into 200-customer subchunks
                foreach ($grouped->chunk(200) as $chunk) {
                    ProcessDailyPayoutChunk::dispatch($chunk->toArray());
                    Log::info('Dispatched payout chunk with ' . count($chunk) . ' customers.');
                }

                $totalCustomers += $grouped->count();
            });

        Log::info('Payout job completed successfully — total customers processed: ' . $totalCustomers);
    }
}
