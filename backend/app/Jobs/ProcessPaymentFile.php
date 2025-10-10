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

        // ----------------------------
        // Count total records
        // ----------------------------
        $countStream = Storage::disk('s3')->readStream($this->path);
        $countCsv = Reader::createFromStream($countStream)->setHeaderOffset(0);
        $totalRecords = iterator_count($countCsv->getRecords());
        fclose($countStream);

        $upload->update([
            'status' => 'processing',
            'total_records' => $totalRecords,
        ]);

        // ----------------------------
        // Process in chunks
        // ----------------------------
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
