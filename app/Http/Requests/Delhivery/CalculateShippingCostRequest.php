<?php

namespace App\Http\Requests\Delhivery;

use Illuminate\Foundation\Http\FormRequest;

class CalculateShippingCostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'd_pin' => ['required', 'digits:6'],
            'o_pin' => ['required', 'digits:6'],
            'cgm'   => ['required', 'integer', 'min:1'],
            'md'    => ['nullable', 'string', 'in:S,E'],
            'ss'    => ['nullable', 'string'],
            'pt'    => ['nullable', 'string', 'in:Pre-paid,COD'],
            'cod'   => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'd_pin.required' => 'Destination pincode is required.',
            'd_pin.digits'   => 'Destination pincode must be 6 digits.',
            'o_pin.required' => 'Origin pincode is required.',
            'o_pin.digits'   => 'Origin pincode must be 6 digits.',
            'cgm.required'   => 'Charged weight is required.',
            'cgm.integer'    => 'Charged weight must be an integer.',
            'cgm.min'        => 'Charged weight must be greater than 0.',
            'md.in'          => 'Mode must be either S or E.',
            'pt.in'          => 'Payment type must be Pre-paid or COD.',
        ];
    }
}