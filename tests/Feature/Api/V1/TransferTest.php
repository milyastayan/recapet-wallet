<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->sender = User::factory()->create();
    $this->receiver = User::factory()->create();

    Sanctum::actingAs($this->sender);

    $this->sender->wallet->update(['balance' => 10000]);

    $this->headers = [
        'Idempotency-Key' => Str::uuid()->toString(),
    ];
});

it('transfers funds successfully without fee (amount <= $25)', function () {
    $response = $this->postJson($this->baseUrl . '/wallet/transfer', [
        'recipient_uuid' => $this->receiver->wallet->uuid,
        'amount' => 2500,
    ], $this->headers);

    $response->assertOk()
        ->assertJsonStructure([
            'message',
            'data' => [
                'transfer' => ['id', 'amount', 'fee', 'sender_wallet_id', 'receiver_wallet_id', 'created_at'],
            ],
        ]);

    expect($this->sender->fresh()->wallet->balance)->toBe(7500)
        ->and($this->receiver->fresh()->wallet->balance)->toBe(2500);
});

it('transfers funds with fee when amount > 25', function () {
    $amount = 5000;
    $expectedFee = 250 + intval($amount * 0.10);
    $expectedTotal = $amount + $expectedFee;

    $response = $this->postJson($this->baseUrl . '/wallet/transfer', [
        'recipient_uuid' => $this->receiver->wallet->uuid,
        'amount' => $amount,
    ], $this->headers)->assertOk();

    expect($this->sender->fresh()->wallet->balance)->toBe(10000 - $expectedTotal)
        ->and($this->receiver->fresh()->wallet->balance)->toBe(5000);

    $response->assertJsonPath('data.transfer.fee', number_format($expectedFee / 100, 2));
});

it('fails to transfer if insufficient balance', function () {
    $this->postJson($this->baseUrl . '/wallet/transfer', [
        'recipient_uuid' => $this->receiver->wallet->uuid,
        'amount' => 20000,
    ], $this->headers)->assertStatus(500);
    expect($this->sender->fresh()->wallet->balance)->toBe(10000)
        ->and($this->receiver->fresh()->wallet->balance)->toBe(0);
});

it('fails if recipient wallet does not exist', function () {
    $this->postJson($this->baseUrl . '/wallet/transfer', [
        'recipient_uuid' => 'invalid-uuid',
        'amount' => 1000,
    ], $this->headers)->assertUnprocessable();
});

it('returns same transfer if duplicate idempotency key used', function () {
    $payload = [
        'recipient_uuid' => $this->receiver->wallet->uuid,
        'amount' => 1500,
    ];

    $first = $this->postJson($this->baseUrl . '/wallet/transfer', $payload, $this->headers)->assertOk();

    $second = $this->postJson($this->baseUrl . '/wallet/transfer', $payload, $this->headers)->assertOk();

    $firstId = $first->json('data.transfer.id');
    $secondId = $second->json('data.transfer.id');

    expect($firstId)->toBe($secondId);
});

it('safely handles multiple sequential transfers simulating concurrency', function () {
    $sender = User::factory()->create();
    $receiver = User::factory()->create();

    Sanctum::actingAs($sender);

    $sender->wallet->update(['balance' => 10000]);

    $transferAmount = 1000;
    $requests = 5;

    $this->headers['Accept'] = 'application/json';

    foreach (range(1, $requests) as $_) {
        $this->postJson($this->baseUrl . '/wallet/transfer', [
            'recipient_uuid' => $receiver->wallet->uuid,
            'amount' => $transferAmount,
        ], [
            'Idempotency-Key' => Str::uuid()->toString(),
            'Accept' => 'application/json',
        ])->assertOk();
    }

    expect($sender->fresh()->wallet->balance)->toBe(10000 - $requests * $transferAmount)
        ->and($receiver->fresh()->wallet->balance)->toBe($requests * $transferAmount);
});
