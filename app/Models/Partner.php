<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $hidden = [
        'balance',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function getMaster()
    {
        if ($this->user->hasRole('partner-master')) {
            return $this;
        }

        return User::role('partner-master')
            ->where('company_id', $this->company_id)
            ->first()
            ->partner;
    }

    public function setHasCommissions($hasCommissions)
    {
        $opTypes = OperationType::whereNotIn('code', ['account_recharge', 'balance_withdrawal'])
            ->get();

        foreach ($opTypes as $opType) {
            $cardTypes = in_array($opType->code, ['card_activation', 'card_deactivation', 'card_recharge'])
                ? CardType::orderBy('id')->pluck('name')->toArray()
                : [null];

            foreach ($cardTypes as $cardType) {
                OperationTypePartner::updateOrCreate([
                    'operation_type_id' => $opType->id,
                    'card_type' => $cardType,
                    'partner_id' => $this->id
                ], [
                    'has_commissions' => $hasCommissions
                ]);
            }
        }
    }

    public function hasCommissions($opTypeId, $cardType = null)
    {
        $opTypeId = is_numeric($opTypeId)
            ? $opTypeId
            : OperationType::firstWhere('code', $opTypeId)->id;

        return OperationTypePartner::where([
            'operation_type_id' => $opTypeId,
            'card_type' => $cardType,
            'partner_id' => $this->id,
            'has_commissions' => true
        ])->exists();
    }

    public function operation_types()
    {
        return $this->hasMany(OperationTypePartner::class)->orderBy('operation_type_id');
    }
}
