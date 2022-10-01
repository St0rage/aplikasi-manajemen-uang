<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Balance;
use App\Models\PiggyBank;
use App\Models\Whislist;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function getBalance()
    {
        $balance = Balance::where('user_id', auth()->user()->id)->get()->first();

        return response()->json([
            'code' => 200,
            'status' => 'ok',
            'data' => [
                'balance' => $balance
            ]
        ], 200);
    }

    public static function sumBalance(): void
    {
        $balance = [];

        $piggyBankTotal = PiggyBank::where('user_id', auth()->user()->id)->get();
        $whislistTotal = Whislist::where('user_id', auth()->user()->id)->get();

        foreach($piggyBankTotal as $key => $value) {
            array_push($balance, $value['piggy_bank_total']);
        }

        foreach($whislistTotal as $key => $value) {
            array_push($balance, $value['whislist_total']);
        }

        Balance::where('user_id', auth()->user()->id)->update(['balance_total' => array_sum($balance)]);
    }
}
