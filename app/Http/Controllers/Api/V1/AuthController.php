<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse(
            message: 'Your account was created successfully',
            data: [
                'user' => new UserResource($user),
                'access_token' => $token,
            ],
            code: Response::HTTP_CREATED
        );
    }

    /**
     * Login user and create token.
     */
    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = User::query()
            ->where('email', $request->get('email'))
            ->with('wallet')
            ->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse(
            message: 'Logged in successfully',
            data: [
                'user' => new UserResource($user),
                'access_token' => $token,
            ],
        );
    }

    /**
     * Logout user (revoke token).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse('Logged out successfully');
    }

    /**
     * Get authenticated user info.
     */
    public function me()
    {
        $user = Auth::user();
        $user->load([
            'wallet',
        ]);
        return $this->successDataResponse([
            'user' => UserResource::make($user),
        ]);
    }
}
