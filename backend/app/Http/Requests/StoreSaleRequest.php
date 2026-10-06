<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('receive', $this->attributes->get('membership'));
    }

    protected function prepareForValidation(): void
    {
        if (is_array($this->input('lines'))) {
            $this->merge(['lines' => array_values(array_filter($this->input('lines'), fn (mixed $line): bool => ! is_array($line) || ! empty($line['service_id']) || ! empty($line['quantity'])))]);
        }
    }

    public function rules(): array
    {
        return ['request_key' => ['required', 'uuid'], 'customer_id' => ['required', 'integer'], 'notes' => ['nullable', 'string', 'max:1000'], 'lines' => ['required', 'array', 'min:1', 'max:20'], 'lines.*' => ['required', 'array'], 'lines.*.service_id' => ['required', 'integer'], 'lines.*.quantity' => ['required', 'string', 'regex:/^[0-9]{1,4}(?:\.[0-9]{1,3})?$/']];
    }

    public function messages(): array
    {
        return ['required' => 'completa este dato.', 'integer' => 'elige un registro válido.', 'uuid' => 'el formulario venció. abre una venta nueva.', 'string' => 'escribe un valor válido.', 'array' => 'usa las líneas del formulario.', 'max' => 'superaste el límite permitido.', 'min' => 'añade al menos un servicio.', 'regex' => 'usa una cantidad positiva, con punto y hasta tres decimales.'];
    }
}
