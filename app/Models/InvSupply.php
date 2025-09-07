<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvSupply extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(InvProduct::class);
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
