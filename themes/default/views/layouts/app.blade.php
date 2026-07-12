<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#090d10">
    @if (site_description() !== '')
        <meta name="description" content="{{ site_description() }}">
    @endif
    @if (site_favicon_url())
        <link rel="icon" href="{{ site_favicon_url() }}">
    @endif
    @if (! empty($isPreview))
        <meta name="robots" content="noindex, nofollow, noarchive">
    @endif
    <title>@yield('title', site_name())</title>
    <link rel="stylesheet" href="{{ theme_asset('css/app.css') }}">
</head>
<body>
    @if (! empty($isPreview))
        <aside class="news-preview-banner" role="status">
            <strong>Предпросмотр новости</strong>
            <span>Изменения не сохранены и не опубликованы. После проверки закройте эту вкладку.</span>
        </aside>
    @endif

    @include('theme::partials.header')
    <main>@yield('content')</main>
    @include('theme::partials.footer')
    <script src="{{ theme_asset('js/app.js') }}" defer></script>
</body>
</html>
