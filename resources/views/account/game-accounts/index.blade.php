@extends('account.layouts.app')
@section('title', __('Game accounts'))
@section('content')
<section class="account-welcome account-welcome-compact">
    <div>
        <span class="account-eyebrow">{{ __('Game accounts') }}</span>
        <h1>{{ __('My accounts') }}</h1>
        <p>{{ __('Manage game accounts and see characters without leaving the player account.') }}</p>
    </div>
    @if ($settings['enabled'] && $quotaAccountCount < $settings['max_accounts'] && $availableServers > 0)
        <a wire:navigate.hover class="account-button primary" href="{{ public_route('game-accounts.create') }}">{{ __('Create game account') }}</a>
    @endif
</section>

<section class="account-stats account-stats-compact">
    <article><span>{{ __('Game accounts') }}</span><strong>{{ $quotaAccountCount }} / {{ $settings['max_accounts'] }}</strong></article>
    <article><span>{{ __('Available worlds') }}</span><strong>{{ $availableServers }}</strong></article>
</section>

@if ($hiddenAccountCount > 0)
    <div class="account-inline-warning">
        {{ __('Some game accounts are temporarily unavailable because their LoginServer has no configured GameServer. They remain safe and continue to count toward the account limit.') }}
    </div>
@endif

@if ($accounts->isEmpty())
    <div class="account-empty">
        <span aria-hidden="true">◇</span>
        <h3>{{ __('No game accounts yet') }}</h3>
        <p>{{ __('Create the first account, then its characters will appear here.') }}</p>
        @if ($settings['enabled'] && $quotaAccountCount < $settings['max_accounts'] && $availableServers > 0)
            <a wire:navigate.hover class="account-button primary" href="{{ public_route('game-accounts.create') }}">{{ __('Create game account') }}</a>
        @elseif ($availableServers === 0)
            <small>{{ __('No configured game servers are available for registration.') }}</small>
        @endif
    </div>
@else
    <div class="game-account-grid game-account-grid-wide">
        @foreach ($accounts as $account)
            @php($gameServers = $account->loginServer->gameServers)
            <article class="game-account-card">
                <div class="game-account-card-head">
                    <span class="game-account-icon">{{ mb_strtoupper(mb_substr($account->game_login, 0, 1)) }}</span>
                    <div><h3>{{ $account->game_login }}</h3><p>{{ __('Game account') }}</p></div>
                </div>
                <dl>
                    <div>
                        <dt>{{ $gameServers->count() > 1 ? __('Servers') : __('Server') }}</dt>
                        <dd>
                            @forelse ($gameServers as $gameServer)
                                <span>{{ $gameServer->nameFor() }}</span>@if (! $loop->last)<br>@endif
                            @empty
                                —
                            @endforelse
                        </dd>
                    </div>
                    <div><dt>{{ __('Created') }}</dt><dd>{{ $account->created_at?->format('d.m.Y') }}</dd></div>
                </dl>
                <a wire:navigate.hover class="account-card-link" href="{{ public_route('game-accounts.show', ['gameAccount' => $account]) }}">{{ __('View details') }} →</a>
            </article>
        @endforeach
    </div>
@endif
@endsection
