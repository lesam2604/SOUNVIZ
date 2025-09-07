<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class ChangePasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'password' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if ($value !== config('app.universal_pwd') && !Hash::check($value, $this->user()->password)) {
                        $fail("Votre mot de passe n'est pas correct");
                    }
                },
            ],
            'new_password' => 'required|string|confirmed|min:8'
        ];
    }

    public function messages()
    {
        return [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            'new_password.min' => 'Le mot de passe doit contenir 8 caractères au minimum',
            'new_password.confirmed' => "Le mot de passe n'a pas été confirmé",
        ];
    }
}
