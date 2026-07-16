@extends('admin.layouts.panel')

@section('title', __('Dashboard'))
@section('description', __('Compact overview of players and server availability.'))

@section('content')
@php
    $stateLabels = [
        'online' => __('Running'),
        'offline' => __('Unavailable'),
        'unknown' => __('Status pending'),
    ];
@endphp

<div
    class="admin-dashboard-stack"
    data-server-monitor-dashboard
    data-refresh-url="{{ public_route('server-monitor.refresh') }}"
    data-auto-refresh="{{ $monitorRefreshDue ? '1' : '0' }}"
>
    <section class="dashboard-monitor-summary">
        <div>
            <span>{{ __('Total online') }}</span>
            <strong data-monitor-total-online>{{ number_format($monitor['total_online'], 0, '.', ' ') }}</strong>
            <small data-monitor-partial @if(! $monitor['partial']) hidden @endif>{{ __('Partial data') }}</small>
        </div>
        <div class="dashboard-monitor-summary-meta">
            <span data-monitor-updated>
                {{ $monitor['checked_at']
                    ? __('Updated :time', ['time' => $monitor['checked_at']->diffForHumans()])
                    : __('Not checked yet') }}
            </span>
            <form method="POST" action="{{ route('admin.server-monitor.refresh') }}">
                @csrf
                <button class="button button-secondary button-compact" type="submit">{{ __('Check now') }}</button>
            </form>
        </div>
    </section>

    <div class="dashboard-monitor-grid">
        <section class="dashboard-monitor-card">
            <header>
                <h2>{{ __('Game servers') }}</h2>
                <a href="{{ route('admin.settings.game-server') }}">{{ __('Settings') }}</a>
            </header>

            <div class="dashboard-monitor-list">
                @forelse($monitor['game_servers'] as $server)
                    <a class="dashboard-monitor-row" data-monitor-admin-game="{{ $server['id'] }}" href="{{ route('admin.settings.game-server') }}">
                        <span class="dashboard-monitor-dot {{ $server['state'] }}" data-monitor-dot aria-hidden="true"></span>
                        <span class="dashboard-monitor-name">{{ $server['name'] }}</span>
                        <span class="dashboard-monitor-state" data-monitor-state>{{ $stateLabels[$server['state']] }}</span>
                        <strong class="dashboard-monitor-online" data-monitor-online>
                            {{ $server['players'] !== null
                                ? __(':count online', ['count' => number_format($server['players'], 0, '.', ' ')])
                                : '—' }}
                        </strong>
                    </a>
                @empty
                    <p class="dashboard-monitor-empty">{{ __('No game servers configured.') }}</p>
                @endforelse
            </div>
        </section>

        <section class="dashboard-monitor-card">
            <header>
                <h2>{{ __('Login servers') }}</h2>
                <a href="{{ route('admin.settings.login-server') }}">{{ __('Settings') }}</a>
            </header>

            <div class="dashboard-monitor-list">
                @forelse($monitor['login_servers'] as $server)
                    <a class="dashboard-monitor-row dashboard-monitor-row-login" data-monitor-admin-login="{{ $server['id'] }}" href="{{ route('admin.settings.login-server') }}">
                        <span class="dashboard-monitor-dot {{ $server['state'] }}" data-monitor-dot aria-hidden="true"></span>
                        <span class="dashboard-monitor-name">{{ $server['name'] }}</span>
                        <span class="dashboard-monitor-state" data-monitor-state>{{ $stateLabels[$server['state']] }}</span>
                    </a>
                @empty
                    <p class="dashboard-monitor-empty">{{ __('No login servers configured.') }}</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/js/server-monitor.js') }}?v={{ cms_version() }}" defer></script>
@endpush
