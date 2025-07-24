<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'fee' => $this->fee,
            'sender_wallet_id' => $this->sender_wallet_id,
            'receiver_wallet_id' => $this->receiver_wallet_id,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
