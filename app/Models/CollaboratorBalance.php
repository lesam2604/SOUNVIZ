<?php
// app/Models/CollaboratorBalance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollaboratorBalance extends Model
{
    protected $fillable = ['user_id', 'balance', 'currency', 'updated_by'];

    protected $casts = [
        'balance' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
