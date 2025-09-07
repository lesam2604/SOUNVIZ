<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(CardCategory::class, 'card_category_id');
    }

    public function order()
    {
        return $this->belongsTo(CardOrder::class, 'card_order_id');
    }
}
