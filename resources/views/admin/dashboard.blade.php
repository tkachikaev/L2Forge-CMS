@extends('admin.layouts.app')

@section('title', 'Обзор')

@section('body')
<div class="dashboard-shell">
    <aside class="sidebar">
        <a class="brand brand-sidebar" href="{{ route('admin.dashboard') }}">
            <span class="brand-mark">L2</span>
            <span><strong>{{ config('app.name') }}</strong><small>ADMINISTRATION</small></span>
        </a>

        <nav class="admin-nav" aria-label="Панель управления">
            <a class="active" href="{{ route('admin.dashboard') }}">Обзор</a>
            <span>Настройки <small>скоро</small></span>
            <span>Новости <small>скоро</small></span>
            <span>Темы <small>скоро</small></span>
            <span>Модули <small>скоро</small></span>
            <span>Администраторы <small>скоро</small></span>
        </nav>

        <a class="back-link" href="{{ route('home') }}">← Перейти на сайт</a>
    </aside>

    <main class="dashboard-main">
        <header class="topbar">
            <div>
                <p class="eyebrow">ПАНЕЛЬ УПРАВЛЕНИЯ</p>
                <h1>Обзор</h1>
            </div>
            <div class="admin-profile">
                <span><strong>{{ $admin->name }}</strong><small>{{ $admin->email }}</small></span>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="secondary-button">Выйти</button>
                </form>
            </div>
        </header>

        <section class="stats-grid" aria-label="Состояние CMS">
            <article class="stat-card"><span>Версия CMS</span><strong>{{ config('cms.version') }}</strong><small>Текущее ядро</small></article>
            <article class="stat-card"><span>Администраторы</span><strong>{{ $adminCount }}</strong><small>Активные учётные записи</small></article>
            <article class="stat-card"><span>Новости</span><strong>{{ $newsCount }}</strong><small>Записей в базе CMS</small></article>
            <article class="stat-card"><span>Игровой адаптер</span><strong>{{ strtoupper(config('game.adapter')) }}</strong><small>Текущий режим подключения</small></article>
        </section>

        <section class="dashboard-grid">
            <article class="content-card">
                <div class="card-heading">
                    <div><p class="eyebrow">БЕЗОПАСНОСТЬ</p><h2>Последние попытки входа</h2></div>
                </div>

                @if ($recentLogins->isEmpty())
                    <p class="empty-state">Журнал пока пуст.</p>
                @else
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Дата</th><th>Адрес</th><th>IP</th><th>Результат</th></tr></thead>
                            <tbody>
                            @foreach ($recentLogins as $login)
                                <tr>
                                    <td>{{ $login->created_at->format('d.m.Y H:i') }}</td>
                                    <td>{{ $login->email }}</td>
                                    <td>{{ $login->ip_address ?: '—' }}</td>
                                    <td><span class="status {{ $login->successful ? 'success' : 'failed' }}">{{ $login->successful ? 'Успешно' : 'Отклонено' }}</span></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </article>

            <aside class="content-card next-card">
                <p class="eyebrow">СЛЕДУЮЩИЙ ЭТАП</p>
                <h2>Системные настройки</h2>
                <p>Следом добавим управление названием сайта, сервером, режимом обслуживания и активной темой.</p>
                <ul>
                    <li>Отдельная учётная запись администратора</li>
                    <li>Argon2id для хранения пароля</li>
                    <li>Ограничение попыток входа</li>
                    <li>Журнал успешных и неудачных входов</li>
                </ul>
            </aside>
        </section>
    </main>
</div>
@endsection
