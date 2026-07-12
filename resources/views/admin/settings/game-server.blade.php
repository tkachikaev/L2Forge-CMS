@extends('admin.layouts.panel')

@section('title', 'Настройки')
@section('description', 'Игровые миры, отображаемые на публичном сайте.')

@section('content')
@include('admin.settings._tabs')

@php
    $createHasOldInput = old('form_context') === 'create';
    $createValues = [
        'name' => $createHasOldInput ? old('server_name', '') : '',
        'rates' => $createHasOldInput ? old('server_rates', '') : '',
        'chronicle' => $createHasOldInput ? old('server_chronicle', '') : '',
        'mode' => $createHasOldInput ? old('server_mode', '') : '',
    ];
@endphp

<div class="settings-server-toolbar">
    <div>
        <span>Игровых серверов</span>
        <strong>{{ count($servers) }}</strong>
    </div>

    <details class="settings-add-server" @if($createHasOldInput) open @endif>
        <summary class="button button-primary">+ Добавить игровой сервер</summary>
        <form method="POST" action="{{ route('admin.settings.game-server.store') }}">
            @csrf
            <input type="hidden" name="form_context" value="create">

            <div class="settings-card-heading">
                <div>
                    <h2>Новый игровой сервер</h2>
                    <p>После сохранения сервер появится в стандартной теме.</p>
                </div>
            </div>

            @include('admin.settings._game-server-fields', [
                'fieldPrefix' => 'new_server',
                'values' => $createValues,
            ])

            <div class="settings-inline-actions">
                <button class="button button-primary" type="submit">Добавить сервер</button>
            </div>
        </form>
    </details>
</div>

@if ($servers === [])
    <div class="empty-state">
        <div class="empty-state-mark" aria-hidden="true">S</div>
        <h2>Игровые серверы не добавлены</h2>
        <p>Добавьте первый игровой мир. Пока список пуст, серверный блок не показывается в стандартной теме.</p>
    </div>
@else
    <div class="settings-server-list">
        @foreach ($servers as $server)
            @php
                $context = 'server-'.$server['id'];
                $hasOldInput = old('form_context') === $context;
                $values = [
                    'name' => $hasOldInput ? old('server_name', '') : $server['name'],
                    'rates' => $hasOldInput ? old('server_rates', '') : $server['rates'],
                    'chronicle' => $hasOldInput ? old('server_chronicle', '') : $server['chronicle'],
                    'mode' => $hasOldInput ? old('server_mode', '') : $server['mode'],
                ];
                $fieldPrefix = 'server_'.$server['id'];
            @endphp

            <article class="form-card settings-server-card">
                <div class="settings-server-card-header">
                    <div>
                        <span class="settings-server-number">Сервер {{ $loop->iteration }}</span>
                        <h2>{{ $server['name'] }}</h2>
                    </div>
                    <button
                        class="button button-danger"
                        type="button"
                        data-game-server-delete-open
                        data-game-server-delete-name="{{ $server['name'] }}"
                        data-game-server-delete-url="{{ route('admin.settings.game-server.destroy', $server['id']) }}"
                    >Удалить</button>
                </div>

                <form class="settings-server-edit-form" method="POST" action="{{ route('admin.settings.game-server.update', $server['id']) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_context" value="{{ $context }}">

                    @include('admin.settings._game-server-fields', [
                        'fieldPrefix' => $fieldPrefix,
                        'values' => $values,
                    ])

                    <div class="settings-inline-actions">
                        <button class="button button-primary" type="submit">Сохранить сервер</button>
                    </div>
                </form>

                @include('admin.settings._game-server-connection', [
                    'fieldPrefix' => $fieldPrefix,
                ])
            </article>
        @endforeach
    </div>

    <dialog class="confirm-dialog" data-game-server-delete-dialog aria-labelledby="delete-game-server-title">
        <div class="confirm-dialog-card">
            <div class="confirm-dialog-copy">
                <span class="confirm-dialog-mark" aria-hidden="true">!</span>
                <div>
                    <h2 id="delete-game-server-title">Удалить игровой сервер?</h2>
                    <p>Сервер «<strong data-game-server-delete-name></strong>» исчезнет из настроек и стандартной темы. Отменить действие нельзя.</p>
                </div>
            </div>

            <div class="confirm-dialog-actions">
                <button class="button button-secondary" type="button" data-game-server-delete-cancel>Отмена</button>

                <form method="POST" action="" data-game-server-delete-form>
                    @csrf
                    @method('DELETE')
                    <button class="button button-danger" type="submit">Да, удалить</button>
                </form>
            </div>
        </div>
    </dialog>
@endif
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/js/game-server-actions.js') }}" defer></script>
@endpush
