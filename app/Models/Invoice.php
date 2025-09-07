<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'items' => 'array',
    ];

    public function operationType()
    {
        return $this->belongsTo(OperationType::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}

