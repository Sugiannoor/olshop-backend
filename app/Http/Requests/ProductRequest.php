<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Nama produk harus diisi.',
            'name.string' => 'Nama produk harus berupa teks.',
            'name.max' => 'Panjang nama produk tidak boleh lebih dari 255 karakter.',
            'description.required' => 'Deskripsi produk harus diisi.',
            'description.string' => 'Deskripsi produk harus berupa teks.',
            'price.required' => 'Harga produk harus diisi.',
            'price.numeric' => 'Harga produk harus berupa angka.',
            'stock.required' => 'Stok produk harus diisi.',
            'stock.integer' => 'Stok produk harus berupa bilangan bulat.',
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.mimes' => 'Format gambar harus JPEG, PNG, JPG, atau GIF.',
            'image.max' => 'Ukuran gambar tidak boleh lebih dari 2MB.',
        ];
    }
}
