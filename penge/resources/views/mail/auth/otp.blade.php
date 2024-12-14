<x-mail::message>
Hi{{$user->name}},
Your 6-digit code is:
<strong>{{$otp->code}}</strong>

@if($otp->type == 'password_reset')
<p>Use this code to reset your password in the app.</p>
@else
    Use this code to complete the verification process in the app.
@endif

Do not share this code. Penge representative will never reach out to you to verify this code over SMS.
<p> Your verification code  is </p>
Thanks,<br>
<strong>The code is valid for 10 minutes.</strong>
{{ config('app.name') }}
</x-mail::message>
