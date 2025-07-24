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
            'amount' => number_format($this->amount / 100, 2),
            'fee' => number_format($this->fee / 100, 2),
            'sender_wallet_id' => $this->sender_wallet_id,
            'receiver_wallet_id' => $this->receiver_wallet_id,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
