<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ __('Activate your account') }}}</title>
</head>
<body>
@if ($user->name)
    <p>{{ __('Hello') }}, {{ $user->name }}</p>
@else
    <p>{{ __('Hello') }}!</p>
@endif
<a href="{{ config('app.front_url') . '/activate?token=' .  $token }}">{{ __('Click this link to activate your account') }}</a>
<p>{{ __('If you don\'t see the link, copy this URL to browser') }}
    : {{ config('app.front_url') . '/activate?token=' .  $token }}</p>
</body>
</html>
