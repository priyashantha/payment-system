<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'uploaded_by',
        'status',
        'total_records',
        'processed_records',
        'failed_records',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
