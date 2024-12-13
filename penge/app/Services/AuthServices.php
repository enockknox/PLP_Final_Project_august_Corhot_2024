<?php

namespace App\Services;

use App\Models\User;
use App\Models\Otp;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Register a new user.
     */
    public function register(object $request): User
    {
        $user = User::create([
            'uuid' => Str::uuid(),
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Hash the password
        ]);
        // send verification
        $this->otp($user);

        return $user;
    }

    /**
     * Login a user.
     */
    public function login(object $request): ?User
    {
        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            return $user;
        }

        return null; // Return null if authentication fails
    }

    /**
     * Generate an OTP for a user.
     */
    public function otp(User $user, string $type = 'verification'): Otp
    {
        $code = random_int(100000, 999999);

        $otp = Otp::create([
            'user_id' => $user->id,
            'type' => $type,
            'code' => $code,
            'active' => 1
        ]);
        
        // send mail
        Mail::to($user)->send(new OtpMail($user, $code));

        return $otp;
    }

    public function verify(User $user, object $request) :User 
    {
        $otp = Otp::where([
            'user_id' => $user->id,
            'codde' => $request->code,
            'active' =>1
    ])->first();

    if(!$otp)
    {
        abort(422,__('app.invalid_otp'));
    }

    // update
    $user->email_verified_at = Carbon::now();
    $user->update();

    $otp->active = 0;
    $otp->updated_at = Carbon::now();
    $otp->update();

    return $user;
    }
    public function getUserByEmail(string $email): User
    {
        return User::where('email', $email)->first();

    }
}
