<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PiggyBank extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(PiggyBank::class);
    }

    public function piggyBankTransactions()
    {
        return $this->hasMany(PiggyBankTransaction::class, 'piggy_bank_id');
    }
}
