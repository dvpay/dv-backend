<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ __('You are invited') }}}</title>
</head>
<body>

<p>{{ __('Hello') }}, {{ $invited->name }}</p>

You are invited by {{ $invite->email }}
</body>
</html>
