@extends('admin.layouts.panel')

@section('title', 'Пользователи')
@section('description', 'Учётные записи публичного сайта. Игровые аккаунты и персонажи подключаются отдельно.')

@section('content')
<div class="users-summary">
    <div class="content-stat">
        <span>Всего</span>
        <strong>{{ $totalCount }}</strong>
    </div>
    <div class="content-stat">
        <span>Активных</span>
        <strong>{{ $activeCount }}</strong>
    </div>
    <div class="content-stat">
        <span>Отключённых</span>
        <strong>{{ $inactiveCount }}</strong>
    </div>
    <div class="content-stat">
        <span>Без подтверждения</span>
        <strong>{{ $unverifiedCount }}</strong>
    </div>
    <p>Этот раздел управляет только пользователями CMS. Он не создаёт и не изменяет аккаунты Login Server.</p>
</div>

<form class="users-filters" method="GET" action="{{ route('admin.users.index') }}">
    <div class="users-search-field">
        <label for="users-search">Поиск</label>
        <input id="users-search" type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="Логин или email">
    </div>

    <div>
        <label for="users-status">Статус</label>
        <select id="users-status" name="status">
            <option value="">Все</option>
            <option value="active" @selected($activeStatus === 'active')>Активные</option>
            <option value="inactive" @selected($activeStatus === 'inactive')>Отключённые</option>
        </select>
    </div>

    <div>
        <label for="users-verification">Email</label>
        <select id="users-verification" name="verification">
            <option value="">Любой статус</option>
            <option value="verified" @selected($activeVerification === 'verified')>Подтверждён</option>
            <option value="unverified" @selected($activeVerification === 'unverified')>Не подтверждён</option>
        </select>
    </div>

    <button class="button button-primary" type="submit">Применить</button>

    @if ($search !== '' || $activeStatus !== '' || $activeVerification !== '')
        <a class="button button-secondary" href="{{ route('admin.users.index') }}">Сбросить</a>
    @endif
</form>

@if ($users->isEmpty())
    <div class="empty-state">
        <div class="empty-state-mark" aria-hidden="true">U</div>
        <h2>Пользователи не найдены</h2>
        <p>Измените условия поиска или дождитесь первой регистрации на сайте.</p>
        @if ($search !== '' || $activeStatus !== '' || $activeVerification !== '')
            <a class="button button-secondary" href="{{ route('admin.users.index') }}">Показать всех</a>
        @endif
    </div>
@else
    <div class="users-list">
        <div class="user-row user-row-header">
            <span>Пользователь</span>
            <span>Email</span>
            <span>Регистрация</span>
            <span>Последний вход</span>
            <span>Статус</span>
            <span></span>
        </div>

        @foreach ($users as $user)
            <article class="user-row">
                <div class="user-list-identity">
                    <strong>{{ $user->name }}</strong>
                    <small>ID {{ $user->id }}</small>
                </div>

                <div class="user-list-email">
                    <span>{{ $user->email }}</span>
                    <small @class([
                        'verified' => $user->hasVerifiedEmail(),
                        'unverified' => ! $user->hasVerifiedEmail(),
                    ])>{{ $user->hasVerifiedEmail() ? 'Email подтверждён' : 'Email не подтверждён' }}</small>
                </div>

                <time datetime="{{ $user->created_at?->toAtomString() }}">
                    {{ $user->created_at?->format('d.m.Y H:i') ?? '—' }}
                </time>

                <time datetime="{{ $user->last_login_at?->toAtomString() }}">
                    {{ $user->last_login_at?->format('d.m.Y H:i') ?? 'Никогда' }}
                </time>

                <div>
                    <span @class([
                        'status-badge',
                        'status-badge-success' => $user->is_active,
                        'status-badge-muted' => ! $user->is_active,
                    ])>{{ $user->is_active ? 'Активен' : 'Отключён' }}</span>
                </div>

                <div class="user-list-action">
                    <a class="button button-secondary" href="{{ route('admin.users.show', $user) }}">Подробнее</a>
                </div>
            </article>
        @endforeach
    </div>

    @if ($users->hasPages())
        @php
            $firstPage = max(1, $users->currentPage() - 2);
            $lastPage = min($users->lastPage(), $users->currentPage() + 2);
        @endphp

        <nav class="simple-pagination" aria-label="Навигация по страницам пользователей">
            @if ($users->onFirstPage())
                <span class="button button-secondary disabled">← Назад</span>
            @else
                <a class="button button-secondary" href="{{ $users->previousPageUrl() }}" rel="prev">← Назад</a>
            @endif

            <div class="pagination-pages" aria-label="Страницы">
                @foreach ($users->getUrlRange($firstPage, $lastPage) as $page => $url)
                    @if ($page === $users->currentPage())
                        <span class="pagination-page active" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="pagination-page" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            </div>

            @if ($users->hasMorePages())
                <a class="button button-secondary" href="{{ $users->nextPageUrl() }}" rel="next">Вперёд →</a>
            @else
                <span class="button button-secondary disabled">Вперёд →</span>
            @endif
        </nav>
    @endif
@endif
@endsection
