<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('receive', $this->attributes->get('membership'));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9][0-9 ()-]{5,28}[0-9]$/'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'escribe el nombre del cliente.',
            'name.string' => 'escribe un nombre válido.',
            'name.max' => 'usa un nombre de hasta 120 caracteres.',
            'phone.string' => 'escribe un teléfono válido.',
            'phone.max' => 'usa un teléfono de hasta 30 caracteres.',
            'phone.regex' => 'usa un teléfono de 7 a 30 caracteres, con números, espacios o guiones.',
            'notes.string' => 'escribe una nota válida.',
            'notes.max' => 'usa una nota de hasta 1000 caracteres.',
        ];
    }
}
