<x-mail::message>
<p>Hi{($user->name)},</p>

<p>We received a request to verify you account on Penge through your e-mail address. Your verification code  is </p>
Thanks,<br>
<p><strong>{($code)}</strong></p>
{{ config('app.name') }}
</x-mail::message>
