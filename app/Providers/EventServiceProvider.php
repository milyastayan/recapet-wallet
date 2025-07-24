<?php

namespace App\Providers;

use App\Events\DepositCompleted;
use App\Events\TransferCompleted;
use App\Events\WithdrawalCompleted;
use App\Listeners\CreateLedgerEntry;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        DepositCompleted::class => [
            CreateLedgerEntry::class,
        ],
        WithdrawalCompleted::class => [
            CreateLedgerEntry::class,
        ],
        TransferCompleted::class => [
            CreateLedgerEntry::class,
        ],

    ];
}
