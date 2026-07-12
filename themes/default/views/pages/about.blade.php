@extends('theme::layouts.app')
@section('title', 'О сервере — '.site_name())

@section('content')
@php
    $gameServers = game_servers();
@endphp
<section class="page-hero">
    <div class="container">
        <p class="eyebrow">ИНФОРМАЦИЯ</p>
        <h1>О сервере</h1>
    </div>
</section>
<section class="container page-content">
    <div class="panel prose">
        @if ($gameServers === [])
            <h2>{{ site_name() }}</h2>
            <p>Информация об игровых серверах пока не добавлена.</p>
        @else
            <h2>{{ count($gameServers) > 1 ? 'Игровые серверы' : $gameServers[0]['name'] }}</h2>
            <div class="about-server-list">
                @foreach ($gameServers as $gameServer)
                    <article class="about-server-card">
                        <h3>{{ $gameServer['name'] }}</h3>
                        @if ($gameServer['show_chronicle'] || $gameServer['show_rates'] || $gameServer['show_mode'])
                            <dl>
                                @if ($gameServer['show_chronicle'])
                                    <div><dt>Хроники</dt><dd>{{ $gameServer['chronicle'] }}</dd></div>
                                @endif
                                @if ($gameServer['show_rates'])
                                    <div><dt>Рейты</dt><dd>{{ $gameServer['rates'] }}</dd></div>
                                @endif
                                @if ($gameServer['show_mode'])
                                    <div><dt>Режим</dt><dd>{{ $gameServer['mode'] }}</dd></div>
                                @endif
                            </dl>
                        @endif
                    </article>
                @endforeach
            </div>
            <p>Базовая информационная страница темы. Позже её содержимое будет редактироваться в CMS.</p>
        @endif
    </div>
</section>
@endsection
