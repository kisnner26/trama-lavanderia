<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($this->input('email')))]);
        }
    }

    public function rules(): array
    {
        return ['email' => ['required', 'email', 'max:254'], 'password' => ['required', 'string', 'max:128']];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'escribe tu correo.',
            'email.email' => 'escribe un correo válido.',
            'email.max' => 'el correo es demasiado largo.',
            'password.required' => 'escribe tu contraseña.',
            'password.string' => 'escribe una contraseña válida.',
            'password.max' => 'la contraseña es demasiado larga.',
        ];
    }
}
