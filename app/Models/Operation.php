<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'data' => 'object',
    ];

    public function operationType()
    {
        return $this->belongsTo(OperationType::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class);
    }

    public function withdrawal()
    {
        return $this->belongsTo(Withdrawal::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }



    /*public function user()
   {
    return $this->belongsTo(User::class, 'user_id');
   }*/

}
