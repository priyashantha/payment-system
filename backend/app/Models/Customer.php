<?php

namespace App\Models;

use App\Models\Auth\AuthenticatableBase;
use Illuminate\Database\Eloquent\Model;

class Customer extends AuthenticatableBase
{
    protected $fillable = ['customer_code', 'name', 'email', 'password'];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
