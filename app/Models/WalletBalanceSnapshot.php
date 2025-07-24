<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletBalanceSnapshot extends Model
{
    use HasFactory;
    protected $fillable = ['wallet_id', 'balance', 'taken_at'];

    public function wallet(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
