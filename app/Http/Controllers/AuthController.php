<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
        public function login(Request $request) {
        $credintials = $request->validate([
            "email" => "required|email",
            "password" => "required|string"
        ]);

        if (!Auth::attempt($credintials))
            return $this->error("wrong email or password.", 404, []);

        $user = Auth::user();
        $token = $user->createToken("api-token")->plainTextToken;

        return $this->success([
            "user" => $user,
            "token" => $token
        ], "logged in.");
    }

    public function register(Request $request) {
        $credintials = $request->validate([
            "name" => "required|string",
            "email" => "required|email|unique:users,email",
            "password" => "required|string",
            "password_repeat" => "required|string"
        ]);
        if ($request->password !== $request->password_repeat)
            return $this->error("password not match", 400, []);

        try {
            $user = User::create($credintials);
            $token = $user->createToken("api-token")->plainTextToken;

            return $this->success([
                "user" => $user,
                "token" => $token
            ], "user created successfully.", 201);
        } catch (QueryException $e) {
            return $this->error("failed to create user.", 500, [
                "errors" => [
                    "database-error" => $e->getMessage()
                    ]
                ]);
        }
    }

    public function logout(Request $request) {
        $request->user()->tokens()->delete();
        return $this->success();
    }
}
