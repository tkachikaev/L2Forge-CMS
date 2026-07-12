@extends('theme::layouts.app')
@section('title','О сервере — '.site_name())
@section('content')<section class="page-hero"><div class="container"><p class="eyebrow">ИНФОРМАЦИЯ</p><h1>О сервере</h1></div></section><section class="container page-content"><div class="panel prose"><h2>{{ config('cms.server.chronicle') }} {{ config('cms.server.rates') }}</h2><p>Базовая информационная страница темы. Позже её содержимое будет редактироваться в CMS.</p></div></section>@endsection
