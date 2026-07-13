@extends('admin.layouts.panel')

@section('title', 'Пользователь '.$user->name)
@section('description', 'Карточка учётной записи сайта и последние связанные события журнала.')

@section('content')
<div class="user-detail-toolbar">
    <a class="button button-secondary" href="{{ route('admin.users.index') }}">← К списку пользователей</a>
</div>

<div class="user-detail-grid">
    <div class="user-detail-main">
        <section class="form-card user-profile-card">
            <div class="user-profile-heading">
                <div>
                    <span class="user-profile-eyebrow">Пользователь CMS</span>
                    <h2>{{ $user->name }}</h2>
                    <p>{{ $user->email }}</p>
                </div>
                <div class="user-profile-badges">
                    <span @class([
                        'status-badge',
                        'status-badge-success' => $user->is_active,
                        'status-badge-muted' => ! $user->is_active,
                    ])>{{ $user->is_active ? 'Активен' : 'Отключён' }}</span>
                    <span @class([
                        'status-badge',
                        'status-badge-success' => $user->hasVerifiedEmail(),
                        'status-badge-warning' => ! $user->hasVerifiedEmail(),
                    ])>{{ $user->hasVerifiedEmail() ? 'Email подтверждён' : 'Email не подтверждён' }}</span>
                </div>
            </div>

            <dl class="user-definition-list">
                <div>
                    <dt>ID</dt>
                    <dd>{{ $user->id }}</dd>
                </div>
                <div>
                    <dt>Логин</dt>
                    <dd>{{ $user->name }}</dd>
                </div>
                <div>
                    <dt>Email</dt>
                    <dd>{{ $user->email }}</dd>
                </div>
                <div>
                    <dt>Дата регистрации</dt>
                    <dd>{{ $user->created_at?->format('d.m.Y H:i:s') ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Подтверждение email</dt>
                    <dd>{{ $user->email_verified_at?->format('d.m.Y H:i:s') ?? 'Не подтверждён' }}</dd>
                </div>
                <div>
                    <dt>Последний успешный вход</dt>
                    <dd>{{ $user->last_login_at?->format('d.m.Y H:i:s') ?? 'Никогда' }}</dd>
                </div>
            </dl>
        </section>

        <section class="form-card user-activity-card">
            <div class="user-card-heading">
                <div>
                    <h2>Последняя активность</h2>
                    <p>До 25 последних записей, где пользователь указан инициатором или объектом действия.</p>
                </div>
                <a class="button button-secondary" href="{{ route('admin.logs.index', ['category' => 'user']) }}">Открыть журнал</a>
            </div>

            @if ($activity->isEmpty())
                <div class="user-activity-empty">Связанных записей в журнале пока нет.</div>
            @else
                <div class="user-activity-table-wrap">
                    <table class="user-activity-table">
                        <thead>
                            <tr>
                                <th>Дата</th>
                                <th>Действие</th>
                                <th>Результат</th>
                                <th>IP-адрес</th>
                                <th>Браузер</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($activity as $event)
                                <tr>
                                    <td>
                                        <strong>{{ $event->created_at?->format('d.m.Y') }}</strong>
                                        <span>{{ $event->created_at?->format('H:i:s') }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.logs.show', $event) }}">{{ $event->actionLabel() }}</a>
                                        <code>{{ $event->action }}</code>
                                    </td>
                                    <td>
                                        <span @class([
                                            'status-badge',
                                            'status-badge-success' => $event->result === 'success',
                                            'status-badge-danger' => $event->result === 'failed',
                                        ])>{{ $event->resultLabel() }}</span>
                                    </td>
                                    <td class="user-activity-monospace">{{ $event->ip_address ?: '—' }}</td>
                                    <td class="user-agent-cell" title="{{ $event->user_agent ?: '' }}">{{ $event->user_agent ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>

    <aside class="user-detail-side">
        <section class="form-card user-action-card">
            <h2>Доступ к сайту</h2>
            @if ($user->is_active)
                <p>Отключение завершит сохранённые сессии пользователя и запретит новый вход. Данные и история сохранятся.</p>
                <form
                    method="POST"
                    action="{{ route('admin.users.status', $user) }}"
                    data-user-status-form
                    data-user-status-confirm="Отключить пользователя {{ $user->name }}?"
                >
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="is_active" value="0">
                    <button class="button button-danger" type="submit">Отключить пользователя</button>
                </form>
            @else
                <p>Пользователь не может войти в личный кабинет. Включение восстановит доступ без изменения пароля.</p>
                <form method="POST" action="{{ route('admin.users.status', $user) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="is_active" value="1">
                    <button class="button button-primary" type="submit">Включить пользователя</button>
                </form>
            @endif
        </section>

        <section class="form-card user-action-card">
            <h2>Почтовые действия</h2>
            <p>Пароль и служебные токены администратору не показываются. Для смены пароля отправляется стандартная одноразовая ссылка.</p>

            @if (! $mailReady)
                <div class="notice notice-warning user-mail-warning">
                    <p>SMTP не настроен или не прошёл тестовую отправку. Почтовые действия временно недоступны.</p>
                </div>
            @endif

            <div class="user-mail-actions">
                @if (! $user->hasVerifiedEmail())
                    <form method="POST" action="{{ route('admin.users.verification', $user) }}">
                        @csrf
                        <button class="button button-secondary" type="submit" @disabled(! $mailReady)>Отправить подтверждение email</button>
                    </form>
                @else
                    <span class="user-action-note">Email уже подтверждён.</span>
                @endif

                <form method="POST" action="{{ route('admin.users.password-reset', $user) }}">
                    @csrf
                    <button class="button button-secondary" type="submit" @disabled(! $mailReady)>Отправить восстановление пароля</button>
                </form>
            </div>
        </section>

        <section class="form-card form-card-muted user-game-placeholder">
            <h2>Игровые данные</h2>
            <p>Связь с Login Server, игровые аккаунты и персонажи в этой версии не подключены. Они появятся отдельным этапом и не будут смешиваться с данными CMS.</p>
        </section>
    </aside>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/js/users.js') }}?v={{ cms_version() }}" defer></script>
@endpush
