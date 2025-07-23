<?php

namespace App\Listeners;

use App\Events\UserCreated;
use App\Models\Wallet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateWalletForUser implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'low';

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserCreated $event): void
    {
        $user = $event->user;

        if (!$user || $user->wallet()->exists()) {
            return;
        }

        Wallet::create([
            'user_id' => $event->user->id,
            'balance' => 0,
        ]);
    }
}
