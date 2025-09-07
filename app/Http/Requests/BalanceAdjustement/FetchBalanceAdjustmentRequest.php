<?php

namespace App\Http\Requests\BalanceAdjustement;

use App\Models\BalanceAdjustment;
use Illuminate\Foundation\Http\FormRequest;

class FetchBalanceAdjustmentRequest extends FormRequest
{
    public function authorize()
    {
        $this->merge(['obj' => BalanceAdjustment::with('partner.user')->findOrFail($this->route('id'))]);

        return $this->user()->hasRole('reviewer') || $this->user()->id === $this->obj->partner->user_id;
    }

    public function rules()
    {
        return [];
    }

    public function messages()
    {
        return [];
    }
}
