<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('receive', $this->attributes->get('membership'));
    }

    public function rules(): array
    {
        return ['request_key' => ['required', 'uuid'], 'amount' => ['required', 'string', 'regex:/^[0-9]{1,13}(?:\.[0-9]{1,2})?$/'], 'method' => ['required', 'in:cash,transfer,card'], 'reference' => ['nullable', 'string', 'max:120']];
    }

    public function messages(): array
    {
        return ['required' => 'completa este dato.', 'uuid' => 'el formulario venció. vuelve a abrirlo.', 'string' => 'escribe un valor válido.', 'regex' => 'usa un importe positivo con hasta dos decimales.', 'in' => 'elige efectivo, transferencia o tarjeta.', 'max' => 'usa hasta 120 caracteres.'];
    }
}
