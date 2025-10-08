<?php

namespace App\Jobs;

use App\Models\PaymentUpload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;

class ProcessPaymentFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $path,
        private int $uploadId
    ) {}

    public function handle(): void
    {
        $upload = PaymentUpload::find($this->uploadId);
        if (!$upload) return;

        // Mark as processing
        $upload->update(['status' => 'processing']);

        // Read from S3 (stream)
        $stream = Storage::disk('s3')->readStream($this->path);
        $csv = Reader::createFromStream($stream)->setHeaderOffset(0);

        // Chunk dispatching
        $chunkSize = 5000;
        $buffer = [];
        $count = 0;

        foreach ($csv->getRecords() as $row) {
            $buffer[] = $row;
            $count++;

            if ($count % $chunkSize === 0) {
                ProcessPaymentChunk::dispatch($buffer, $upload->id);
                $buffer = [];
            }
        }

        if (!empty($buffer)) {
            ProcessPaymentChunk::dispatch($buffer, $upload->id);
        }

        // Update total record count
        $upload->update(['total_records' => $count]);
    }
}
