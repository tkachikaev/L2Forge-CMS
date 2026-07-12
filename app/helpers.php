<?php
use App\Support\Themes\ThemeManager;

if (!function_exists('theme_asset')) {
    function theme_asset(string $path): string
    {
        return app(ThemeManager::class)->asset($path);
    }
}
