<?php

namespace App\Services;
use Illuminate\Support\Str;

class AuthService
{
    public function register(object $request): User{
        $user = User::create([
            'uuid' => Str::uuid(),
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);
        return $user;
    }


public function login(object $request): User{
    $user = User::where('email', $request->email)->first();
    if(!$user || Hash::check($request->password))
    return $user;
}
}