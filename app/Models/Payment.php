<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'payment_type', 'amount', 'payment_method', 'payment_date', 'proof_file'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}