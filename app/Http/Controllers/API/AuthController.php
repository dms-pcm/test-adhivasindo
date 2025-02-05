<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function me() : JsonResponse
    {
        $user = auth('sanctum')->user();

        return response()->json([
            'data' => UserResource::make($user),
            'status' => [
                'message' => 'User retrieved successfully.',
                'code' => 200
            ]
        ], 200);
    }
    
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'nim' => 'required',
                'password' => 'required'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'data' => $e->getMessage(),
                'status' => [
                    'message' => 'Something wrong',
                    'code' => 400
                ]
            ], 400);
        }

        if (!Auth::attempt(['nim' => $request->nim, 'password' => $request->password])) {
            return response()->json([
                'data' => null,
                'status' => [
                    'message' => 'NIM or Password is incorrect.',
                    'code' => 400
                ]
            ], 400);
        }

        $user = User::whereNim($request->nim)->first();

        try {
            $token = $user->createToken(config('app.name'))->plainTextToken;

            return response()->json([
                'data' => [
                    'user' => new UserResource($user),
                    'token' => $token
                ],
                'status' => [
                    'message' => 'User logged in successfully.',
                    'code' => 200
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'data' => $e->getMessage(),
                'status' => [
                    'message' => 'Something went wrong while generating the token.',
                    'code' => 500
                ]
            ], 500);
        }
    }

    public function logout() : JsonResponse
    {
        $authUser = auth('sanctum')->user();

        try {
            $user = User::find(auth('sanctum')->id());

            if (!$user) {
                return response()->json([
                    'data' => null,
                    'status' => [
                        'message' => 'User not found',
                        'code' => 400
                    ]
                ], 400);
            }

            $user->tokens()->delete();
        } catch (Exception $e) {
            return response()->json([
                'data' => $e->getMessage(),
                'status' => [
                    'message' => 'Error on user on auth',
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
