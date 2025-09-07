<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvOrder extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function products()
    {
        return $this->belongsToMany(InvProduct::class, 'inv_order_product', 'order_id', 'product_id')->withPivot('quantity', 'unit_price');
    }

    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    public function updator()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveries()
    {
        return $this->hasMany(InvDelivery::class, 'order_id');
    }
}
