<?php

namespace App\Jobs;

use App\Models\Wallet;
use App\Models\WalletBalanceSnapshot;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SnapshotWalletBalances implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->queue = 'low';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Wallet::chunk(100, function ($wallets) {
            foreach ($wallets as $wallet) {
                WalletBalanceSnapshot::create([
                    'wallet_id' => $wallet->id,
                    'balance' => $wallet->balance,
                    'taken_at' => now(),
                ]);
            }
        });
    }
}
