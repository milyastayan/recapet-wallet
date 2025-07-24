<?php

namespace App\Traits;

use App\Models\Wallet;
use Closure;
use Illuminate\Support\Facades\DB;
use Throwable;

trait HandlesWalletConcurrency
{
    /**
     * Execute an operation on a single wallet with locking for concurrency safety.
     *
     * @param Wallet $wallet
     * @param Closure(Wallet): mixed $callback
     * @return mixed
     * @throws Throwable
     */
    protected function withWalletLock(Wallet $wallet, Closure $callback): mixed
    {
        return DB::transaction(function () use ($wallet, $callback) {
            $lockedWallet = Wallet::where('id', $wallet->id)
                ->lockForUpdate()
                ->firstOrFail();

            return $callback($lockedWallet);
        });
    }

    /**
     * Execute an operation on two wallets with deadlock-safe locking.
     *
     * @param Wallet $wallet1
     * @param Wallet $wallet2
     * @param Closure(Wallet $firstLocked, Wallet $secondLocked): mixed $callback
     * @return mixed
     * @throws Throwable
     */
    protected function withDualWalletLock(Wallet $wallet1, Wallet $wallet2, Closure $callback): mixed
    {
        return DB::transaction(function () use ($wallet1, $wallet2, $callback) {
            $sorted = collect([$wallet1, $wallet2])->sortBy('id')->values();

            $lockedWallets = $sorted->map(fn($wallet) =>
            Wallet::where('id', $wallet->id)
                ->lockForUpdate()
                ->firstOrFail()
            );

            return $callback($lockedWallets[0], $lockedWallets[1]);
        });
    }
}
