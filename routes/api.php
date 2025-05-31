<?php

use App\Http\Controllers\Api\AdController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(["middleware" => "auth:sanctum"], function () {
    Route::apiResource("ads", AdController::class);
    Route::post("/ads/{id}/status", [AdController::class, "updateStatus"])
        ->whereNumber("id");
    
    Route::apiResource("categories", CategoryController::class);
    Route::apiResource("ads.reviews", ReviewController::class)->shallow();
});

Route::post("/login", [AuthController::class, "login"]);
Route::post("/sign-up", [AuthController::class, "register"]);
Route::post("/logout", [AuthController::class, "logout"]);