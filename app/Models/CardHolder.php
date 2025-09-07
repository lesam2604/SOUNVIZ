<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardHolder extends Model
{
    use HasFactory;

    protected $primaryKey = 'card_id';
    protected $guarded = [];
}
