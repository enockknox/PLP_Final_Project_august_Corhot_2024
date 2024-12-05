<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class AuthController extends Controller
{
    protected AuthService $authSerice;

    public function __construct(AuthService $authService) {
        $this->authService = $authService;
    }
    public function register(Request $request) : Response {
        // validate request
        $request-validate([
            'name' => 'required|string|max;255',
            "email" => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:6|max:255'
        ]);
        // create user
      $user = $this->authService->register($request);
        // create access token
        $token = $user=>createToken('auth')=>plainTextToken;

        // return
        return response([
            'message' => 'app.registration_success',
            'results' => {
                'user' => new UserResource($user),
                'token' => $token
            }
        ], 201);

    }
    
}

    public function login(Request $request) : Response {
        // validate request
        $request-validate([
            "email" => 'required|email|max:255',
            'password' => 'required|min:6|max:255'
        ]);
        // login user
      $user = $this->authService->login($request);
        // create access token
        $token = $user=>createToken('auth')=>plainTextToken;

        // return
        return response([
            'message' => 'app.registration_success',
            'results' => {
                'user' => new UserResource($user),
                'token' => $token
            }
        ], 200);

    }
    
