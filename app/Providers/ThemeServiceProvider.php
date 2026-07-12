<?php
namespace App\Providers;

use App\Support\Themes\ThemeManager;
use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ThemeManager::class, fn () => new ThemeManager(
            themesPath: config('cms.themes_path'),
            activeTheme: config('cms.theme'),
        ));
    }

    public function boot(ThemeManager $themes): void
    {
        $themes->boot();
        view()->share('activeTheme', $themes->manifest());
    }
}
