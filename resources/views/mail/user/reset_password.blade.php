<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ __('Password reset request') }}}</title>
</head>
<body>
@if ($user->name)
    <p>{{ __('Hello') }}, {{ $user->name }}</p>
@else
    <p>{{ __('Hello') }}!</p>
@endif
<a href="{{ config('app.front_url') . '/password-reset?token=' .  $token }}">{{ __('Click this link to reset your password') }}</a>
<p>{{ __('If you don\'t see the link, copy this URL to browser') }}
    : {{ config('app.front_url') . '/password-reset?token=' .  $token }}</p>
</body>
</html>
