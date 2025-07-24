<?php

namespace App\Models;

use App\Enums\WithdrawalStatus;
use App\Events\DepositCompleted;
use App\Events\TransferCompleted;
use App\Events\WithdrawalCompleted;
use App\Traits\HandlesWalletConcurrency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Throwable;

class Wallet extends Model
{
    use HasFactory, HandlesWalletConcurrency;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'balance',
        'uuid',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'balance' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($wallet) {
            if (empty($wallet->uuid)) {
                $wallet->uuid = Str::uuid()->toString();
            }
        });
    }

    /**
     * Get the user that owns the wallet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function ledger()
    {
        return $this->hasMany(LedgerEntry::class);
    }

    /**
     * @throws Throwable
     */
    public function deposit(int $amount): Deposit
    {
        return $this->withWalletLock($this, function (Wallet $wallet) use ($amount) {
            $wallet->increment('balance', $amount);

            $deposit = $wallet->deposits()->create([
                'amount' => $amount,
                'new_balance' => $wallet->balance,
            ]);

            event(new DepositCompleted($deposit));

            return $deposit;
        });
    }

    /**
     * @throws Throwable
     */
    public function withdraw(int $amount): Withdrawal
    {
        return $this->withWalletLock($this, function (Wallet $wallet) use ($amount) {
            if ($amount > $wallet->balance) {
                return $wallet->withdrawals()->create([
                    'amount' => $amount,
                    'status' => WithdrawalStatus::Failed,
                ]);
            }

            $wallet->decrement('balance', $amount);

            $withdrawal =  $wallet->withdrawals()->create([
                'amount' => $amount,
                'status' => WithdrawalStatus::Succeeded,
            ]);

            if ($withdrawal->status === WithdrawalStatus::Succeeded) {
                event(new WithdrawalCompleted($withdrawal));
            }

            return $withdrawal;
        });
    }

    /**
     * @throws Throwable
     */
    public function transferTo(Wallet $receiver, int $amount, string $idempotencyKey): Transfer
    {
        return $this->withDualWalletLock($this, $receiver, function (Wallet $sender, Wallet $receiver) use ($amount, $idempotencyKey) {

            if ($sender->id === $receiver->id) {
                throw new \Exception('Cannot transfer to the same wallet.');
            }

            if ($amount <= 0) {
                throw new \Exception('Invalid amount.');
            }

            $fee = $amount > 2500 ? 250 + intval($amount * 0.10) : 0;
            $total = $amount + $fee;

            if ($sender->balance < $total) {
                throw new \Exception('Insufficient balance.');
            }

            $sender->decrement('balance', $total);
            $receiver->increment('balance', $amount);

            $transfer =  Transfer::create([
                'sender_wallet_id' => $sender->id,
                'receiver_wallet_id' => $receiver->id,
                'amount' => $amount,
                'fee' => $fee,
                'idempotency_key' => $idempotencyKey,
            ]);

            event(new TransferCompleted($transfer));

            return $transfer;
        });
    }
}
