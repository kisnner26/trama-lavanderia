<?php

namespace App\Http\Requests;

use App\Enums\BillingUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-catalog', $this->attributes->get('membership'));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'billing_unit' => ['required', Rule::enum(BillingUnit::class)],
            'price' => ['required', 'string', 'regex:/^[0-9]{1,7}(?:\.[0-9]{1,2})?$/'],
            'requires_finish' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'escribe el nombre del servicio.',
            'name.string' => 'escribe un nombre válido.',
            'name.max' => 'usa un nombre de hasta 120 caracteres.',
            'billing_unit.required' => 'elige cómo se cobra el servicio.',
            'billing_unit.enum' => 'elige por pieza o por kilogramo.',
            'price.required' => 'escribe el precio del servicio.',
            'price.string' => 'escribe el precio como un número decimal.',
            'price.regex' => 'usa un precio entre 0 y 9999999.99, con punto y hasta dos decimales.',
            'requires_finish.required' => 'elige la ruta de trabajo.',
            'requires_finish.boolean' => 'elige una ruta con o sin acabado.',
        ];
    }
}
