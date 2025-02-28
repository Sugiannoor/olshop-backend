<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;

class ProductService
{

    public function getAll($currentPage = 1, $limit = 10, $search = null)
    {
        $query = Product::query();

        if ($search) {
            $query->where('name', 'LIKE', '%' . $search . '%')
                ->orWhere('description', 'LIKE', '%' . $search . '%');
        }
        $totalData = $query->count();
        $perPage = 10;
        $totalPages = ceil($totalData / $perPage);

        if ($currentPage > $totalPages) {
            $currentPage = 1;
        }

        return $query->paginate($perPage, ['*'], 'page', $currentPage);
    }
    public function store(array $data)
    {
        if (isset($data['image'])) {
            $data['image'] = $data['image']->store('products', 'public');
        }
        return Product::create($data);
    }

    public function show(Product $product)
    {
        return $product;
    }

    /**
     * Update an existing product.
     */
    public function update(Product $product, array $data)
    {
        // Handle image upload if exists
        if (isset($data['image'])) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $data['image']->store('products', 'public');
        }

        $product->update($data);
        return $product;
    }

    /**
     * Delete a product.
     */
    public function destroy($product_id)
    {
        try {
            $product = Product::findOrFail($product_id);

            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->delete();

            return [
                'message' => 'Produk berhasil dihapus',
                'status' => 'success',
                'code' => 204,
            ];
        } catch (ModelNotFoundException $e) {
            // Tangani jika produk tidak ditemukan
            return [

                'message' => 'Produk tidak ditemukan',
                'status' => 'error',
                'code' => 404,
            ];
        }
    }
}
