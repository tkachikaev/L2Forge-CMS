<?php

use App\Services\SiteSettings;
use App\Support\Themes\ThemeManager;

if (! function_exists('theme_asset')) {
    function theme_asset(string $path): string
    {
        return app(ThemeManager::class)->asset($path);
    }
}

if (! function_exists('site_name')) {
    function site_name(): string
    {
        return app(SiteSettings::class)->name();
    }
}

if (! function_exists('site_description')) {
    function site_description(): string
    {
        return app(SiteSettings::class)->description();
    }
}

if (! function_exists('site_logo_url')) {
    function site_logo_url(): ?string
    {
        return app(SiteSettings::class)->logoUrl();
    }
}

if (! function_exists('site_favicon_url')) {
    function site_favicon_url(): ?string
    {
        return app(SiteSettings::class)->faviconUrl();
    }
}

if (! function_exists('site_footer_text')) {
    function site_footer_text(): string
    {
        return app(SiteSettings::class)->footerText();
    }
}
