<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvDelivery extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function order()
    {
        return $this->belongsTo(InvOrder::class);
    }

    public function products()
    {
        return $this->belongsToMany(InvProduct::class, 'inv_delivery_product', 'delivery_id', 'product_id')->withPivot('quantity');
    }

    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    public function updator()
    {
        return $this->belongsTo(User::class);
    }
}
