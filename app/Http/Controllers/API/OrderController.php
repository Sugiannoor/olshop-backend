<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Services\DuitkuService;
use App\Services\OrderService;
use DB;

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

        return response()->json([
            'data' => OrderResource::collection($orders),
            'code' => 200,
            'status' => 'success',
        ]);
    }

    /**
     * Store a newly created order.
     */
    // public function store(OrderRequest $request)
    // {
    //     $user = Auth::user();

    //     // Ambil keranjang pengguna dengan relasi items dan product
    //     $cart = $user->cart()->with('items.product')->first();

    //     if (!$cart || $cart->items->isEmpty()) {
    //         return response()->json([
    //             'message' => 'Keranjang Anda Masih Kosong',
    //             'status' => 'error',
    //             'code' => 400,
    //         ], 400);
    //     }

    //     $totalPrice = $cart->items->sum(function ($item) {
    //         return $item->quantity * $item->product->price;
    //     });

    //     // Transaksi database
    //     DB::beginTransaction();
    //     try {
    //         // Simpan pesanan
    //         $order = Order::create([
    //             'user_id' => $user->id,
    //             'address' => $request->address,
    //             'payment_method' => $request->payment_method,
    //             'total_price' => $totalPrice,
    //             'status' => 'pending',
    //         ]);

    //         // Simpan item pesanan
    //         $orderItems = $cart->items->map(function ($item) use ($order) {
    //             return [
    //                 'order_id' => $order->id,
    //                 'product_id' => $item->product_id,
    //                 'quantity' => $item->quantity,
    //                 'price' => $item->product->price,
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ];
    //         })->toArray();

    //         $order->items()->insert($orderItems);

    //         // Kurangi stok produk
    //         foreach ($cart->items as $item) {
    //             $product = $item->product;

    //             if ($product->stock < $item->quantity) {
    //                 throw new \Exception("Stok untuk produk '{$product->name}' tidak mencukupi.");
    //             }

    //             $product->decrement('stock', $item->quantity);
    //         }

    //         // Kosongkan keranjang
    //         $cart->items()->delete();

    //         DB::commit();

    //         return response()->json([
    //             'message' => 'Pesanan berhasil dibuat',
    //             'data' => $order->load('items.product'),
    //             'status' => 'success',
    //             'code' => 201,
    //         ], 201);
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'message' => 'Terjadi kesalahan saat membuat pesanan',
    //             'error' => $e->getMessage(),
    //             'status' => 'error',
    //             'code' => 500,
    //         ], 500);
    //     }
    // }

    public function store(OrderRequest $request, OrderService $orderService)
    {
        $user = Auth::user();
        try {
            $order = $orderService->createOrder($user, $request->address, $request->payment_method, $request->qr_string, $request->va_number, $request->reference);
            return response()->json([
                'message' => 'Pesanan berhasil dibuat',
                'data' => $order->load('items.product'),
                'status' => 'success',
                'code' => 201,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat membuat pesanan',
                'error' => $e->getMessage(),
                'status' => 'error',
                'code' => 500,
            ], 500);
        }
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

    public function checkTransaction(Request $request, DuitkuService $duitkuService)
    {
        $request->validate([
            'merchantOrderId' => 'required|string',
        ]);

        $merchantOrderId = $request->input('merchantOrderId');
        try {
            $data = $duitkuService->checkTransactionStatus($merchantOrderId);
            return response()->json([
                'message' => 'Cek transaksi berhasil',
                'data' => $data,
                'status' => 'success',
                'code' => 200,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal cek transaksi',
                'error' => $e->getMessage(),
                'status' => 'error',
                'code' => 500,
            ], 500);
        }
    }
}
