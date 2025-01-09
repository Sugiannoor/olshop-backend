<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartRequest extends FormRequest
{
    public function authorize()
    {
        return false;
    }

    public function rules()
    {
        return [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'product_id.required' => 'Produk harus dipilih.',
            'product_id.exists' => 'Produk yang dipilih tidak valid.',
            'quantity.required' => 'Jumlah harus diisi.',
            'quantity.integer' => 'Jumlah harus berupa bilangan bulat.',
            'quantity.min' => 'Jumlah minimal adalah 1.',
        ];
    }
}
