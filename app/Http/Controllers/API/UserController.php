<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function getUser()
    {
        return response()->json([
            'code' => 200,
            'status' => 'ok',
            'data' => [
                'user' => auth()->user()
            ]
        ], 200);
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|min:5|max:255|confirmed'
        ]);

        User::where('id', auth()->user()->id)->update(['password' => Hash::make($validated['password'])]);

        return response()->json([
            'code' => 200,
            'status' => 'ok',
            'message' => 'Password berhasil diubah'
        ], 200);
    }
}
