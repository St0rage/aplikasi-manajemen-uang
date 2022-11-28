<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PiggyBank;
use App\Models\PiggyBankTransaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PiggyBankController extends Controller
{
    public function getPiggyBanks()
    {
        $piggyBanks = PiggyBank::where('user_id', auth()->user()->id)->get();

        return response()->json([
            'code' => 200,
            'status' => 'OK',
            'data' => $piggyBanks
        ], 200);
    }

    public function getPiggyBankDetail(PiggyBank $piggyBank)
    {
        if (auth()->user()->id != $piggyBank->user_id) {
            return response()->json([
                'code' => 404,
                'status' => 'NOT_FOUND',
            ], 404);
        }

        $piggyBank['total_transaction'] = PiggyBankTransaction::where('piggy_bank_id', $piggyBank->id)->count();

        return response()->json([
            'code' => 200,
            'status' => 'OK',
            'data' => $piggyBank
        ], 200);
    }

    public function createPiggyBank(Request $request)
    {
        $primayPiggyBank =  PiggyBank::where('user_id', auth()->user()->id)
                                ->where('type', 1)
                                ->count();

        $validated = $request->validate([
            // 'piggy_bank_name' => 'required|max:50|unique:piggy_banks,piggy_bank_name'
            'piggy_bank_name' => [
                'required',
                'max:10',
                'min:3',
                Rule::unique('piggy_banks', 'piggy_bank_name')->where(fn ($query) => $query->where('user_id', auth()->user()->id))
            ]
        ]);

        PiggyBank::create([
            'user_id' => auth()->user()->id,
            'piggy_bank_name' => $validated['piggy_bank_name'],
            'type' => $primayPiggyBank == 1 ? 0 : 1
        ]);

        return response()->json([
            'code' => 201,
            'status' => 'CREATED',
            'message' => 'Tabungan ' . $validated['piggy_bank_name'] . ' berhasil dibuat'
        ], 201);
    }

    public function updatePiggyBank(Request $request, PiggyBank $piggyBank)
    {
        if (auth()->user()->id != $piggyBank->user_id) {
            return response()->json([
                'code' => 404,
                'status' => 'NOT_FOUND',
            ], 404);
        }

        $rules = [
            'piggy_bank_name' => 'required|max:10|min:3'
        ];

        if ($request->piggy_bank_name != $piggyBank->piggy_bank_name) {
            $rules['piggy_bank_name'] = [
                'required',
                'max:10',
                'min:3',
                Rule::unique('piggy_banks', 'piggy_bank_name')->where(fn ($query) => $query->where('user_id', auth()->user()->id))
            ];
        }

        $validated = $request->validate($rules);

        PiggyBank::where('id', $piggyBank->id)->update($validated);

        return response()->json([
            'code' => 200,
            'status' => 'OK',
            'message' => 'Tabungan berhasil diubah menjadi ' . $validated['piggy_bank_name']
        ], 200);
    }

    public function deletePiggyBank(PiggyBank $piggyBank)
    {
        if (auth()->user()->id != $piggyBank->user_id) {
            return response()->json([
                'code' => 404,
                'status' => 'NOT_FOUND',
            ], 404);
        }

        if ($piggyBank->type == 1) {
            return response()->json([
                'code' => 400,
                'status' => 'BAD_REQUEST',
                'message' => 'Tabungan ini bersifat primary, tidak bisa dihapus'
            ], 400);
        }

        $currentAmount = $piggyBank->piggy_bank_total;
        $currentPiggyBankName = $piggyBank->piggy_bank_name;

        PiggyBank::destroy($piggyBank->id);

        if ($currentAmount == 0) {
            return response()->json([
                'code' => 200,
                'status' =>  'OK',
                'message' => 'Tabungan berhasil dihapus'      
            ], 200);
        } else {
            $primayPiggyBank = PiggyBank::where('user_id', auth()->user()->id)
                                            ->where('type', 1)
                                            ->get()->first();

            $transaction = new PiggyBankTransaction([
                'transaction_name' => 'Saldo Pindahan Tabungan ' . $currentPiggyBankName,
                'amount' => $currentAmount,
                'status' => 1,
                'date' => time()
            ]);

            $transaction = $primayPiggyBank->piggyBankTransactions()->save($transaction);

            // SUM TRANSACTION
            self::sumPiggyBankTransaction($primayPiggyBank->id);

            return response()->json([
                'code' => 200,
                'status' =>  'OK',
                'message' => 'Tabungan berhasil dihapus'      
            ], 200);
        }
    }

    public function getPiggyBankTransactions(Request $request, PiggyBank $piggyBank)
    {
        if (auth()->user()->id != $piggyBank->user_id) {
            return response()->json([
                'code' => 404,
                'status' => 'NOT_FOUND',
            ], 404);
        }

        $transactions =  PiggyBankTransaction::where('piggy_bank_id', $piggyBank->id)->offset($request->page * 10)->limit(10)->orderBy('id', 'desc')->get();

        return response()->json([
            'code' => 200,
            'status' => 'OK',
            'data' => $transactions
        ], 200);
    }

    public function createPiggyBankTransaction(Request $request, PiggyBank $piggyBank)
    {
        if (auth()->user()->id != $piggyBank->user_id) {
            return response()->json([
                'code' => 404,
                'status' => 'NOT_FOUND',
            ], 404);
        }

        $validated = $request->validate([
            // 'transaction_name' => 'required|max:50',
            'amount' => 'required|numeric|min:10000'
        ]);

        $transaction = new PiggyBankTransaction([
            // 'transaction_name' => $validated['transaction_name'],
            'transaction_name' => 'Tambah Saldo',
            'amount' => $validated['amount'],
            'status' => 1,
            'date' => time()
        ]);
        $transaction = $piggyBank->piggyBankTransactions()->save($transaction);

        // SUM TRANSACTION
        self::sumPiggyBankTransaction($piggyBank->id);

        return response()->json([
            'code' => 201,
            'status' => 'CREATED',
            'message' => 'Transaksi sebesar Rp ' . number_format($validated['amount']) . ' berhasil ditambahkan ke Tabungan ' . $piggyBank->piggy_bank_name
        ], 201);
    }

    public function substractPiggyBankTransaction(Request $request, PiggyBank $piggyBank)
    {
        if (auth()->user()->id != $piggyBank->user_id) {
            return response()->json([
                'code' => 404,
                'status' => 'NOT_FOUND',
            ], 404);
        }

        $validated = $request->validate([
            'transaction_name' => 'required|max:15|min:3',
            // 'amount' => "required|numeric|min:10000|lte:$piggyBank->piggy_bank_total"
            'amount' => [
                'required',
                'numeric',
                'min:1000',
                function($attribute, $value, $fail) use ($piggyBank) {
                    if ($piggyBank->piggy_bank_total == 0) {
                        $fail('Penarikan gagal saldo tidak mencukupi.');
                    } else if ($value > $piggyBank->piggy_bank_total) {
                        $fail('Gagal, penarikan tidak boleh lebih dari Rp ' . number_format($piggyBank->piggy_bank_total) . '.');
                    }
                }
            ]
        ]);

        $transaction = new PiggyBankTransaction([
            'transaction_name' => $validated['transaction_name'],
            'amount' => 0 - $validated['amount'],
            'status' => 0,
            'date' => time()
        ]);
        $transaction = $piggyBank->piggyBankTransactions()->save($transaction);

        // SUM TRANSACTION
        self::sumPiggyBankTransaction($piggyBank->id);

        return response()->json([
            'code' => 201,
            'status' => 'CREATED',
            'message' => 'Transaksi sebesar Rp ' . number_format($validated['amount']) . ' berhasil dipotong dari Tabungan ' . $piggyBank->piggy_bank_name
        ], 201);
    }

    public function deletePiggyBankTransaction(PiggyBankTransaction $piggyBankTransaction)
    {
        if (auth()->user()->id != $piggyBankTransaction->piggyBank->user_id) {
            return response()->json([
                'code' => 404,
                'status' => 'NOT_FOUND',
            ], 404);
        }   
        
        $lastTransaction =  PiggyBankTransaction::where('piggy_bank_id', $piggyBankTransaction->piggy_bank_id)->get()->last();

        if ($piggyBankTransaction->id != $lastTransaction->id) {
            return response()->json([
                'code' => 400,
                'status' => 'BAD_REQUEST',
                'message' => 'Hanya transaksi terakhir yang bisa dihapus'
            ], 400);
        }

        PiggyBankTransaction::destroy($piggyBankTransaction->id);

        // SUM TRANSACTION
        self::sumPiggyBankTransaction($piggyBankTransaction->piggy_bank_id);

        return response()->json([
            'code' => 200,
            'status' => 'OK',
            'message' => 'Transaksi berhasil dihapus'
        ], 200);
    }

    public static function sumPiggyBankTransaction($piggyBankId): void
    {
        $transactions = [];

        $piggyBankTransaction =  PiggyBankTransaction::where('piggy_bank_id', $piggyBankId)->get();

        foreach($piggyBankTransaction as $key => $value) {
            array_push($transactions, $value['amount']);
        }

        PiggyBank::where('id', $piggyBankId)->update(['piggy_bank_total' => array_sum($transactions)]);

        // SUM BALANCE
        BalanceController::sumBalance();
    }
}
