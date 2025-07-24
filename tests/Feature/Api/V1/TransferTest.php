<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->sender = User::factory()->create();
    $this->receiver = User::factory()->create();

    Sanctum::actingAs($this->sender);

    $this->sender->wallet->update(['balance' => 10000]);
});

it('transfers funds successfully without fee (amount <= 25)', function () {
    $response = $this->postJson($this->baseUrl . '/wallet/transfer', [
        'recipient_uuid' => $this->receiver->wallet->uuid,
        'amount' => 2500,
    ]);

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
    ])->assertOk();

    expect($this->sender->fresh()->wallet->balance)->toBe(10000 - $expectedTotal)
        ->and($this->receiver->fresh()->wallet->balance)->toBe(5000);

    $response->assertJsonPath('data.transfer.fee', $expectedFee);
});

it('fails to transfer if insufficient balance', function () {
    $this->postJson($this->baseUrl . '/wallet/transfer', [
        'recipient_uuid' => $this->receiver->wallet->uuid,
        'amount' => 20000,
    ])->assertStatus(500);
    expect($this->sender->fresh()->wallet->balance)->toBe(10000)
        ->and($this->receiver->fresh()->wallet->balance)->toBe(0);
});

it('fails if recipient wallet does not exist', function () {
    $this->postJson($this->baseUrl . '/wallet/transfer', [
        'recipient_uuid' => 'invalid-uuid',
        'amount' => 1000,
    ])->assertNotFound();
});
