@extends('admin.layouts.panel')

@section('title', 'Администраторы')
@section('description', 'Учётные записи с полным доступом к панели управления L2Forge CMS.')

@section('content')
<div class="administrators-toolbar">
    <div class="content-stat">
        <span>Всего</span>
        <strong>{{ $totalCount }}</strong>
    </div>
    <div class="content-stat">
        <span>Активных</span>
        <strong>{{ $activeCount }}</strong>
    </div>
    <p>Все администраторы имеют одинаковые права доступа.</p>
    <a class="button button-primary" href="{{ route('admin.administrators.create') }}">Создать администратора</a>
</div>

<div class="notice notice-warning administrators-notice">
    <p>Учётные записи не удаляются: ненужного администратора можно отключить. Это сохраняет историю его действий в журнале.</p>
</div>

<div class="administrators-list">
    <div class="administrator-row administrator-row-header">
        <span>Администратор</span>
        <span>Создан</span>
        <span>Последний вход</span>
        <span>Статус</span>
        <span>Действия</span>
    </div>

    @foreach ($administrators as $administrator)
        @php
            $isCurrent = $currentAdmin?->is($administrator) ?? false;
            $canDisable = $administrator->is_active && ! $isCurrent && $activeCount > 1;
        @endphp

        <article class="administrator-row">
            <div class="administrator-identity">
                <strong>{{ $administrator->name }}</strong>
                <span>{{ $administrator->email }}</span>
                @if ($isCurrent)
                    <small>Текущая учётная запись</small>
                @endif
            </div>

            <time datetime="{{ $administrator->created_at?->toAtomString() }}">
                {{ $administrator->created_at?->format('d.m.Y H:i') ?? '—' }}
            </time>

            <time datetime="{{ $administrator->last_login_at?->toAtomString() }}">
                {{ $administrator->last_login_at?->format('d.m.Y H:i') ?? 'Никогда' }}
            </time>

            <div>
                <span @class([
                    'status-badge',
                    'status-badge-success' => $administrator->is_active,
                    'status-badge-muted' => ! $administrator->is_active,
                ])>
                    {{ $administrator->is_active ? 'Активен' : 'Отключён' }}
                </span>
            </div>

            <div class="administrator-actions">
                <a class="button button-secondary" href="{{ route('admin.administrators.edit', $administrator) }}">Изменить</a>

                @if ($administrator->is_active)
                    <form method="POST" action="{{ route('admin.administrators.status', $administrator) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="is_active" value="0">
                        <button
                            class="button button-danger"
                            type="submit"
                            @disabled(! $canDisable)
                            title="{{ $isCurrent ? 'Нельзя отключить собственную учётную запись' : ($activeCount <= 1 ? 'Нельзя отключить последнего активного администратора' : 'Отключить администратора') }}"
                        >Отключить</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.administrators.status', $administrator) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="is_active" value="1">
                        <button class="button button-primary" type="submit">Включить</button>
                    </form>
                @endif
            </div>
        </article>
    @endforeach
</div>

@if ($administrators->hasPages())
    <div class="simple-pagination">
        @if ($administrators->onFirstPage())
            <span class="button button-secondary disabled">← Назад</span>
        @else
            <a class="button button-secondary" href="{{ $administrators->previousPageUrl() }}" rel="prev">← Назад</a>
        @endif

        <span class="administrator-page-state">Страница {{ $administrators->currentPage() }} из {{ $administrators->lastPage() }}</span>

        @if ($administrators->hasMorePages())
            <a class="button button-secondary" href="{{ $administrators->nextPageUrl() }}" rel="next">Вперёд →</a>
        @else
            <span class="button button-secondary disabled">Вперёд →</span>
        @endif
    </div>
@endif
@endsection
