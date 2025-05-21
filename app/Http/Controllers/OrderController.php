<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
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

        $orders = [];
        foreach ($request->items as $item) {
            $food = Food::findOrFail($item['food_id']);
            $totalPrice = $food->price * $item['quantity'];

            $orders[] = Order::create([
                'user_id' => Auth::id(),
                'food_id' => $item['food_id'],
                'quantity' => $item['quantity'],
                'total_price' => $totalPrice,
            ]);
        }

        return response()->json($orders, 201);
    }

    public function index()
    {
        $orders = Order::where('user_id', Auth::id())->get();
        return response()->json($orders);
    }

    public function pay(Request $request, $orderId)
    {
        $user = Auth::user();

        if (empty($user->phone) || empty($user->address)) {
            return response()->json(['error' => 'Please complete your phone and address before proceeding with payment.'], 400);
        }

        $order = Order::findOrFail($orderId);

        $params = [
            'transaction_details' => [
                'order_id' => $order->id,
                'gross_amount' => $order->total_price,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        return response()->json(['snap_token' => $snapToken]);
    }
}
