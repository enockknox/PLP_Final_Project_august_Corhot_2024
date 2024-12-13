<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\AuthService; // Import your AuthService
use App\Http\Resources\UserResource; // Import UserResource

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request): Response
    {
        // Validate request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:6|max:255',
        ]);
        // Create user
        $user = $this->authService->register($request);

        // Create access token
        $token = $user->createToken('auth')->plainTextToken;

        // Return response
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
        // Validate request
        $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|min:6|max:255',
        ]);

        // Login user
        $user = $this->authService->login($request);

        if (!$user) {
            return response([
                'message' => __('auth.failed'),
            ], 401);
        }

        // Create access token
        $token = $user->createToken('auth')->plainTextToken;

        // Return response
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
        // Get user
        $user = $this->authService->verifyOtp($request);

        if (!$user) {
            return response([
                'message' => __('auth.failed'),
            ], 401);
        }

        // Create access token
        $token = $user->createToken('auth')->plainTextToken;

        // Return response
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
        // validate the request
        $request->validate([
            'otp' => 'required|numeric'
        ]);
        // Get user
        $user = $this->authService->verifyOtp($request);

        if (!$user) {
            return response([
                'message' => __('auth.failed'),
            ], 401);
        }

        // Create access token
        $token = $user->createToken('auth')->plainTextToken;

        // verify otp
        $user = $this->authService->verify($user, $request);
        // Return response
        return response([
            'message' => __('auth.otp_verified'),
            'results' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
        ], 200);
    }

    public function restOtp (Request $request): Response
    {
        // Validate request
        $request->validate([
            'email' => 'required|email|max:255|exists:users,email'
        ]);
        if (!$user) {
            return response([
                'message' => __('auth.failed'),
            ], 401);
        }

        // get user
        $user = $this->authService->getUserByEmail($request->email);

        // Create access token
        $token = $user->createToken('auth')->plainTextToken,'password-reset';

        // Return response
        return response([
            'message' => __('auth.otp_reset_successfully'),
            'results' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
        ], 200);
    }
}
