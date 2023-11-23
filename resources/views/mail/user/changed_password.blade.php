<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ __('Your password has been changed') }}}</title>
</head>
<body>
@if ($user->name)
    <p>{{ __('Hello') }}, {{ $user->name }}</p>
@else
    <p>{{ __('Hello') }}!</p>
@endif
<p>{{ __('Your password has been changed') }}</p>
</body>
</html>
