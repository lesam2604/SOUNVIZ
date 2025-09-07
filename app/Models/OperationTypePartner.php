<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationTypePartner extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function operation_type()
    {
        return $this->belongsTo(OperationType::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
