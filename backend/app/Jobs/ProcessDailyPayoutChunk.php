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
use Illuminate\Support\Facades\DB;
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
        $customerIds = collect($this->chunkData)->pluck('customer_id')->unique();
        $customers = Customer::whereIn('id', $customerIds)->get()->keyBy('id');

        $invoiceIds = [];

        foreach ($this->chunkData as $group) {
            $customer = $customers->get($group['customer_id']);
            if (!$customer) continue;

            DB::transaction(function () use ($group, $customer, &$invoiceIds) {
                $totalUsd = Payment::whereIn('id', $group['payment_ids'])->sum('amount_usd');

                $invoice = Invoice::create([
                    'customer_id' => $customer->id,
                    'invoice_date' => today(),
                    'total_amount_usd' => $totalUsd,
                ]);

                Payment::whereIn('id', $group['payment_ids'])
                    ->update(['status' => 'processed', 'invoice_id' => $invoice->id]);

                $invoiceIds[] = $invoice->id;
            });
        }

        // Send mails in another async job
        SendInvoiceMails::dispatch($invoiceIds);
    }
}
