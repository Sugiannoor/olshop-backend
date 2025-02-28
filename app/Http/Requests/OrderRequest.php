<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'address' => 'required|string|max:255',
            'payment_method' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'address.required' => 'Alamat pengiriman harus diisi.',
            'payment_method.required' => 'Metode pembayaran harus diisi.',
        ];
    }
}
