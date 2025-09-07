<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function operations()
    {
        return $this->hasMany(Operation::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
