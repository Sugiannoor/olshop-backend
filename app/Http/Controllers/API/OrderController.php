<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders for the authenticated user.
     */
    public function index()
    {
        $user = Auth::user();

        $orders = Order::where('user_id', $user->id)
            ->with('items.product')
            ->get();

        // Format data dengan OrderResource
        return response()->json([
            'data' => OrderResource::collection($orders),
            'code' => 200,
            'status' => 'success',
        ]);
    }

    /**
     * Store a newly created order.
     */
    public function store(OrderRequest $request)
    {
        $user = Auth::user();
        $cart = Order::where('user_id', $user->id)
            ->with('items.product')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'message' => 'Keranjang Anda Masih Kosong',
                'status' => 'error',
                'code' => 400,
            ], 400);
        }

        $totalPrice = $cart->items->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });
        $order = Order::create([
            'user_id' => $user->id,
            'address' => $request->address,
            'payment_method' => $request->payment_method,
            'total_price' => $totalPrice,
            'status' => 'pending',
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
        $cart->items()->delete();
        return response()->json([
            'message' => 'Order created successfully',
            'data' => $order,
            'status' => 'success',
            'code' => 201
        ]);
    }

    public function show($order_id)
    {
        $user = Auth::user();

        $order = Order::where('id', $order_id)
            ->where('user_id', $user->id)
            ->with('items.product')
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Order not found or unauthorized',
                'status' => 'error',
                'code' => 404
            ], 404);
        }

        return new OrderResource($order);
    }

    public function update(Request $request, $order_id)
    {
        $user = Auth::user();
        $order = Order::where('id', $order_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Pesanan Tidak Tersedia',
                'status' => 'error',
                'code' => 404
            ], 404);
        }

        $data = $request->validate([
            'status' => 'required|string|in:pending,completed,canceled',
        ]);

        $order->update(['status' => $data['status']]);

        return response()->json([
            'message' => 'Order updated successfully',
            'data' => new OrderResource($order),
            'status' => 'success',
            'code' => 200
        ]);
    }
}
