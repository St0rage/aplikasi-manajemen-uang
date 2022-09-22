<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PiggyBank;
use App\Models\PiggyBankTransaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PiggyBankController extends Controller
{
    public function createPiggyBank(Request $request)
    {
        $primayPiggyBank =  PiggyBank::where('user_id', auth()->user()->id)
                                ->where('type', 1)
                                ->count();

        $validated = $request->validate([
            // 'piggy_bank_name' => 'required|max:50|unique:piggy_banks,piggy_bank_name'
            'piggy_bank_name' => [
                'required',
                'max:50',
                Rule::unique('piggy_banks', 'piggy_bank_name')->where(fn ($query) => $query->where('user_id', auth()->user()->id))
            ]
        ]);

        PiggyBank::create([
            'user_id' => auth()->user()->id,
            'piggy_bank_name' => $validated['piggy_bank_name'],
            'type' => $primayPiggyBank == 1 ? 0 : 1
        ]);

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Tabungan ' . $validated['piggy_bank_name'] . ' berhasil dibuat'
        ], 200);
    }

    public function updatePiggyBank(Request $request, PiggyBank $piggyBank)
    {
        if (auth()->user()->id != $piggyBank->user_id) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
            ], 404);
        }

        $rules = [
            'piggy_bank_name' => 'required|max:50'
        ];

        if ($request->piggy_bank_name != $piggyBank->piggy_bank_name) {
            // $rules['piggy_bank_name'] = 'required|max:50|unique:piggy_banks,piggy_bank_name';
            $rules['piggy_bank_name'] = [
                'required',
                'max:50',
                Rule::unique('piggy_banks', 'piggy_bank_name')->where(fn ($query) => $query->where('user_id', auth()->user()->id))
            ];
        }

        $validated = $request->validate($rules);

        PiggyBank::where('id', $piggyBank->id)->update($validated);

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Tabungan ' . $validated['piggy_bank_name'] . ' berhasil diubah'
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
        
        if (auth()->user()->id != $piggyBank->user_id) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'data' => $piggyBank
        ], 200);
    }

    public function deletePiggyBank(PiggyBank $piggyBank)
    {
        if (auth()->user()->id != $piggyBank->user_id) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
            ], 404);
        }

        if ($piggyBank->type == 1) {
            return response()->json([
                'code' => 400,
                'status' => 'error',
                'message' => 'Tabungan ini bersifat primary, tidak bisa dihapus'
            ]);
        }

        return $piggyBank;

        
    }

    public function createPiggyBankTransaction(Request $request, PiggyBank $piggyBank)
    {
        if (auth()->user()->id != $piggyBank->user_id) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
            ], 404);
        }

        $validated = $request->validate([
            'transaction_name' => 'required|max:50',
            'amount' => 'required|numeric|min:10000'
        ]);

        $transaction = new PiggyBankTransaction([
            'transaction_name' => $validated['transaction_name'],
            'amount' => $validated['amount'],
            'status' => 1,
            'date' => time()
        ]);
        $transaction = $piggyBank->piggyBankTransactions()->save($transaction);

        // SUM TRANSACTION
        $this->sumPiggyBankTransaction($piggyBank->id);

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Transaksi sebesar ' . $validated['amount'] . ' berhasil ditambahkan ke ' . $piggyBank->piggy_bank_name
        ], 200);
    }

    public function substractPiggyBankTransaction(PiggyBank $piggyBank, Request $request)
    {
        if (auth()->user()->id != $piggyBank->user_id) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
            ], 404);
        }

        $validated = $request->validate([
            'transaction_name' => 'required|max:50',
            'amount' => "required|numeric|min:10000|lte:$piggyBank->piggy_bank_total"
        ]);

        $transaction = new PiggyBankTransaction([
            'transaction_name' => $validated['transaction_name'],
            'amount' => 0 - $validated['amount'],
            'status' => 0,
            'date' => time()
        ]);
        $transaction = $piggyBank->piggyBankTransactions()->save($transaction);

        // SUM TRANSACTION
        $this->sumPiggyBankTransaction($piggyBank->id);

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Transaksi sebesar ' . $validated['amount'] . ' berhasil dipotong dari ' . $piggyBank->piggy_bank_name
        ], 200);
    }

    public function deletePiggyBankTransaction(PiggyBankTransaction $piggyBankTransaction)
    {
        if (auth()->user()->id != $piggyBankTransaction->piggyBank->user_id) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
            ], 404);
        }    

        PiggyBankTransaction::destroy($piggyBankTransaction->id);

        // SUM TRANSACTION
        $this->sumPiggyBankTransaction($piggyBankTransaction->piggy_bank_id);

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Transaksi berhasil dihapus'
        ], 200);
    }




    private function sumPiggyBankTransaction($piggyBankId): void
    {
        $transactions = [];

        $piggyBankTransaction =  PiggyBankTransaction::where('piggy_bank_id', $piggyBankId)->get();

        foreach($piggyBankTransaction as $key => $value) {
            array_push($transactions, $value['amount']);
        }

        PiggyBank::where('id', $piggyBankId)->update(['piggy_bank_total' => array_sum($transactions)]);
    }
}
