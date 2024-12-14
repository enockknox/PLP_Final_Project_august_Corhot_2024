<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\AuthService; // Import AuthService
use App\Http\Resources\UserResource; // Import UserResource
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request): Response
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|max:255',
        ]);

        $user = $this->authService->register($validated);

        $token = $user->createToken('auth')->plainTextToken;

        return response([
            'message' => __('app.registration_success'),
            'results' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
        ], 201);
    }

    public function login(Request $request): Response
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6|max:255',
        ]);

        $user = $this->authService->login($validated);

        if (!$user) {
            return response([
                'message' => __('auth.failed'),
            ], 401);
        }

        $token = $user->createToken('auth')->plainTextToken;

        return response([
            'message' => __('auth.login_success'),
            'results' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
        ], 200);
    }

    public function otp(Request $request): Response
    {
        $user = $this->authService->verifyOtp($request);

        if (!$user) {
            return response([
                'message' => __('auth.failed'),
            ], 401);
        }

        $token = $user->createToken('auth')->plainTextToken;

        return response([
            'message' => __('auth.otp_verified'),
            'results' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
        ], 200);
    }

    public function verify(Request $request): Response
    {
        $validated = $request->validate([
            'otp' => 'required|numeric',
        ]);

        $user = $this->authService->verifyOtp($validated);

        if (!$user) {
            return response([
                'message' => __('auth.failed'),
            ], 401);
        }

        $token = $user->createToken('auth')->plainTextToken;

        return response([
            'message' => __('auth.otp_verified'),
            'results' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
        ], 200);
    }

    public function resetOtp(Request $request): Response
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255|exists:users,email',
        ]);

        $user = $this->authService->getUserByEmail($validated['email']);

        if (!$user) {
            return response([
                'message' => __('auth.user_not_found'),
            ], 404);
        }

        $this->authService->sendOtp($user);

        return response([
            'message' => __('auth.otp_sent'),
        ], 200);
    }

    public function resetPassword(Request $request): Response
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|numeric',
            'password' => 'required|string|confirmed|min:6|max:255',
        ]);

        $user = $this->authService->getUserByEmail($validated['email']);

        if (!$user || !$this->authService->validateOtp($user, $validated['otp'])) {
            return response([
                'message' => __('auth.invalid_otp'),
            ], 401);
        }

        $this->authService->resetPassword($user, $validated['password']);

        return response([
            'message' => __('auth.password_reset_success'),
        ], 200);
    }
}
