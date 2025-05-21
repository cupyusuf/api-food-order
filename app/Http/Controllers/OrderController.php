<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
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
}