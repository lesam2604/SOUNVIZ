<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                function ($attribute, $value, $fail) {
                    $exists = User::where('status', '<>', 'rejected')
                        ->where('email', $value)
                        ->exists();

                    if (!$exists) {
                        return $fail("Cet email n'existe pas sur la plateforme");
                    }
                }
            ],
            'token' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (($feedback = comparePasswordResetToken($this->email, $value, 30)) !== true) {
                        return $fail($feedback);
                    }
                }
            ],
            'password' => 'required|string|min:8|confirmed'
        ];
    }

    public function messages()
    {
        return [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.email' => 'Ce champs doit être un email valide',
            'password.min' => 'Le mot de passe doit contenir 8 caractères au minimum',
            'password.confirmed' => "Le mot de passe n'a pas été confirmé",
        ];
    }
}
