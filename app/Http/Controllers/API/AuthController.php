<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\SendAccount;
use App\Models\Balance;
use App\Models\PiggyBank;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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
            'data' => [
                'user' => $user,
                'piggy_banks_count' => $piggyBankCount,
                'token' => $token
            ]
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

    public function register(Request $request)
    {
        if (!Gate::allows('register-user', auth()->user()->id)) {
            return response()->json([
                'code' => 403,
                'status' => 'forbidden',
            ], 403);
        }

        $validated = $request->validate([
            'email' => 'required|email:dns|unique:users',
            'name' => 'required|min:5|max:50'
            // 'password' => 'required|min:5|max:255',
        ]);

        $password = mt_rand(10000, 50000);

        $validated['password'] = Hash::make($password);

        $user = User::create($validated);

        $balance  = new Balance([
            'balance_total' => 0
        ]);

        $balance = $user->balance()->save($balance);

        Mail::to($validated['email'])->send(new SendAccount([
            'email' => $validated['email'],
            'name' => $validated['name'],
            'password' => $password
        ]));

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'User berhasil dibuat silahkan cek email untuk dapat melihat password'
        ]);
    }
}
