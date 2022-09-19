<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PiggyBank;
use App\Models\PiggyBankTransaction;
use App\Models\User;
use Illuminate\Http\Request;

class PiggyBankController extends Controller
{
    public function createPiggyBank(Request $request)
    {
        $validated = $request->validate([
            'piggy_bank_name' => 'required|max:50'
        ]);

        PiggyBank::create([
            'user_id' => auth()->user()->id,
            'piggy_bank_name' => $validated['piggy_bank_name']
        ]);

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Saving ' . $validated['piggy_bank_name'] . ' berhasil dibuat'
        ], 200);
    }

    public function getPiggyBanks()
    {
        $piggyBanks = PiggyBank::where('user_id', auth()->user()->id)->get();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'data' => $piggyBanks
        ], 200);

    }

    public function getPiggyBankDetail(PiggyBank $piggyBank)
    {
        
        if (!auth()->user()->id == $piggyBank->user_id) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
            ], 404);
        };

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'data' => $piggyBank
        ], 200);
    }

    public function createPiggyBankTransaction(PiggyBank $piggyBank, Request $request)
    {
        if (!auth()->user()->id == $piggyBank->user_id) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
            ], 404);
        };

        $validated = $request->validate([
            'transaction_name' => 'required|max:50',
            'amount' => 'required|numeric'
        ]);

        $transaction = new PiggyBankTransaction([
            'transaction_name' => $validated['transaction_name'],
            'amount' => $validated['amount'],
            'status' => 1,
            'date' => time()
        ]);
        $transaction = $piggyBank->piggyBankTransactions()->save($transaction);

        $transactions = [];

        foreach($piggyBank->piggyBankTransactions as $key => $value) {
            array_push($transactions, $value['amount']);
        }

        $piggyBank->piggy_bank_total = array_sum($transactions);
        $piggyBank->save();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Transaksi sebesar ' . $validated['amount'] . ' berhasil ditambahkan ke ' . $piggyBank->piggy_bank_name
        ], 200);
    }
}
