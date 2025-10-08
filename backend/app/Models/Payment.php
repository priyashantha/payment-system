<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'status',
        'invoice_id',
        'payment_upload_id',
        'customer_id',
        'amount_original',
        'currency',
        'amount_usd',
        'reference_no',
        'date_time',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function upload()
    {
        return $this->belongsTo(PaymentUpload::class, 'payment_upload_id');
    }
}
