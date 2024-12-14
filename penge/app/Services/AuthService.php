<?php

namespace App\Services;

use App\Models\User;
use App\Models\Otp;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\OtpMail;

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

        // send verification OTP
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
        // Throttle OTP requests
        $tries = 3;
        $time = Carbon::now()->subMinutes(30);

        $count = Otp::where([
            'user_id' => $user->id,
            'type' => $type,
            'active' => 1
        ])->where('created_at', '>=', $time)->count();

        if ($count >= $tries) {
            abort(422, 'Too many OTP requests');
        }

        $code = random_int(100000, 999999);

        $otp = Otp::create([
            'user_id' => $user->id,
            'type' => $type,
            'code' => $code,
            'active' => 1,
        ]);

        // Send OTP via email
        Mail::to($user)->send(new OtpMail($user, $otp));

        return $otp;
    }

    /**
     * Verify an OTP for a user.
     */
    public function verify(User $user, object $request): User
    {
        $otp = Otp::where([
            'user_id' => $user->id,
            'code' => $request->code,
            'active' => 1
        ])->first();

        if (!$otp) {
            abort(422, __('app.invalid_otp'));
        }

        // Update user verification status
        $user->email_verified_at = Carbon::now();
        $user->save();

        // Deactivate OTP
        $otp->active = 0;
        $otp->updated_at = Carbon::now();
        $otp->save();

        return $user;
    }

    /**
     * Retrieve a user by email.
     */
    public function getUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Reset a user's password.
     */
    public function resetPassword(User $user, object $request): User
    {
        // Validate OTP
        $otp = Otp::where([
            'user_id' => $user->id,
            'code' => $request->otp,
            'active' => 1,
            'type' => 'password-reset'
        ])->first();

        if (!$otp) {
            abort(422, __('app.invalid_otp'));
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->updated_at = Carbon::now();
        $user->save();

        // Deactivate OTP
        $otp->active = 0;
        $otp->updated_at = Carbon::now();
        $otp->save();

        return $user;
    }
}
