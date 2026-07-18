<?php

namespace Tests\Feature\Account;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_account_uses_a_persisted_livewire_shell(): void
    {
        $user = $this->user();

        $response = $this->actingAs($user)->get('/account');

        $response
            ->assertOk()
            ->assertSee('data-account-sidebar', false)
            ->assertSee('data-account-topbar', false)
            ->assertSee('wire:navigate', false)
            ->assertSee('data-navigate-track', false)
            ->assertSee('data-navigate-once="true"', false)
            ->assertSee('assets/account/js/navigation.js', false)
            ->assertSee('livewire.js?id=', false);

        $layout = file_get_contents(resource_path('views/account/layouts/app.blade.php'));
        $navigation = file_get_contents(resource_path('views/account/partials/navigation.blade.php'));
        $script = file_get_contents(public_path('assets/account/js/navigation.js'));
        $styles = file_get_contents(public_path('assets/account/css/app.css'));

        $this->assertIsString($layout);
        $this->assertIsString($navigation);
        $this->assertIsString($script);
        $this->assertIsString($styles);
        $this->assertStringContainsString("@persist('account-sidebar')", $layout);
        $this->assertStringContainsString("@persist('account-topbar')", $layout);
        $this->assertStringContainsString('wire:navigate:scroll', $layout);
        $this->assertStringContainsString('wire:navigate.hover', $navigation);
        $this->assertStringContainsString('wire:current.exact="active"', $navigation);
        $this->assertStringContainsString('livewire:navigate', $script);
        $this->assertStringContainsString('livewire:navigated', $script);
        $this->assertStringContainsString('account-is-navigating', $script);
        $this->assertStringContainsString('html.account-is-navigating .account-content', $styles);
        $this->assertStringContainsString('@media(prefers-reduced-motion:reduce)', $styles);
    }

    public function test_game_account_index_is_available_on_default_and_localized_routes(): void
    {
        $this->get('/account/game-accounts')->assertRedirect(route('login'));

        $user = $this->user();

        $this->actingAs($user)
            ->get('/account/game-accounts')
            ->assertOk()
            ->assertSee('Мои аккаунты')
            ->assertSee('wire:current="active"', false);

        $this->actingAs($user)
            ->get('/ru/account/game-accounts')
            ->assertOk()
            ->assertSee('Мои аккаунты');
    }

    public function test_account_get_views_use_livewire_navigation_links(): void
    {
        $views = [
            resource_path('views/account/dashboard.blade.php'),
            resource_path('views/account/game-accounts/index.blade.php'),
            resource_path('views/account/game-accounts/create.blade.php'),
            resource_path('views/account/game-accounts/show.blade.php'),
            resource_path('views/livewire/account/character-directory.blade.php'),
        ];

        foreach ($views as $view) {
            $contents = file_get_contents($view);

            $this->assertIsString($contents);
            $this->assertStringContainsString('wire:navigate', $contents, $view);
        }
    }

    private function user(): User
    {
        return User::query()->create([
            'name' => 'Player Navigation',
            'email' => 'player-navigation@example.test',
            'email_verified_at' => now(),
            'password' => Hash::make('CorrectPassword123!'),
            'is_active' => true,
            'locale' => 'ru',
        ]);
    }
}
