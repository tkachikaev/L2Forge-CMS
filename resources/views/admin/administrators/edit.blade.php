@extends('admin.layouts.panel')

@section('title', 'Администратор')
@section('description', 'Изменение данных, пароля и состояния учётной записи.')

@section('content')
<div class="administrator-page-toolbar">
    <a class="button button-secondary" href="{{ route('admin.administrators.index') }}">← Вернуться к списку</a>
    <span @class([
        'status-badge',
        'status-badge-success' => $administrator->is_active,
        'status-badge-muted' => ! $administrator->is_active,
    ])>{{ $administrator->is_active ? 'Активен' : 'Отключён' }}</span>
</div>

<div class="administrator-edit-grid">
    <form class="administrator-form" method="POST" action="{{ route('admin.administrators.update', $administrator) }}">
        @csrf
        @method('PUT')

        <section class="form-card administrator-form-card">
            <h2>Основные данные</h2>

            <div class="form-group">
                <label for="name">Имя</label>
                <input id="name" name="name" type="text" maxlength="100" required autocomplete="name" value="{{ old('name', $administrator->name) }}">
                <small>Отображается в панели управления и журнале действий.</small>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" maxlength="255" required autocomplete="username" value="{{ old('email', $administrator->email) }}">
                <small>Email используется для входа в административную панель.</small>
            </div>

            <div class="administrator-metadata">
                <div>
                    <span>Создан</span>
                    <strong>{{ $administrator->created_at?->format('d.m.Y H:i') ?? '—' }}</strong>
                </div>
                <div>
                    <span>Последний вход</span>
                    <strong>{{ $administrator->last_login_at?->format('d.m.Y H:i') ?? 'Никогда' }}</strong>
                </div>
            </div>
        </section>

        <div class="settings-actions administrator-form-actions">
            <button class="button button-primary" type="submit">Сохранить данные</button>
        </div>
    </form>

    <div class="administrator-side-column">
        <form class="administrator-form" method="POST" action="{{ route('admin.administrators.password', $administrator) }}">
            @csrf
            @method('PUT')

            <section class="form-card administrator-form-card">
                <h2>Смена пароля</h2>

                @if ($isCurrentAdmin)
                    <div class="form-group">
                        <label for="current_password">Текущий пароль</label>
                        <input id="current_password" name="current_password" type="password" maxlength="4096" required autocomplete="current-password">
                        <small>Для собственной учётной записи требуется подтверждение текущего пароля.</small>
                    </div>
                @endif

                <div class="form-group">
                    <label for="password">Новый пароль</label>
                    <input id="password" name="password" type="password" maxlength="4096" required autocomplete="new-password">
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Повторите новый пароль</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" maxlength="4096" required autocomplete="new-password">
                </div>

                <div class="administrator-password-rules">
                    <strong>Требования</strong>
                    <span>Не менее 12 символов, строчная и заглавная буквы, минимум одна цифра.</span>
                </div>

                <button class="button button-primary administrator-password-button" type="submit">Изменить пароль</button>
            </section>
        </form>

        <section class="form-card administrator-form-card administrator-status-card">
            <h2>Состояние учётной записи</h2>

            @if ($administrator->is_active)
                @if ($isCurrentAdmin)
                    <p>Собственную учётную запись отключить нельзя.</p>
                    <button class="button button-danger" type="button" disabled>Отключить</button>
                @elseif ($activeCount <= 1)
                    <p>Нельзя отключить последнего активного администратора.</p>
                    <button class="button button-danger" type="button" disabled>Отключить</button>
                @else
                    <p>После отключения администратор больше не сможет открыть панель управления.</p>
                    <form method="POST" action="{{ route('admin.administrators.status', $administrator) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="is_active" value="0">
                        <button class="button button-danger" type="submit">Отключить администратора</button>
                    </form>
                @endif
            @else
                <p>После включения администратор снова сможет войти с текущим email и паролем.</p>
                <form method="POST" action="{{ route('admin.administrators.status', $administrator) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="is_active" value="1">
                    <button class="button button-primary" type="submit">Включить администратора</button>
                </form>
            @endif
        </section>
    </div>
</div>
@endsection
