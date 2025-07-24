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
            'amount' => number_format($this->amount / 100, 2),
            'new_balance' => number_format($this->new_balance / 100, 2),
            'wallet' => new WalletResource($this->whenLoaded('wallet')),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
