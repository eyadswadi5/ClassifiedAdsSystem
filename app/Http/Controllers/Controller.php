<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    protected function success(array $data = [], string $message = "job done successfully", int $code = 200): JsonResponse
    {
        return response()->json([
            "success" => true,
            "message" => $message,
            "data" => $data,
        ], $code);
    }

    protected function error(string $message = "", int $code = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            "success" => false,
            "message" => $message,
            $errors
        ], $code);
    }
}
