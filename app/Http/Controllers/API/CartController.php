<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\Request;
use App\Http\Resources\CartItemResource;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Get the user's cart with items.
     */
    public function getCart()
    {
        $user = Auth::user();
        $cart = $this->cartService->getCart($user->id);

        return response()->json([
            'data' => CartItemResource::collection($cart->items),
            'code' => 200,
            'status' => 'success',
        ]);
    }

    public function addItem(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = $this->cartService->addItem($user->id, $data);

        return response()->json([
            'message' => 'Item berhasil ditambahkan ke keranjang',
            'code' => 201,
            'status' => 'success',
            'data' => $cartItem,
        ], 201);
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem($product_id)
    {
        try {
            $user = Auth::user();
            $cart = $this->cartService->getCart($user->id);
            $this->cartService->removeItemByProductId($cart->id, $product_id);

            return response()->json([
                'message' => 'Item berhasil dihapus dari keranjang',
                'code' => 200,
                'status' => 'success',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'message' => 'Item dengan product_id ini tidak ditemukan di keranjang',
                'code' => 404,
                'status' => 'error',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus item',
                'code' => 500,
                'status' => 'error',
            ], 500);
        }
    }

    /**
     * Checkout the cart and create an order.
     */
    public function checkout()
    {
        try {
            $user = Auth::user();
            $order = $this->cartService->checkout($user->id);

            return response()->json([
                'message' => 'Checkout berhasil',
                'data' => $order,
                'code' => 201,
                'status' => 'success',
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
