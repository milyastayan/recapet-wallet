<?php

namespace App\Listeners;

use App\Enums\WithdrawalStatus;
use App\Events\DepositCompleted;
use App\Events\TransferCompleted;
use App\Events\WithdrawalCompleted;
use App\Models\LedgerEntry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateLedgerEntry implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'high';

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
    public function handle(object $event): void
    {
        match (true) {
            $event instanceof DepositCompleted => $this->handleDeposit($event),
            $event instanceof WithdrawalCompleted => $this->handleWithdrawal($event),
            $event instanceof TransferCompleted => $this->handleTransfer($event),
            default => null,
        };
    }

    protected function handleDeposit(DepositCompleted $event): void
    {
        $deposit = $event->deposit;

        $this->createEntry([
            'wallet_id' => $deposit->wallet_id,
            'type' => 'credit',
            'amount' => $deposit->amount,
            'reference' => $deposit,
        ]);
    }

    protected function handleWithdrawal(WithdrawalCompleted $event): void
    {
        $withdrawal = $event->withdrawal;

        if ($withdrawal->status !== WithdrawalStatus::Succeeded) {
            return;
        }

        $this->createEntry([
            'wallet_id' => $withdrawal->wallet_id,
            'type' => 'debit',
            'amount' => $withdrawal->amount,
            'reference' => $withdrawal,
        ]);
    }

    protected function handleTransfer(TransferCompleted $event): void
    {
        $transfer = $event->transfer;

        $entries = [
            [
                'wallet_id' => $transfer->sender_wallet_id,
                'type' => 'debit',
                'amount' => $transfer->amount + $transfer->fee,
                'reference' => $transfer,
            ],
            [
                'wallet_id' => $transfer->receiver_wallet_id,
                'type' => 'credit',
                'amount' => $transfer->amount,
                'reference' => $transfer,
            ],
        ];

        if ($transfer->fee > 0) {
            $entries[] = [
                'wallet_id' => $transfer->sender_wallet_id,
                'type' => 'fee',
                'amount' => $transfer->fee,
                'reference' => $transfer,
            ];
        }

        LedgerEntry::insert(array_map(function ($entry) {
            return $this->buildEntryPayload($entry);
        }, $entries));
    }

    protected function createEntry(array $entry): void
    {
        LedgerEntry::create($this->buildEntryPayload($entry));
    }

    protected function buildEntryPayload(array $entry): array
    {
        return [
            'wallet_id' => $entry['wallet_id'],
            'type' => $entry['type'],
            'amount' => $entry['amount'],
            'reference_type' => get_class($entry['reference']),
            'reference_id' => $entry['reference']->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
