<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreWithdrawalRequest;
use App\Http\Resources\Api\V1\TransferResource;
use App\Http\Resources\Api\V1\WithdrawalResource;
use App\Models\User;
use App\Models\Wallet;
use Throwable;

class TransferController extends Controller
{
    /**
     * @throws Throwable
     */
    public function store(StoreWithdrawalRequest $request)
    {
         /** @var Wallet $sender */
        $sender = $request->user()->wallet;
        $receiver = Wallet::where('uuid', $request->recipient_uuid)->firstOrFail();

        $transfer = $sender->transferTo($receiver, $request->amount);

        return $this->successResponse(
            message: 'Transfer completed',
            data: ['transfer' => new TransferResource($transfer)]);
    }
}
