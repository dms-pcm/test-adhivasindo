<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Http\Resources\UserResource;
use App\Http\Traits\SearchContent;
use App\Models\User;
use Exception;
use Illuminate\Support\Arr;

class UserController extends Controller
{
    use SearchContent;

    public function newUser(Request $request) : JsonResponse
    {
        $authUser = auth('sanctum')->user();

        try {
            $request->validate([
                'name' => 'required|min:3',
                'nim' => 'required|min:10|unique:users,nim',
                'ymd' => ['required', 'regex:/^\d{8}$/'],
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
            ]);
            
            $newRecord = User::create([
                'name' => $request->name,
                'nim' => $request->nim,
                'ymd' => $request->ymd,
                'email' => $request->email,
                'password' => Hash::make($request->password)
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
            'data' => UserResource::make($newRecord),
            'status' => [
                'message' => 'User created successfully',
                'code' => 200
            ]
        ], 200);
    }

    public function showDetail(string $skey) : JsonResponse
    {
        $authUser = auth('sanctum')->user();

        $user = User::where('skey', $skey)->first();

        if (!$user) {
            return response()->json([
                'data' => null,
                'status' => [
                    'message' => 'User not found',
                    'code' => 400
                ]
            ], 400);
        }

        return response()->json([
            'data' => UserResource::make($user),
            'status' => [
                'message' => 'User retrieved successfully.',
                'code' => 200
            ]
        ], 200);
    }

    public function updateUser(string $skey, Request $request) : JsonResponse
    {
        $authUser = auth('sanctum')->user();

        try {
            $request->validate([
                'name' => 'required|min:3',
                'nim' => 'required|min:10|unique:users,nim,' . $skey . ',skey',
                'ymd' => ['required', 'regex:/^\d{8}$/'],
                'email' => 'email|unique:users,email,' . $skey . ',skey',
                'password' => 'required|min:8'
            ]);

            $user = User::where('skey', $skey)->first();

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
                'password' => Hash::make($request->password)
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

    public function destroy($skey) : JsonResponse
    {
        $authUser = auth('sanctum')->user();

        $user = User::where('skey', $skey)->first();

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
                'message' => 'User deleted successfully.',
                'code' => 200
            ]
        ], 200);
    }

    public function searchName(Request $request) : JsonResponse
    {
        $authUser = auth('sanctum')->user();
        
        try {
            $request->validate([
                'name' => 'required'
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

        $collection = $this->initSource();
        $filtered = Arr::where($collection, function ($value, $key) use ($request) {
            return stripos($value['name'], $request->name) !== false;
        });

        if (!$filtered) {
            return response()->json([
                'status' => [
                    'message' => 'Name not found.',
                    'code' => 400
                ]
            ], 400);
        }

        return response()->json([
            'data' => $filtered,
            'status' => [
                'message' => 'Name found.',
                'code' => 200
            ]
        ], 200);
    }

    public function searchNim(Request $request) : JsonResponse
    {
        $authUser = auth('sanctum')->user();

        try {
            $request->validate([
                'nim' => 'required'
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

        $collection = $this->initSource();
        $filtered = Arr::where($collection, function ($value, $key) use ($request) {
            return $value['nim'] === $request->nim;
        });

        if (!$filtered) {
            return response()->json([
                'status' => [
                    'message' => 'NIM not found.',
                    'code' => 400
                ]
            ], 400);
        }

        return response()->json([
            'data' => $filtered,
            'status' => [
                'message' => 'NIM found.',
                'code' => 200
            ]
        ], 200);
    }

    public function searchYMD(Request $request) : JsonResponse
    {
        $authUser = auth('sanctum')->user();
        
        try {
            $request->validate([
                'ymd' => 'required'
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

        $collection = $this->initSource();
        $filtered = Arr::where($collection, function ($value, $key) use ($request) {
            return $value['ymd'] === $request->ymd;
        });

        if (!$filtered) {
            return response()->json([
                'status' => [
                    'message' => 'YMD not found.',
                    'code' => 400
                ]
            ], 400);
        }

        return response()->json([
            'data' => $filtered,
            'status' => [
                'message' => 'YMD found.',
                'code' => 200
            ]
        ], 200);
    }
}
