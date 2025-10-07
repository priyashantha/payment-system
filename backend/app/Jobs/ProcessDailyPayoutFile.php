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
        $payments = Payment::where('status', 'pending')
            ->whereDate('created_at', today())
            ->get()
            ->groupBy('customer_id');

        // Flatten into customer groups first
        $groupedData = $payments->map(function ($group) {
            return [
                'customer_id' => $group->first()->customer_id,
                'payment_ids' => $group->pluck('id')->toArray(),
            ];
        })->values(); // ->values() resets keys

        // Now chunk by 200 customers per job
        foreach ($groupedData->chunk(200) as $chunk) {
            $chunkData = $chunk->toArray(); // ensure plain array
            ProcessDailyPayoutChunk::dispatch($chunkData);
            Log::info('Dispatched payout chunk with ' . count($chunkData) . ' customers.');
        }

        Log::info('Payout file job completed successfully — total customers: ' . $groupedData->count());
    }
}
