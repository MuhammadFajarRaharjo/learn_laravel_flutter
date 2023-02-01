<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ProductGallery extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['url'];

    // untuk menambahkan url di $url
    // config('app.url') adalah url web kita.
    public function getUrl(): Attribute
    {
        return Attribute::make(get: fn ($url) => \config('app.url') . Storage::url($url));
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'products_id');
    }
}
