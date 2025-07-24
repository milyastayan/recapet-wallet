<?php

namespace App\Models;

use App\Enums\WithdrawalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class Wallet extends Model
{
    use HasFactory;

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
    protected static function boot()
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

    public function deposit(int $amount): Deposit
    {
        $this->balance += $amount;
        $this->save();

        return $this->deposits()->create([
            'amount' => $amount,
            'new_balance' => $this->balance,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function withdraw(int $amount): Withdrawal
    {
        return DB::transaction(function () use ($amount) {
            if ($amount > $this->balance) {
                return $this->withdrawals()->create([
                    'amount' => $amount,
                    'status' => WithdrawalStatus::Failed,
                ]);
            }

            $this->balance -= $amount;
            $this->save();

            return $this->withdrawals()->create([
                'amount' => $amount,
                'status' => WithdrawalStatus::Succeeded,
            ]);
        });
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }
}
