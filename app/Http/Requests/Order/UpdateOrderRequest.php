<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name'   => ['sometimes', 'required', 'string', 'max:255'],
            'customer_phone'  => ['sometimes', 'nullable', 'string', 'max:20'],
            'customer_email'  => ['sometimes', 'nullable', 'email', 'max:255'],
            'address_line1'   => ['sometimes', 'required', 'string', 'max:255'],
            'address_line2'   => ['sometimes', 'nullable', 'string', 'max:255'],
            'city'            => ['sometimes', 'required', 'string', 'max:255'],
            'state'           => ['sometimes', 'nullable', 'string', 'max:255'],
            'pincode'         => ['sometimes', 'required', 'digits:6'],
            'notes'           => ['sometimes', 'nullable', 'string'],
            'shipping_cost'   => ['sometimes', 'numeric', 'min:0'],
            'status'          => ['sometimes', 'string', 'in:draft,placed,preparing_for_dispatch,ready_for_pickup,picked_up,in_transit,out_for_delivery,delivered,cancelled'],
            'items'           => ['sometimes', 'array', 'min:1'],
            'items.*.product_name' => ['required_with:items', 'string', 'max:255'],
            'items.*.sku'          => ['nullabl/*  */e', 'string', 'max:100'],
            'items.*.quantity'     => ['required_with:items', 'integer', 'min:1'],
            'items.*.unit_price'   => ['required_with:items', 'numeric', 'min:0'],
        ];
    }
}
