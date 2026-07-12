@extends('theme::layouts.app')
@section('title','Вход — '.site_name())
@section('content')<section class="auth-page"><div class="panel auth-card"><p class="eyebrow">ЛИЧНЫЙ КАБИНЕТ</p><h1>Вход</h1><p class="muted">Интерфейс уже свёрстан. Подключение безопасной авторизации — следующий модуль.</p><form><label>Игровой аккаунт<input disabled></label><label>Пароль<input disabled type="password"></label><button disabled class="button button-gold">Войти</button></form></div></section>@endsection
