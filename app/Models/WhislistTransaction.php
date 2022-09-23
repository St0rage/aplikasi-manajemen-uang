<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhislistTransaction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function whislist()
    {
        return $this->belongsTo(Whislist::class);
    }
}
