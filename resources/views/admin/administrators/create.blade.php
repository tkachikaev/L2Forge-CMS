@extends('admin.layouts.panel')

@section('title', 'Создать администратора')
@section('description', 'Новая учётная запись получит полный доступ к панели управления.')

@section('content')
<div class="administrator-page-toolbar">
    <a class="button button-secondary" href="{{ route('admin.administrators.index') }}">← Вернуться к списку</a>
</div>

<form class="administrator-form" method="POST" action="{{ route('admin.administrators.store') }}">
    @csrf

    <section class="form-card administrator-form-card">
        <h2>Данные администратора</h2>

        <div class="form-group">
            <label for="name">Имя</label>
            <input id="name" name="name" type="text" maxlength="100" required autocomplete="name" value="{{ old('name') }}">
            <small>Отображается в панели управления и журнале действий.</small>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" maxlength="255" required autocomplete="username" value="{{ old('email') }}">
            <small>Email используется для входа в административную панель.</small>
        </div>

        <div class="form-row form-row-equal">
            <div class="form-group">
                <label for="password">Пароль</label>
                <input id="password" name="password" type="password" maxlength="4096" required autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="password_confirmation">Повторите пароль</label>
                <input id="password_confirmation" name="password_confirmation" type="password" maxlength="4096" required autocomplete="new-password">
            </div>
        </div>

        <div class="administrator-password-rules">
            <strong>Требования к паролю</strong>
            <span>Не менее 12 символов, строчная и заглавная буквы, минимум одна цифра.</span>
        </div>
    </section>

    <div class="settings-actions administrator-form-actions">
        <button class="button button-primary" type="submit">Создать администратора</button>
        <a class="button button-secondary" href="{{ route('admin.administrators.index') }}">Отмена</a>
    </div>
</form>
@endsection
