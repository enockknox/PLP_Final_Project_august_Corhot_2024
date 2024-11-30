<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register() {
        // validate request
        $request-validate([
            'name' => 'required|string|max;255',
            "email" => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:6|max:255'
        ])
        // create user

        // create access token

        // return

    }
    
}
