@extends('admin.layouts.panel')

@section('title', 'Настройки')
@section('description', 'Параметры сайта и подключений L2Forge CMS.')

@section('content')
@include('admin.settings._tabs')

<section class="settings-placeholder">
    <span class="settings-placeholder-mark" aria-hidden="true">⌁</span>
    <h2>{{ $title }}</h2>
    <p>{{ $description }}</p>
    <span class="settings-placeholder-status">Раздел находится в разработке</span>
</section>
@endsection
