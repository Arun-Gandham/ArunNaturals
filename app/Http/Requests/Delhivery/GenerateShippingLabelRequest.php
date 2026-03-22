<?php

namespace App\Http\Requests\Delhivery;

use Illuminate\Foundation\Http\FormRequest;

class GenerateShippingLabelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'waybill' => ['required', 'string', 'max:100'],
            'pdf'     => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'waybill.required' => 'Waybill is required.',
        ];
    }
}