<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Str;

class OrderService
{
    public function createOrder(User $user, string $address, string $paymentMethod, string $qr_string, string $va_number, string $reference)
    {
        $cart = $user->cart()->with('items.product')->first();
        if (!$cart || $cart->items->isEmpty()) {
            throw new \Exception('Keranjang Anda Masih Kosong');
        }
        $totalPrice = $cart->items->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });
        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => $user->id,
                'address' => $address,
                'payment_method' => $paymentMethod,
                'total_price' => $totalPrice,
                'qr_string' => $qr_string,
                'va_number' => $va_number,
                'reference' => $reference,
                'status' => 'pending',
                'merchant_order_id' => Str::uuid(),
            ]);

            $orderItems = $cart->items->map(function ($item) use ($order) {
                return [
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            $order->items()->insert($orderItems);

            // Kurangi stok produk
            foreach ($cart->items as $item) {
                $product = $item->product;

                if ($product->stock < $item->quantity) {
                    throw new \Exception("Stok untuk produk '{$product->name}' tidak mencukupi.");
                }
                $product->decrement('stock', $item->quantity);
            }

            // Kosongkan keranjang
            $cart->items()->delete();

            DB::commit();

            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
