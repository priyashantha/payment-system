<?php

namespace App\Jobs;

use App\Models\{Customer, Payment, PaymentUpload};
use App\Services\ExchangeRateService;
use \App\Services\PaymentRowValidator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessPaymentChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $rates;
    private \Carbon\Carbon $now;

    public function __construct(
        private array $rows,
        private int $uploadId
    ) {}

    public function handle(): void
    {
        $upload = PaymentUpload::find($this->uploadId);
        if (!$upload) return;

        $this->now = now();
        $this->rates = ExchangeRateService::getRates();

        [$customers, $customerCodes] = $this->prepareCustomers($this->rows);

        [$validPayments, $failedPayments, $successCount, $failedCount] =
            $this->processPayments($upload, $customers);

        $this->insertPayments($validPayments, $failedPayments);
        $this->updateProgress($upload, $successCount, $failedCount);
    }

    /* -------------------------------------------------------------------------- */
    /*  Step 1: Prepare Customers (bulk fetch + upsert)                           */
    /* -------------------------------------------------------------------------- */
    private function prepareCustomers(array $rows): array
    {
        $customerRows = [];
        $customerCodes = [];

        foreach ($rows as $row) {
            $code = $row['customer_id'] ?? null;
            if (!$code) continue;

            $customerCodes[] = $code;
            $customerRows[$code] = [
                'customer_code' => $code,
                'email' => $row['customer_email'] ?? null,
                'name'  => $row['customer_name'] ?? 'Unknown',
                'password' => Hash::make('secret'),
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ];
        }

        $customerCodes = array_filter(array_unique($customerCodes));

        // Existing customers
        $existing = Customer::whereIn('customer_code', $customerCodes)
            ->get(['id', 'customer_code'])
            ->keyBy('customer_code');

        // Insert new ones if needed
        $newCodes = array_diff($customerCodes, $existing->keys()->toArray());
        if ($newCodes) {
            $toInsert = array_values(array_intersect_key($customerRows, array_flip($newCodes)));
            Customer::upsert($toInsert, ['customer_code'], ['email', 'name', 'updated_at']);
        }

        // Reload all customers
        $customers = Customer::whereIn('customer_code', $customerCodes)
            ->get(['id', 'customer_code', 'email'])
            ->keyBy('customer_code');

        return [$customers, $customerCodes];
    }

    /* -------------------------------------------------------------------------- */
    /*  Step 2: Process Payments                                                  */
    /* -------------------------------------------------------------------------- */
    private function processPayments(PaymentUpload $upload, $customers): array
    {
        $valid = $failed = [];
        $success = $fail = 0;

        foreach ($this->rows as $row) {
            $code = $row['customer_id'] ?? null;
            $email = $row['customer_email'] ?? null;
            $customer = $customers->get($code);

            try {
                $data = PaymentRowValidator::validate($row);

                if (!$customer) {
                    throw new \Exception("Customer not found after upsert: {$code}");
                }

                $currency = strtoupper($data['currency']);
                $usdRate = $this->rates[$currency] ?? null;
                if (!$usdRate) {
                    throw new \Exception("Currency rate not found for {$currency}");
                }

                $valid[] = [
                    'payment_upload_id' => $upload->id,
                    'customer_id' => $customer->id,
                    'amount_original' => $data['amount'],
                    'currency' => $currency,
                    'amount_usd' => round($data['amount'] / $usdRate, 2),
                    'reference_no' => $data['reference_no'],
                    'date_time' => $data['date_time'],
                    'status' => 'pending',
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ];
                $success++;

//                Log::info('Payment row SUCCESS', [
//                    'customer_id' => $code,
//                    'email' => $email,
//                    'ref' => $data['reference_no'] ?? 'N/A',
//                ]);
            } catch (Throwable $e) {
                $fail++;
                $failed[] = [
                    'payment_upload_id' => $upload->id,
                    'customer_id' => $customer?->id,
                    'amount_original' => $row['amount'] ?? 0,
                    'currency' => $row['currency'] ?? '',
                    'amount_usd' => 0,
                    'reference_no' => $row['reference_no'] ?? uniqid('ERR-'),
                    'date_time' => $row['date_time'] ?? $this->now,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ];

                Log::warning('Payment row FAILED', [
                    'customer_id' => $code,
                    'email' => $email,
                    'ref' => $row['reference_no'] ?? 'N/A',
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [$valid, $failed, $success, $fail];
    }

    /* -------------------------------------------------------------------------- */
    /*  Step 3: Insert Payments                                                   */
    /* -------------------------------------------------------------------------- */
    private function insertPayments(array $valid, array $failed): void
    {
        if ($valid) Payment::insertOrIgnore($valid);
        if ($failed) Payment::insertOrIgnore($failed);
    }

    /* -------------------------------------------------------------------------- */
    /*  Step 4: Update Progress / Completion                                      */
    /* -------------------------------------------------------------------------- */
    private function updateProgress(PaymentUpload $upload, int $success, int $failed): void
    {
        $upload->incrementEach([
            'processed_records' => $success,
            'failed_records' => $failed,
        ]);

        $upload->refresh();

        if ($upload->processed_records + $upload->failed_records >= $upload->total_records) {
            $upload->update([
                'status' => 'completed',
            ]);

            Log::info("Upload #{$upload->id} marked as " . strtoupper($upload->status));
        }

        gc_collect_cycles();
    }
}
