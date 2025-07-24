<?php

use App\Enums\WithdrawalStatus;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
    $this->wallet = $this->user->wallet;
    $this->wallet->update(['balance' => 1000]);

    $this->baseUrl =  $this->baseUrl . '/wallet/withdrawals';

    $this->headers = ['Accept' => 'application/json'];
});

it('successfully withdraws an amount', function () {
    $response = $this->postJson($this->baseUrl, [
        'amount' => 500
    ], $this->headers);

    $response->assertOk()
        ->assertJsonPath('data.withdrawal.amount', 500)
        ->assertJsonPath('data.withdrawal.status', WithdrawalStatus::Succeeded->value);

    expect($this->user->fresh()->wallet->balance)->toBe(500);
});

it('fails to withdraw when balance is insufficient', function () {
    $response = $this->postJson($this->baseUrl, [
        'amount' => 1500
    ], $this->headers);

    $response->assertOk()
        ->assertJsonPath('data.withdrawal.status', WithdrawalStatus::Failed->value);

    expect($this->user->fresh()->wallet->balance)->toBe(1000);
});

it('prevents double spending under concurrent withdrawals', function () {
    $this->wallet->update(['balance' => 1000]);

    $requests = collect(range(1, 5))->map(function () {
        return fn () => $this->postJson($this->baseUrl, ['amount' => 300], $this->headers);
    });

    $responses = $requests->map(fn ($request) => $request());

    $succeeded = $responses->filter(fn ($res) => $res->json('data.withdrawal.status') === WithdrawalStatus::Succeeded->value);
    $failed = $responses->filter(fn ($res) => $res->json('data.withdrawal.status') === WithdrawalStatus::Failed->value);

    expect($succeeded)->toHaveCount(3)
        ->and($failed->count())->toBe(2)
        ->and($this->wallet->fresh()->balance)->toBe(100);
});
