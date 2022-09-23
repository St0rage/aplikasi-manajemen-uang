<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Models\Whislist;
use App\Models\WhislistTransaction;

class WhislistController extends Controller
{
    public function getWhislists()
    {
        $whislist = Whislist::where('user_id', auth()->user()->id)->get();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'data' => $whislist
        ], 200);
    }

    public function getWhislistDetail(Whislist $whislist)
    {
        if (auth()->user()->id != $whislist->user_id) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'data' => $whislist
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
            'code' => 200,
            'status' => 'success',
            'message' => 'Whislist ' . $validated['whislist_name'] . ' berhasil dibuat'
        ], 200);
    }

    public function updateWhislist(Request $request, Whislist $whislist)
    {
        if (auth()->user()->id != $whislist->user_id) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
            ], 404);
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
            'status' => 'success',
            'message' => 'Whislist berhasil diubah menjadi ' . $validated['whislist_name']
        ], 200);
    }

    public function createWhislistTransaction(Request $request, Whislist $whislist)
    {
        if (auth()->user()->id != $whislist->user_id) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
            ], 404);
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
            'code' => 200,
            'status' => 'success',
            'message' => 'Transaksi sebesar ' . $validated['amount'] . ' berhasil ditambahkan ke Whislist ' . $whislist->whislist_name
        ], 200);
    }

    public function substractWhislistTransaction(Request $request, Whislist $whislist)
    {
        if (auth()->user()->id != $whislist->user_id) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
            ], 404);
        }

        $validated = $request->validate([
            'transaction_name' => 'required|max:50',
            'amount' => "required|numeric|min:10000|lte:$whislist->whislist_total"
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
            'code' => 200,
            'status' => 'success',
            'message' => 'Transaksi sebesar ' . $validated['amount'] . ' berhasil dipotong dari Whislist ' . $whislist->whislist_name
        ], 200);
    }

    private static function sumWhislistTransaction($whislistId): void
    {
        $transactions = [];

        $whislistTransaction =  WhislistTransaction::where('whislist_id', $whislistId)->get();

        foreach($whislistTransaction as $key => $value) {
            array_push($transactions, $value['amount']);
        }

        Whislist::where('id', $whislistId)->update(['whislist_total' => array_sum($transactions)]);
    }
}
