<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => "L'email est requis",
            'email.string' => "L'email n'est pas valide",
            'email.email' => "L'email n'est pas valide",
            'password.required' => "Le mot de passe est requis",
            'password.string' => "Le mot de passe n'est pas valide"
        ];
    }
}
