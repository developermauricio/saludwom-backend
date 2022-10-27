@component('mail::message')
# Account activation

Follow this link to activate your account.

@component('mail::button', ['url' => route('api.v1.activate.account', $token)])
Activate your account
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

