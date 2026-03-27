<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name'   => ['required', 'string', 'max:255'],
            'customer_phone'  => ['nullable', 'string', 'max:20'],
            'customer_email'  => ['nullable', 'email', 'max:255'],
            'address_line1'   => ['required', 'string', 'max:255'],
            'address_line2'   => ['nullable', 'string', 'max:255'],
            'city'            => ['required', 'string', 'max:255'],
            'state'           => ['nullable', 'string', 'max:255'],
            'pincode'         => ['required', 'digits:6'],
            'notes'           => ['nullable', 'string'],
            'cgm'             => ['nullable', 'integer', 'min:1'],
            'box_length'      => ['nullable', 'integer', 'min:1'],
            'box_width'       => ['nullable', 'integer', 'min:1'],
            'box_height'      => ['nullable', 'integer', 'min:1'],
            'shipping_cost'   => ['nullable', 'numeric', 'min:0'],
            'items'           => ['required', 'array', 'min:1'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.sku'          => ['nullable', 'string', 'max:100'],
            'items.*.quantity'     => ['required', 'integer', 'min:1'],
            'items.*.unit_price'   => ['required', 'numeric', 'min:0'],
            'create_in_delhivery'  => ['nullable', 'boolean'],
        ];
    }
}
