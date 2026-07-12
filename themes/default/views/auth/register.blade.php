@extends('theme::layouts.app')
@section('title','Регистрация — '.config('app.name'))
@section('content')<section class="auth-page"><div class="panel auth-card"><p class="eyebrow">НОВЫЙ АККАУНТ</p><h1>Регистрация</h1><p class="muted">Форма намеренно отключена до привязки точной схемы и алгоритма паролей вашей сборки Mobius.</p><form><label>Логин<input disabled></label><label>Почта<input disabled type="email"></label><label>Пароль<input disabled type="password"></label><button disabled class="button button-gold">Создать аккаунт</button></form></div></section>@endsection
