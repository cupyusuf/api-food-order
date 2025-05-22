<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Food;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Midtrans\Snap;
use Midtrans\Config as MidtransConfig;

class OrderController extends Controller
{
    public function __construct()
    {
        MidtransConfig::$serverKey = config('midtrans.server_key');
        MidtransConfig::$isProduction = config('midtrans.is_production');
        MidtransConfig::$isSanitized = config('midtrans.is_sanitized');
        MidtransConfig::$is3ds = config('midtrans.is_3ds');
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.food_id' => 'required|exists:food,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $userId = Auth::id();
        $grandTotal = 0;
        $orderItems = [];

        foreach ($request->items as $item) {
            $food = Food::findOrFail($item['food_id']);
            $totalPrice = $food->price * $item['quantity'];
            $grandTotal += $totalPrice;
            $orderItems[] = [
                'food_id' => $item['food_id'],
                'quantity' => $item['quantity'],
                'price' => $food->price,
                'total_price' => $totalPrice,
            ];
        }

        $order = Order::create([
            'user_id' => $userId,
            'total_price' => $grandTotal,
        ]);

        foreach ($orderItems as $item) {
            $item['order_id'] = $order->id;
            \App\Models\OrderItem::create($item);
        }

        return response()->json([
            'order_id' => $order->id,
            'total_price' => $grandTotal,
            'items' => $orderItems
        ], 201);
    }

    public function index()
    {
        $orders = \App\Models\Order::with('items.food')->where('user_id', Auth::id())->get();
        return response()->json($orders);
    }

    public function pay(Request $request, $orderId)
    {
        $user = Auth::user();

        if (empty($user->phone) || empty($user->address)) {
            return response()->json(['error' => 'Please complete your phone and address before proceeding with payment.'], 400);
        }

        $order = Order::findOrFail($orderId);

        $uniqueOrderId = $order->id . '-' . time();
        $params = [
            'transaction_details' => [
                'order_id' => $uniqueOrderId,
                'gross_amount' => (int) $order->total_price,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
        } catch (\Exception $e) {
            Log::error('Midtrans Error: ' . $e->getMessage(), [
                'params' => $params,
                'user' => $user,
                'order' => $order,
            ]);
            return response()->json(['error' => 'Payment processing failed.'], 500);
        }

        return response()->json(['snap_token' => $snapToken]);
    }
}
