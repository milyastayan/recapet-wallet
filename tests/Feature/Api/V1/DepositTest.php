<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('allows a user to deposit money into their wallet', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson($this->baseUrl . '/wallet/deposit', [
        'amount' => 500,
    ], $this->headers)->assertOk()
        ->assertJsonStructure([
            'message',
            'data' => [
                'deposit' => ['id', 'amount', 'new_balance', 'created_at'],
            ],
        ]);

    expect($user->fresh()->wallet->balance)->toBe(500);
});

it('safely handles multiple sequential deposits simulating concurrency', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $depositAmount = 100;
    $requests = 5;

    $this->headers['Accept'] = 'application/json';

    foreach (range(1, $requests) as $_) {
        $this->postJson($this->baseUrl . '/wallet/deposit', [
            'amount' => $depositAmount,
        ], $this->headers)->assertOk();
    }

    expect($user->fresh()->wallet->balance)->toBe($depositAmount * $requests);
});
