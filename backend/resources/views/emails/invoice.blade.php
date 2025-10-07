<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice->invoice_date }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fafafa;
            color: #333;
            padding: 20px;
        }
        .invoice-container {
            background: #fff;
            border: 1px solid #ddd;
            padding: 25px;
            max-width: 700px;
            margin: auto;
            border-radius: 8px;
        }
        h2 {
            color: #222;
            margin-bottom: 0;
        }
        p {
            margin: 4px 0;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #e5e5e5;
            padding: 8px 10px;
            text-align: left;
            font-size: 14px;
        }
        th {
            background-color: #f4f4f4;
            color: #333;
        }
        tfoot td {
            font-weight: bold;
            border-top: 2px solid #ddd;
        }
    </style>
</head>
<body>
<div class="invoice-container">
    <h2>Daily Invoice Summary</h2>

    <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('F d, Y') }}</p>
    <p><strong>Customer ID:</strong> {{ $invoice->customer->customer_code ?? '' }}</p>
    <p><strong>Email:</strong> {{ $invoice->customer->email ?? '' }}</p>

    <table>
        <thead>
        <tr>
            <th>Date</th>
            <th>Reference</th>
            <th>Amount (Original)</th>
            <th>Currency</th>
            <th>Amount (USD)</th>
        </tr>
        </thead>
        <tbody>
        @foreach($invoice->payments()->get() as $payment)
            <tr>
                <td>{{ \Carbon\Carbon::parse($payment->date_time)->format('Y-m-d H:i') }}</td>
                <td>{{ $payment->reference_no }}</td>
                <td>{{ number_format($payment->amount_original, 2) }}</td>
                <td>{{ $payment->currency }}</td>
                <td>{{ number_format($payment->amount_usd, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td colspan="4">Total (USD)</td>
            <td>{{ number_format($invoice->total_amount_usd, 2) }}</td>
        </tr>
        </tfoot>
    </table>

    <p style="margin-top: 25px;">Thank you for your continued business.</p>
</div>
</body>
</html>
