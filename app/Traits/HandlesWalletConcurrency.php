<?php

namespace App\Traits;

use App\Models\Wallet;
use Closure;
use Illuminate\Support\Facades\DB;
use Throwable;

trait HandlesWalletConcurrency
{
    /**
     * Execute an operation on a wallet with locking for concurrency safety.
     *
     * @param Wallet $wallet
     * @param Closure $callback
     * @return mixed
     * @throws Throwable
     */
    protected function withWalletLock(Wallet $wallet, Closure $callback): mixed
    {
        return DB::transaction(function () use ($wallet, $callback) {
            $lockedWallet = Wallet::where('id', $wallet->id)->lockForUpdate()->firstOrFail();
            return $callback($lockedWallet);
        });
    }
}
