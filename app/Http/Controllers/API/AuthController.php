<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PiggyBank;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        $user =  User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'code' => '401',
                'status' => 'error',
                'message' => 'Gagal Login Email Atau Password Salah'
            ], 401);
        }

        $piggyBankCount = PiggyBank::where('user_id', $user->id)->count();

        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'code' => '200',
            'status' => 'success',
            'user' => $user,
            'piggy_banks' => $piggyBankCount,
            'token' => $token
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'code' => '200',
            'status' => 'success',
            'message' => 'Berhasil Logout'
        ]);
    }
}
