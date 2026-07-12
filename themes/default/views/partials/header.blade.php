<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="{{ route('home') }}" aria-label="На главную">
            <span class="brand-mark">L2</span>
            <span><strong>{{ config('app.name') }}</strong><small>LINEAGE II</small></span>
        </a>
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="main-menu">Меню</button>
        <nav id="main-menu" class="main-nav" aria-label="Основная навигация">
            <a class="{{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Главная</a>
            <a class="{{ request()->routeIs('news.*') ? 'active' : '' }}" href="{{ route('news.index') }}">Новости</a>
            <a href="#rating">Статистика</a>
            <a href="{{ route('downloads') }}">Файлы</a>
            <a href="{{ route('about') }}">О сервере</a>
        </nav>
        <div class="header-actions"><a class="button button-ghost" href="{{ route('login') }}">Вход</a><a class="button button-gold" href="{{ route('register') }}">Регистрация</a></div>
    </div>
</header>
