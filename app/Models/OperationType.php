<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationType extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'fields' => 'object',
        'fees' => 'object',
        'commissions' => 'object'
    ];

    public function getSortedFieldsAttribute()
    {
        $fields = [];

        foreach ($this->fields as $fieldName => $fieldData) {
            $fields[] = [$fieldName, $fieldData];
        }

        usort($fields, function ($f1, $f2) {
            if ($f1[1]->position < $f2[1]->position) {
                return -1;
            }
            if ($f1[1]->position > $f2[1]->position) {
                return 1;
            }
            return 0;
        });

        return $fields;
    }

    public function toArray()
    {
        $data = parent::toArray();
        $data['sorted_fields'] = $this->sorted_fields;
        return $data;
    }

    public function getFee($amount = null, $cardType = null)
    {
        foreach ($this->fees->$cardType as $step) {
            if ($step->breakpoint === '' || $amount <= floatval($step->breakpoint)) {
                if (is_numeric($step->value)) {
                    $value = intval($step->value);

                    if ($amount === null) {
                        return [0, $value];
                    } else {
                        return [$amount - $value, $value];
                    }
                } else {
                    $fees = intval(($amount * floatval(str_replace(',', '.', $step->value))) / 100);
                    return [$amount - $fees, $fees];
                }
            }
        }

        return [$amount, 0];
    }

    public function getCommission($amount, $cardType = null)
    {
        foreach ($this->commissions->$cardType as $step) {
            if ($step->breakpoint === '' || $amount <= floatval($step->breakpoint)) {
                if (is_numeric($step->value)) {
                    return intval($step->value);
                } else {
                    return intval($amount * floatval(str_replace(',', '.', $step->value)) / 100);
                }
            }
        }

        return 0;
    }

    public function getValues($master, $amount = null, $op = null, $cardType = null)
    {
        $amount = $amount === null ? null : intval($amount);

        if ($master->hasCommissions($this->id, $cardType)) {
            [$newAmount, $fee] = $this->getFee($amount, $cardType);
            $commission = $this->getCommission($amount, $cardType);
        } else {
            if ($this->code === 'card_recharge') {
                $fee = 0;
            } else {
                $fee = $amount <= 500000 ? 100 : 200;
            }

            $newAmount = $amount < $fee ? 0 : $amount - $fee;
            $commission = 0;
        }

        if (
            $op === null && ($newAmount + $fee > $master->balance) ||
            $op !== null && ($newAmount + $fee > $master->balance + $op->amount + $op->fee)
        ) {
            throw new Exception('Votre solde est insuffisant. Veuillez recharger votre compte.');
        }

        return [$newAmount, $fee, $commission];
    }

    public function nextCode()
    {
        $last = Operation::where('operation_type_id', $this->id)->latest('code')->first();
        $lastNum = $last ? intval(explode('-', $last->code)[1]) : 5000;

        for (
            $uniqueCode = $this->prefix . '-' . str_pad(++$lastNum, 6, '0', STR_PAD_LEFT);
            Operation::where('operation_type_id', $this->id)->where('code', $uniqueCode)->exists();
        );

        return $uniqueCode;
    }
}
