<?php

namespace App\Http\Requests\Delhivery;

use Illuminate\Foundation\Http\FormRequest;

class TrackShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'waybill' => ['required_without:ref_ids', 'nullable', 'string', 'max:100'],
            'ref_ids' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'waybill.required_without' => 'Waybill is required when ref_ids is not provided.',
        ];
    }
}