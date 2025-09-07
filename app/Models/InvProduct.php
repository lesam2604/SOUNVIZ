<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvProduct extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(InvCategory::class);
    }

    public function orders()
    {
        return $this->belongsToMany(InvOrder::class, 'inv_order_product', 'product_id', 'order_id')->withPivot('quantity', 'unit_price');
    }

    public function deliveries()
    {
        return $this->belongsToMany(InvDelivery::class, 'inv_delivery_product', 'product_id', 'delivery_id')->withPivot('quantity');
    }

    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    public function updator()
    {
        return $this->belongsTo(User::class);
    }

    public function supplies()
    {
        return $this->hasMany(InvSupply::class, 'product_id');
    }
}
