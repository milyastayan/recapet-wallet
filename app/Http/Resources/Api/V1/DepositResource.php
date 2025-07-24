<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepositResource extends JsonResource
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
            'new_balance' => $this->new_balance,
            'wallet' => new WalletResource($this->whenLoaded('wallet')),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
