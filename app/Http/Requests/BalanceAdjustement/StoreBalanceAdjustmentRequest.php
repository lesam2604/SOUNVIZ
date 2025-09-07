<?php

namespace App\Http\Requests\BalanceAdjustement;

use Illuminate\Foundation\Http\FormRequest;

class StoreBalanceAdjustmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'partner_id' => 'required|numeric|exists:partners,id',
            'amount_to_withdraw' => 'required|numeric',
            'reason' => 'required|string|max:5000',
        ];
    }

    public function messages()
    {
        return [
            '*.required' => 'Ce champs est requis',
            '*.numeric' => 'Ce champs doit être une valeur numérique',
            '*.exists' => "La valeur fournie pour ce champs n'est pas valide",
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            'amount_to_withdraw.min' => 'Ce champs doit être un nombrer entier positif ou zero',
            'reason.max' => 'La longueur maximale pour ce champs est de 5000 caractères',
        ];
    }
}
