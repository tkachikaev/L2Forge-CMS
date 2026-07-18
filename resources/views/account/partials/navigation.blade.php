<nav class="account-nav" aria-label="{{ __('Player account navigation') }}">
    <a wire:navigate.hover wire:current.exact="active" href="{{ public_route('account') }}">
        <span class="account-nav-icon" aria-hidden="true">⌂</span>
        <span>{{ __('Overview') }}</span>
    </a>
    <a wire:navigate.hover wire:current="active" href="{{ public_route('game-accounts.index') }}">
        <span class="account-nav-icon" aria-hidden="true">▣</span>
        <span>{{ __('Game accounts') }}</span>
    </a>
</nav>
