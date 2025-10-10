<?php

namespace App\Jobs;

use App\Mail\InvoiceMail;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SendInvoiceMails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array<int> IDs of invoices to email
     */
    public array $invoiceIds;

    /**
     * Create a new job instance.
     */
    public function __construct(array $invoiceIds)
    {
        $this->invoiceIds = $invoiceIds;
    }

    /**
     * Handle the job.
     */
    public function handle(): void
    {
        $invoices = Invoice::whereIn('id', $this->invoiceIds)
            ->with('customer')
            ->get();

        Log::info("SendInvoiceMails: dispatching " . count($invoices) . " invoice emails...");

        $success = 0;
        $failed  = 0;

        foreach ($invoices as $invoice) {
            $customer = $invoice->customer;
            $email = $customer?->email;

            if (empty($email)) {
                Log::warning("Skipping invoice #{$invoice->id} — missing email");
                continue;
            }

            // Validate email format (RFC-compliant)
            $validator = Validator::make(['email' => $email], [
                'email' => 'email:rfc,dns',
            ]);

            if ($validator->fails()) {
                Log::warning("Skipping invoice #{$invoice->id} — invalid email format: {$email}");
                continue;
            }

            try {
                Mail::to($email)->queue(new InvoiceMail($invoice));
                Log::info("InvoiceMail queued for {$email}");
            } catch (\Throwable $e) {
                Log::error("InvoiceMail failed for {$email}: {$e->getMessage()}");
            }
        }

        Log::info("SendInvoiceMails completed: success={$success}, failed={$failed}");
    }
}
