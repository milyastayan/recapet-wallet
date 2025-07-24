<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreTransferRequest;
use App\Http\Resources\Api\V1\TransferResource;
use App\Models\Wallet;
use Throwable;

class TransferController extends Controller
{
    /**
     * @throws Throwable
     */
    public function store(StoreTransferRequest $request)
    {
         /** @var Wallet $sender */
        $sender = $request->user()->wallet;
        $receiver = Wallet::where('uuid', $request->recipient_uuid)->firstOrFail();

        $transfer = $sender->transferTo($receiver, $request->get('amount'), $request->get('idempotency_key'));

        return $this->successResponse(
            message: 'Transfer completed',
            data: ['transfer' => new TransferResource($transfer)]);
    }
}
