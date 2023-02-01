<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDetails extends Model
{
    use HasFactory;

    protected $fillable = ['quantity', 'users_id', 'products_id', 'transactions_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'products_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transctions_id');
    }
}
