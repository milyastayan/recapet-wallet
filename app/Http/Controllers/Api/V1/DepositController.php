<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Requests\Api\V1\StoreDepositRequest;
use App\Http\Resources\Api\V1\DepositResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DepositController extends Controller
{
    public function store(StoreDepositRequest $request)
    {
        /** @var User $user */
        $user = $request->user();
        /** @var Wallet $wallet */
        $wallet = $user->wallet;

        $deposit = $wallet->deposit($request->validated('amount'));

        return $this->successResponse(
            message: 'Deposit successful',
            data: [
                'deposit' =>new DepositResource($deposit),
            ],
        );
    }
}
