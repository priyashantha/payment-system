<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPaymentFile;
use App\Models\PaymentUpload;
use Illuminate\Http\Request;

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
            'file' => 'required|mimes:csv,txt|max:204800', // 20MB
        ]);

        $originalName = $request->file('file')->getClientOriginalName();

        $path = $request->file('file')->storeAs('uploads', $originalName, 's3');

        // Create batch-level record
        $upload = PaymentUpload::create([
            'filename' => basename($path),
            'uploaded_by' => $request->user()->id,
            'status' => 'pending',
        ]);

        // Dispatch background job
        ProcessPaymentFile::dispatch($path, $upload->id);

        return response()->json(['message' => 'File uploaded and queued for processing: '. $originalName]);
    }
}
