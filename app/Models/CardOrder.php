<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardOrder extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function extra_client()
    {
        return $this->belongsTo(ExtraClient::class);
    }

    public function cards()
    {
        return $this->hasMany(Card::class);
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
