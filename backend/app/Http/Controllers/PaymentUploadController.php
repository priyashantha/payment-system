<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPaymentFile;
use App\Models\PaymentUpload;
use Illuminate\Http\Request;
use League\Csv\Reader;
use League\Csv\Exception as CsvException;

class PaymentUploadController extends Controller
{
    public function index()
    {
        $uploads = PaymentUpload::withCount('payments')
            ->orderByDesc('created_at')
            ->paginate(20, ['id', 'filename', 'status', 'created_at', 'total_records', 'processed_records', 'failed_records']);

        return response()->json($uploads);
    }

    public function show($id, Request $request)
    {
        $upload = PaymentUpload::findOrFail($id);

        $payments = $upload->payments()
            ->with('customer:id,name,customer_code,email')
            ->select('id', 'customer_id', 'amount_original', 'currency', 'status', 'error_message', 'reference_no', 'created_at')
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->customer, function ($q, $customer) {
                $q->whereHas('customer', fn($c) =>
                $c->where('name', 'like', "%$customer%")
                    ->orWhere('customer_code', 'like', "%$customer%")
                    ->orWhere('email', 'like', "%$customer%")
                );
            })
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json([
            'upload' => $upload,
            'payments' => $payments,
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:204800', // 200MB
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();

        // Step 1: Read first line and validate headers
        try {
            $csv = Reader::createFromPath($file->getRealPath(), 'r');
            $csv->setHeaderOffset(0);
            $headers = $csv->getHeader(); // returns an array of header names
        } catch (CsvException $e) {
            return response()->json(['message' => 'Invalid CSV format'], 422);
        }

        $requiredHeaders = [
            'customer_id',
            'customer_name',
            'customer_email',
            'amount',
            'currency',
            'reference_no',
            'date_time',
        ];

        // Step 2: Check missing headers
        $missing = array_diff($requiredHeaders, $headers);
        if (!empty($missing)) {
            return response()->json([
                'message' => 'Missing required headers: ' . implode(', ', $missing),
            ], 422);
        }

        // Step 3: Optional — detect unexpected headers
        $unexpected = array_diff($headers, $requiredHeaders);
        if (!empty($unexpected)) {
            return response()->json([
                'message' => 'Unexpected headers found: ' . implode(', ', $unexpected),
            ], 422);
        }

        // Step 4: Upload to S3
        $path = $file->storeAs('uploads', $originalName, 's3');

        // Step 5: Create PaymentUpload record
        $upload = PaymentUpload::create([
            'filename' => basename($path),
            'uploaded_by' => $request->user()->id,
            'status' => 'pending',
        ]);

        // Step 6: Dispatch background job
        ProcessPaymentFile::dispatch($path, $upload->id);

        return response()->json([
            'message' => 'File uploaded and queued for processing: ' . $originalName,
        ]);
    }
}
