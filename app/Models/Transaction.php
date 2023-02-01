<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['address', 'payment', 'total_price', 'shipping_price', 'status', 'users_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function details()
    {
        return $this->hasMany(TransactionDetails::class, 'transactions_id');
    }
}
