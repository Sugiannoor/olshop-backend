<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartService
{
    /**
     * Get the user's cart with items.
     *
     * @param int $userId
     * @return Cart
     */
    public function getCart(int $userId): Cart
    {
        return Cart::with('items.product')->firstOrCreate(['user_id' => $userId]);
    }

    /**
     * Add or update an item in the cart.
     *
     * @param int $userId
     * @param array $data
     * @return CartItem
     */
    public function addItem(int $userId, array $data): CartItem
    {
        $cart = $this->getCart($userId);
        $cartItem = $cart->items()->where('product_id', $data['product_id'])->first();

        if ($cartItem) {
            $cartItem->quantity += $data['quantity'];
            $cartItem->save();
        } else {
            $cartItem = $cart->items()->create([
                'product_id' => $data['product_id'],
                'quantity' => $data['quantity'],
            ]);
        }

        return $cartItem;
    }

    /**
     * Remove an item from the cart.
     *
     * @param int $cartId
     * @param int $itemId
     * @return void
     */
    public function removeItem(int $cartId, int $itemId): void
    {
        $cart = Cart::findOrFail($cartId);
        $item = $cart->items()->find($itemId);

        if (!$item) {
            throw new ModelNotFoundException('Item tidak ditemukan di keranjang.');
        }
        $item->delete();
    }

    public function removeItemByProductId(int $cartId, int $productId): void
    {
        $cart = Cart::findOrFail($cartId);
        $item = $cart->items()->where('product_id', $productId)->first();

        if (!$item) {
            throw new ModelNotFoundException('Item dengan product_id ini tidak ditemukan di keranjang.');
        }
        $item->delete();
    }

    /**
     * Checkout the cart and create an order.
     *
     * @param int $userId
     * @return array
     */
    public function checkout(int $userId): array
    {
        $cart = $this->getCart($userId);

        if ($cart->items->isEmpty()) {
            throw new \Exception('Cart is empty');
        }

        $totalPrice = $cart->items->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });

        // Simulate order creation (replace with real implementation)
        $order = [
            'user_id' => $userId,
            'total_price' => $totalPrice,
            'status' => 'pending',
            'items' => $cart->items->toArray(),
        ];

        // Clear the cart
        $cart->items()->delete();

        return $order;
    }
}
