<?php

namespace App\Http\Controllers;

use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FoodController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!$user || !$user->role || $user->role->name !== 'staff') {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Only staff can perform this action.',
                ], Response::HTTP_FORBIDDEN);
            }

            return $next($request);
        })->only(['store', 'update', 'destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Food::all());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            \Log::info('Incoming request data', [
                'requestData' => $request->all(),
            ]);

            $validatedData = $request->validate([
                'name' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'price' => 'nullable|numeric|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('images', 'public');
                $validatedData['image_url'] = asset('storage/' . $imagePath);
            }

            $food = Food::create($validatedData);

            return response()->json([
                'id' => $food->id,
                'name' => $food->name,
                'description' => $food->description,
                'price' => $food->price,
                'image_url' => $food->image_url,
                'created_at' => $food->created_at,
                'updated_at' => $food->updated_at,
            ], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Food $food)
    {
        return response()->json($food);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Food $food)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'price' => 'nullable|numeric|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('images', 'public');
                $validatedData['image_url'] = asset('storage/' . $imagePath);
            }

            // Only update fields that are present in the request
            foreach (['name', 'description', 'price', 'image_url'] as $field) {
                if (array_key_exists($field, $validatedData)) {
                    $food->$field = $validatedData[$field];
                }
            }
            $food->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $food->id,
                    'name' => $food->name,
                    'description' => $food->description,
                    'price' => $food->price,
                    'image_url' => $food->image_url,
                    'created_at' => $food->created_at,
                    'updated_at' => $food->updated_at,
                ],
                'message' => 'Food updated successfully.',
            ], Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update food. Please try again later.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Food $food)
    {
        try {
            $food->delete();

            return response()->json([
                'success' => true,
                'message' => 'Food deleted successfully.',
            ], Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete food. Please try again later.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}