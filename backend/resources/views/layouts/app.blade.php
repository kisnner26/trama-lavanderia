<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'trama · mostrador')</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('trama.css') }}">
</head>
<body class="@yield('body-class')">
<a href="#main" class="skip-link">saltar al contenido</a>
@yield('content')
</body>
</html>
