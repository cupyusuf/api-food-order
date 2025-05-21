<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\AuthController;

Route::apiResource('foods', FoodController::class);
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
