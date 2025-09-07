<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OperationCancellationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'operation_id',
        'requested_by',
        'approved_by',
        'status',
        'reason',
        'approved_at',
    ];

    public function operation()
    {
        return $this->belongsTo(Operation::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
