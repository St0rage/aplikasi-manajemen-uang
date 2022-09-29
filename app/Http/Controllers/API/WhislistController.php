<?php

namespace App\Http\Controllers\API;

use App\Models\Whislist;
use App\Models\PiggyBank;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\WhislistTransaction;
use App\Http\Controllers\Controller;
use App\Models\PiggyBankTransaction;

class WhislistController extends Controller
{
    public function getWhislists()
    {
        $whislists = Whislist::where('user_id', auth()->user()->id)->get();

        return response()->json([
            'code' => 200,
            'status' => 'ok',
            'data' => [
                'whislists' => $whislists
            ]
        ], 200);
    }

    public function getWhislistDetail(Whislist $whislist)
    {
        if (auth()->user()->id != $whislist->user_id) {
            return response()->json([
                'code' => 403,
                'status' => 'forbidden',
            ], 403);
        }

        return response()->json([
            'code' => 200,
            'status' => 'ok',
            'data' => [
                'whislist' => $whislist
            ]
        ], 200);
    }

    public function createWhislist(Request $request)
    {
        $validated = $request->validate([
            'whislist_name' => [
                'required',
                'max:50',
                Rule::unique('whislists', 'whislist_name')->where(fn ($query) => $query->where('user_id', auth()->user()->id))
            ]
        ]);

        Whislist::create([
            'user_id' => auth()->user()->id,
            'whislist_name' => $validated['whislist_name'],
        ]);

        return response()->json([
            'code' => 201,
            'status' => 'created',
            'message' => 'Whislist ' . $validated['whislist_name'] . ' berhasil dibuat'
        ], 201);
    }

    public function updateWhislist(Request $request, Whislist $whislist)
    {
        if (auth()->user()->id != $whislist->user_id) {
            return response()->json([
                'code' => 403,
                'status' => 'forbidden',
            ], 403);
        }

        $rules = [
            'whislist_name' => 'required|max:50'
        ];

        if ($request->whislist_name != $whislist->whislist_name) {
            $rules['whislist_name'] = [
                'required',
                'max:50',
                Rule::unique('whislists', 'whislist_name')->where(fn ($query) => $query->where('user_id', auth()->user()->id))
            ];
        }

        $validated = $request->validate($rules);

        Whislist::where('id', $whislist->id)->update($validated);

        return response()->json([
            'code' => 200,
            'status' => 'ok',
            'message' => 'Whislist berhasil diubah menjadi ' . $validated['whislist_name']
        ], 200);
    }

    public function deleteWhislist(Whislist $whislist)
    {
        if (auth()->user()->id != $whislist->user_id) {
            return response()->json([
                'code' => 403,
                'status' => 'forbidden',
            ], 403);
        }

        $currentAmount = $whislist->whislist_total;
        $currentWhislistName = $whislist->whislist_name;

        Whislist::destroy($whislist->id);

        if ($currentAmount == 0) {
            return response()->json([
                'code' => 204,
                'status' =>  'success',
                'message' => 'Whislist berhasil dihapus'      
            ], 204);
        } else {
            $primayPiggyBank = PiggyBank::where('user_id', auth()->user()->id)
                                            ->where('type', 1)
                                            ->get()->first();

            $transaction = new PiggyBankTransaction([
                'transaction_name' => 'Saldo Pindahan Whislist ' . $currentWhislistName,
                'amount' => $currentAmount,
                'status' => 1,
                'date' => time()
            ]);

            $transaction = $primayPiggyBank->piggyBankTransactions()->save($transaction);

            PiggyBankController::sumPiggyBankTransaction($primayPiggyBank->id);

            return response()->json([
                'code' => 204,
                'status' =>  'success',
                'message' => 'Whislist berhasil dihapus'      
            ], 204);
        }
    }

    public function getWhislistTransactions(Request $request, Whislist $whislist)
    {
        if (auth()->user()->id != $whislist->user_id) {
            return response()->json([
                'code' => 403,
                'status' => 'forbidden',
            ], 403);
        }

        $transactions =  WhislistTransaction::where('whislist_id', $whislist->id)->offset($request->page * 5)->limit(5)->get();

        return response()->json([
            'code' => 200,
            'status' => 'ok',
            'data' => [
                'transactions' => $transactions
            ]
        ], 200);
    }

