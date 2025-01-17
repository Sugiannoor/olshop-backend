<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_price',
        'status',
        'address',
        'payment_method',
        'payment_status',
        'payment_reference',
        'merchant_order_id',
        'reference',
        'va_number',
        'qr_string',

    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalPriceAttribute($value)
    {
        return number_format($value, 2, '.', ',');
    }
}
