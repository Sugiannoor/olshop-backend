<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductCollection;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);
        $search = $request->get('search');
        $currentPage = $request->input('page', 1);
        $products = $this->productService->getAll($currentPage, $limit, $search);
        return new ProductCollection($products);
    }

    public function store(ProductRequest $request)
    {
        $product = $this->productService->store($request->validated());
        return new ProductResource($product);
    }

    public function show($product_id)
    {
        $product = Product::findOrFail($product_id);
        return new ProductResource($this->productService->show($product));
    }

    public function update($product_id, ProductRequest $request)
    {
        $product = Product::findOrFail($product_id);
        $updatedProduct = $this->productService->update($product, $request->validated());
        return response()->json(
            [
                'message' => 'Produk berhasil diperbarui',
                'data' => $updatedProduct,
                'status' => 'success',
                'code' => 200
            ],
            200
        );
    }


    public function destroy($product_id)
    {
        $this->productService->destroy($product_id);
        return response()->json(['message' => 'Produk berhasil dihapus', 'status' => 'success', 'code' => 204], 204);
    }
}
