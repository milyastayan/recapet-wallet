<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreWithdrawalRequest;
use App\Http\Resources\Api\V1\WithdrawalResource;
use App\Models\User;
use App\Models\Wallet;

class WithdrawalController extends Controller
{
    public function store(StoreWithdrawalRequest $request)
    {
         /** @var User $user */
        $user = $request->user();
        /** @var Wallet $wallet */
        $wallet = $user->wallet;

        $withdrawal = $wallet->withdraw($request->validated('amount'));

        return $this->successResponse(
            message: 'Withdrawal processed',
            data: ['withdrawal' => new WithdrawalResource($withdrawal->load('wallet'))],
        );
    }
}