    public function createWhislistTransaction(Request $request, Whislist $whislist)
    {
        if (auth()->user()->id != $whislist->user_id) {
            return response()->json([
                'code' => 403,
                'status' => 'forbidden',
            ], 403);
        }

        $validated = $request->validate([
            'transaction_name' => 'required|max:50',
            'amount' => 'required|numeric|min:10000'
        ]);

        $transaction = new WhislistTransaction([
            'transaction_name' => $validated['transaction_name'],
            'amount' => $validated['amount'],
            'status' => 1,
            'date' => time()
        ]);
        $transaction = $whislist->whislistTransactions()->save($transaction);

        // SUM TRANSACTION
        self::sumWhislistTransaction($whislist->id);

        return response()->json([
            'code' => 201,
            'status' => 'created',
            'message' => 'Transaksi sebesar Rp ' . number_format($validated['amount']) . ' berhasil ditambahkan ke Whislist ' . $whislist->whislist_name
        ], 201);
    }

    public function substractWhislistTransaction(Request $request, Whislist $whislist)
    {
        if (auth()->user()->id != $whislist->user_id) {
            return response()->json([
                'code' => 403,
                'status' => 'forbidden',
            ], 403);
        }

        $validated = $request->validate([
            'transaction_name' => 'required|max:50',
            // 'amount' => "required|numeric|min:10000|lte:$whislist->whislist_total"
            'amount' => [
                'required',
                'numeric',
                'min:1000',
                function($attribute, $value, $fail) use ($whislist) {
                    if ($whislist->whislist_total == 0) {
                        $fail('Penarikan gagal saldo tidak mencukupi.');
                    } else if ($value > $whislist->whislist_total) {
                        $fail('Gagal, penarikan tidak boleh lebih dari Rp ' . number_format($whislist->whislist_total) . '.');
                    }
                }
            ]
        ]);

        $transaction = new WhislistTransaction([
            'transaction_name' => $validated['transaction_name'],
            'amount' => 0 - $validated['amount'],
            'status' => 0,
            'date' => time()
        ]);
        $transaction = $whislist->whislistTransactions()->save($transaction);

        // SUM TRANSACTION
        self::sumWhislistTransaction($whislist->id);

        return response()->json([
            'code' => 201,
            'status' => 'created',
            'message' => 'Transaksi sebesar Rp ' . number_format($validated['amount']) . ' berhasil dipotong dari Whislist ' . $whislist->whislist_name
        ], 201);
    }

    public function deleteWhislistTransaction(WhislistTransaction $whislistTransaction)
    {
        if (auth()->user()->id != $whislistTransaction->whislist->user_id) {
            return response()->json([
                'code' => 403,
                'status' => 'forbidden',
            ], 403);
        }
        
        $lastTransaction =  WhislistTransaction::where('whislist_id', $whislistTransaction->whislist_id)->get()->last();

        if ($whislistTransaction->id != $lastTransaction->id) {
            return response()->json([
                'code' => 400,
                'status' => 'Hanya transaksi terakhir yang bisa dihapus'
            ], 400);
        }

        WhislistTransaction::destroy($whislistTransaction->id);

        // SUM TRANSACTION
        self::sumWhislistTransaction($whislistTransaction->whislist_id);

        return response()->json([
            'code' => 204,
            'status' => 'success',
            'message' => 'Transaksi berhasil dihapus'
        ], 204);
    }

    private static function sumWhislistTransaction($whislistId): void
    {
        $transactions = [];

        $whislistTransaction =  WhislistTransaction::where('whislist_id', $whislistId)->get();

        foreach($whislistTransaction as $key => $value) {
            array_push($transactions, $value['amount']);
        }

        Whislist::where('id', $whislistId)->update(['whislist_total' => array_sum($transactions)]);

        // SUM BALANCE
        BalanceController::sumBalance();
    }
}
