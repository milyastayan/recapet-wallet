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
