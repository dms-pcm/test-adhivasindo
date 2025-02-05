<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;

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

    public function updateProfil(Request $request) : JsonResponse
    {
        $authUser = auth('sanctum')->user();

        try {
            $request->validate([
                'name' => 'required|min:3',
                'nim' => 'required|min:10|unique:users,nim,' . $authUser->id . ',id',
                'ymd' => ['required', 'regex:/^\d{8}$/'],
                'email' => 'email|unique:users,email,' . $authUser->id . ',id',
            ]);

            $user = User::whereId($authUser->id)->first();

            if (!$user) {
                return response()->json([
                    'data' => null,
                    'status' => [
                        'message' => 'User not found',
                        'code' => 400
                    ]
                ], 400);
            }
            
            $update = $user->update([
                'name' => $request->name,
                'nim' => $request->nim,
                'ymd' => $request->ymd,
                'email' => $request->email,
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

        return response()->json([
            'data' => UserResource::make($update),
            'status' => [
                'message' => 'User updated successfully',
                'code' => 200
            ]
        ], 200);
    }

    public function changePassword(Request $request) : JsonResponse
    {
        $authUser = auth('sanctum')->user();

        try {
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8',
                'confirm_password' => 'same:new_password'
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

        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return response()->json([
                'data' => null,
                'status' => [
                    'message' => 'Current password not valid!',
                    'code' => 400,
                ]
            ],400);
        }

        if(strcmp($request->current_password, $request->new_password) == 0){
            return response()->json([
                'data' => null,
                'status' => [
                    'message' => 'The New Password cannot be the same as your current password.',
                    'code' => 400,
                ]
            ],400);
        }

        $user = User::find(Auth::user()->id);
        $user->password =  Hash::make($request->new_password);
        $user->update();

        return response()->json([
            'data' => [],
            'status' => [
                'message' => 'Password changed successfully',
                'code' => 200
            ]
        ],200);
    }

    public function deleteAccount() : JsonResponse
    {
        $authUser = auth('sanctum')->user();

        $user = User::find(Auth::user()->id);

        if (!$user) {
            return response()->json([
                'data' => null,
                'status' => [
                    'message' => 'User not found',
                    'code' => 400
                ]
            ], 400);
        }

        $user->delete();

        return response()->json([
            'data' => null,
            'status' => [
                'message' => 'Account deleted successfully.',
                'code' => 200
            ]
        ], 200);
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
