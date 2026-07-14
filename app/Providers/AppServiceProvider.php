<?php

namespace App\Providers;

use App\Services\AuditLogger;
use App\Services\GameServerSettings;
use App\Services\Localization\LanguageManager;
use App\Services\Localization\LocalizedContentResolver;
use App\Services\MailSettings;
use App\Services\MailTemplateSettings;
use App\Services\News\NewsHtmlSanitizer;
use App\Services\News\NewsImageStorage;
use App\Services\Pages\PageHtmlSanitizer;
use App\Services\Pages\PageImageStorage;
use App\Services\Pages\PageNavigation;
use App\Services\RegistrationSettings;
use App\Services\Settings\SettingsImageStorage;
use App\Services\SiteSettings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AuditLogger::class);
        $this->app->singleton(GameServerSettings::class);
        $this->app->singleton(MailSettings::class);
        $this->app->singleton(MailTemplateSettings::class);
        $this->app->singleton(LanguageManager::class);
        $this->app->singleton(LocalizedContentResolver::class);
        $this->app->singleton(NewsHtmlSanitizer::class);
        $this->app->singleton(NewsImageStorage::class);
        $this->app->singleton(PageHtmlSanitizer::class);
        $this->app->singleton(PageImageStorage::class);
        $this->app->singleton(PageNavigation::class);
        $this->app->singleton(RegistrationSettings::class);
        $this->app->singleton(SettingsImageStorage::class);
        $this->app->singleton(SiteSettings::class);
    }

    public function boot(SiteSettings $siteSettings, MailSettings $mailSettings, LanguageManager $languages): void
    {
        $this->configureRateLimiters();

        $defaultLocale = $languages->default();
        $fallbackLocale = $languages->fallback();
        config()->set('app.locale', $defaultLocale);
        config()->set('app.fallback_locale', $fallbackLocale);
        app()->setLocale($defaultLocale);
        app('translator')->setFallback($fallbackLocale);
        $siteSettings->applyConfiguredTimezone();
        $mailSettings->applyConfiguration();

        if (config('app.force_https')) {
            URL::forceScheme('https');
        }
    }

    private function configureRateLimiters(): void
    {
        RateLimiter::for('admin-login-ip', static function (Request $request): array {
            $ip = $request->ip() ?? 'unknown';
            $key = 'admin-login-ip:'.hash('sha256', $ip);

            return [
                Limit::perMinute(max(1, (int) config('cms.admin.login_ip_max_attempts_per_minute', 10)))
                    ->by($key.':minute'),
                Limit::perHour(max(1, (int) config('cms.admin.login_ip_max_attempts_per_hour', 100)))
                    ->by($key.':hour'),
            ];
        });
    }
}
