<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Invoice;
use App\Mail\InvoiceMail;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessDailyPayoutChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $chunkData;

    public function __construct(array $chunkData)
    {
        $this->chunkData = $chunkData;
    }

    public function handle()
    {
        foreach ($this->chunkData as $group) {
            $customer = Customer::find($group['customer_id']);
            if (!$customer) continue;

            $payments = Payment::whereIn('id', $group['payment_ids'])->get();

            $invoice = Invoice::create([
                'customer_id' => $customer->id,
                'invoice_date' => today(),
                'total_amount_usd' => $payments->sum('amount_usd'),
            ]);

            // Link payments to invoice
            $payments->each->update([
                'status' => 'processed',
                'invoice_id' => $invoice->id,
            ]);

            // send email via queued Mailable
            try {
                Mail::to($customer->email)->queue(new InvoiceMail($invoice));
            } catch (\Exception $e) {
                Log::error("InvoiceMail failed for customer {$customer->id}: {$e->getMessage()}");
            }
        }
    }
}
