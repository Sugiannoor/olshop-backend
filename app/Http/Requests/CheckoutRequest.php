<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize()
    {
        return false;
    }

    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'Sepertinya Anda Belum Login',
            'user_id.exists' => 'Sepertinya Anda Belum Login',
        ];
    }
}
