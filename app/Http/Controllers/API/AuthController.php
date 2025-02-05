<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;

class AuthController extends Controller
{
    public function me() : JsonResponse
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json([
                'data' => null,
                'status' => [
                    'message' => 'Unauthenticated.',
                    'code' => 401
                ]
            ], 401);
        }

        return response()->json([
            'data' => UserResource::make($user),
            'status' => [
                'message' => 'User retrieved successfully.',
                'code' => 200
            ]
        ], 200);
    }
    
    public function login(Request $request) : JsonResponse
    {
        $request->validate([
            'nim' => 'required',
            'password' => 'required'
        ]);

        auth()->attempt(['nim' => $request->nim, 'password' => $request->password]);

        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json([
                'data' => null,
                'status' => [
                    'message' => 'Nim or Password wrong.',
                    'code' => 400
                ]
            ], 400);
        }

        try {
            $token = $user->createToken(config('app.name'))->plainTextToken;

            $collection = UserResource::make($user);

            return response()->json([
                'data' => [
                    'user' => $collection,
                    'token' => $token
                ],
                'status' => [
                    'message' => 'User Login successfully.',
                    'code' => 200
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'data' => null,
                'status' => [
                    'message' => $e->getMessage(),
                    'code' => 400
                ]
            ], 400);
        }
    }

    public function logout() : JsonResponse
    {
        try {
            $user = User::find(auth('sanctum')->id());
            $user->tokens()->delete();
        } catch (Exception $e) {
            return response()->json([
                'data' => 'Error on user on auth',
                'status' => [
                    'message' => $e->getMessage(),
                    'code' => 400
                ]
            ], 400);
        }

        return response()->json([
            'data' => null,
            'status' => [
                'message' => 'User Logout successfully.',
                'code' => 200
            ]
        ], 200);
    }
}
