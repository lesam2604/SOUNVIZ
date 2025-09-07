<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class SendPasswordResetTokenRequest extends FormRequest
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
            ]
        ];
    }

    public function messages()
    {
        return [
            'email.required' => "L'email est requis",
            'email.string' => "L'email n'est pas valide",
            'email.email' => "L'email n'est pas valide",
        ];
    }
}
