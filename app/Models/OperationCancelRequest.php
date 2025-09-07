<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationCancelRequest extends Model
{
    protected $fillable = [
        'operation_id', // ID de l'opération concernée
        'user_id',      // ID du collaborateur (utilisez user_id, pas collab_id)
        'partner_id',   // ID du partenaire
        'amount',       // Montant de l'opération
        'status',       // pending, approved, rejected
        'admin_id',     // ID de l'administrateur ayant traité la demande
        'reason',       // facultatif
    ];

    public function operation() {
        return $this->belongsTo(Operation::class);
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin() {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
