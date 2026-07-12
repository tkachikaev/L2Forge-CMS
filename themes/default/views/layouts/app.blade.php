<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#090d10">
    <title>@yield('title', config('app.name'))</title>
    <link rel="stylesheet" href="{{ theme_asset('css/app.css') }}">
</head>
<body>
    @include('theme::partials.header')
    <main>@yield('content')</main>
    @include('theme::partials.footer')
    <script src="{{ theme_asset('js/app.js') }}" defer></script>
</body>
</html>
