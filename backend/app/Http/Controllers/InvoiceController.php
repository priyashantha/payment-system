<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = $this->getFilteredInvoices($request);

        // If it's a customer, restrict
        if ($user instanceof \App\Models\Customer) {
            $query->where('customer_id', $user->id);
        }
        $invoices = $query->paginate(20);

        return response()->json($invoices);
    }

    public function customerInvoices(Request $request)
    {
        return $this->index($request);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        $query = Invoice::with(['customer:id,name,customer_code,email', 'payments'])
            ->where('id', $id);

        // Restrict customer view
        if ($user instanceof \App\Models\Customer) {
            $query->where('customer_id', $user->id);
        }

        $invoice = $query->firstOrFail();

        return response()->json($invoice);
    }

    public function showCustomerInvoice(Request $request, $id)
    {
        return $this->show($request, $id);
    }

    public function preview(Invoice $invoice)
    {
        // Load relationships to avoid N+1
        $invoice->load('customer', 'payments');

        // Render the same Blade view used by emails
        return view('emails.invoice', [
            'invoice' => $invoice,
        ]);
    }

    protected function getFilteredInvoices(Request $request)
    {
        $query = Invoice::query()->with('customer:id,name,customer_code');

        // Optional filters
        if ($request->status) $query->where('status', $request->status);
        if ($request->customer) {
            $query->whereHas('customer', fn($q) =>
            $q->where('name', 'like', "%{$request->customer}%")
                ->orWhere('customer_code', 'like', "%{$request->customer}%")
            );
        }
        if ($request->date_from) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->date_to) $query->whereDate('created_at', '<=', $request->date_to);

        return $query->orderByDesc('created_at');
    }
}
