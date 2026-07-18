<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ locale_direction() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="theme-color" content="#090d13">
    @if (site_favicon_url())
        <link rel="icon" href="{{ site_favicon_url() }}">
    @endif
    <title>@yield('title', __('Personal account')) — {{ site_name() }}</title>
    <link rel="stylesheet" href="{{ asset('assets/account/css/app.css') }}?v={{ cms_version() }}" data-navigate-track>
    <script src="{{ asset('assets/account/js/navigation.js') }}?v={{ cms_version() }}" defer data-navigate-track data-navigate-once></script>
    @livewireStyles
    @stack('head')
</head>
<body class="account-body">
<div class="account-shell">
    @persist('account-sidebar')
        <aside class="account-sidebar" data-account-sidebar wire:navigate:scroll>
            <a wire:navigate.hover class="account-brand" href="{{ public_route('account') }}">
                @if (site_logo_url())
                    <img src="{{ site_logo_url() }}" alt="{{ site_name() }}">
                @else
                    <span class="account-brand-mark">L2</span>
                @endif
                <span><strong>{{ site_name() }}</strong><small>{{ __('Player account') }}</small></span>
            </a>

            @include('account.partials.navigation')

            <div class="account-sidebar-footer">
                <a href="{{ public_route('home') }}">← {{ __('Back to website') }}</a>
                <span>{{ __('Version :version', ['version' => cms_version()]) }}</span>
            </div>
        </aside>
    @endpersist

    <main class="account-main">
        @persist('account-topbar')
            <header class="account-topbar" data-account-topbar>
                <button class="account-sidebar-toggle" type="button" data-account-sidebar-toggle aria-label="{{ __('Player account navigation') }}" aria-expanded="false">
                    <span></span><span></span><span></span>
                </button>

                <div class="account-topbar-context">
                    <span>{{ __('Player account') }}</span>
                    <strong>{{ $user->name }}</strong>
                </div>

                <details class="account-profile-menu">
                    <summary>
                        <span class="account-profile-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                        <span class="account-profile-copy">
                            <strong>{{ $user->name }}</strong>
                            <small>{{ $user->email }}</small>
                        </span>
                        <span class="account-profile-chevron" aria-hidden="true">⌄</span>
                    </summary>
                    <div class="account-profile-dropdown">
                        <a wire:navigate href="{{ public_route('account') }}">{{ __('Overview') }}</a>
                        <a wire:navigate href="{{ public_route('game-accounts.index') }}">{{ __('Game accounts') }}</a>
                        <a href="{{ public_route('home') }}">{{ __('Back to website') }}</a>
                        <form method="POST" action="{{ public_route('logout') }}">
                            @csrf
                            <button type="submit">{{ __('Sign out') }}</button>
                        </form>
                    </div>
                </details>
            </header>
        @endpersist

        <div class="account-content" data-account-content>
            @if (session('status'))
                <div class="account-notice success" role="status">{{ session('status') }}</div>
            @endif
            @if (session('warning'))
                <div class="account-notice warning" role="alert">{{ session('warning') }}</div>
            @endif
            @if ($errors->any() && ! trim($__env->yieldContent('inline-validation-errors')))
                <div class="account-notice error" role="alert">
                    @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif
            @yield('content')
        </div>
    </main>
</div>
@livewireScripts
@stack('scripts')
</body>
</html>
