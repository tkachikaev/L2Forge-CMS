<?php

namespace App\Services;

use App\Services\Settings\SettingsImageStorage;

final class SiteSettings
{
    public const KEY_NAME = 'site.name';
    public const KEY_DESCRIPTION = 'site.description';
    public const KEY_LOGO = 'site.logo';
    public const KEY_FAVICON = 'site.favicon';
    public const KEY_TIMEZONE = 'site.timezone';
    public const KEY_ADMIN_EMAIL = 'site.admin_email';
    public const KEY_FOOTER_TEXT = 'site.footer_text';

    public function __construct(
        private readonly CmsSettings $settings,
        private readonly SettingsImageStorage $images,
    ) {
    }

    /** @return array{name: string, description: string, logo: string|null, logo_url: string|null, favicon: string|null, favicon_url: string|null, timezone: string, admin_email: string, footer_text: string} */
    public function values(): array
    {
        $defaults = $this->defaults();
        $values = $this->settings->getMany([
            self::KEY_NAME => $defaults['name'],
            self::KEY_DESCRIPTION => $defaults['description'],
            self::KEY_LOGO => null,
            self::KEY_FAVICON => null,
            self::KEY_TIMEZONE => $defaults['timezone'],
            self::KEY_ADMIN_EMAIL => $defaults['admin_email'],
            self::KEY_FOOTER_TEXT => $defaults['footer_text'],
        ]);

        $logo = $this->images->normalizePath($values[self::KEY_LOGO] ?? null, 'logo');
        $favicon = $this->images->normalizePath($values[self::KEY_FAVICON] ?? null, 'favicon');
        $timezone = $this->normalizeTimezone(
            (string) ($values[self::KEY_TIMEZONE] ?? $defaults['timezone']),
            $defaults['timezone'],
        );

        return [
            'name' => $this->nonEmptyString($values[self::KEY_NAME] ?? null, $defaults['name']),
            'description' => (string) ($values[self::KEY_DESCRIPTION] ?? $defaults['description']),
            'logo' => $logo,
            'logo_url' => $this->images->publicUrl($logo),
            'favicon' => $favicon,
            'favicon_url' => $this->images->publicUrl($favicon),
            'timezone' => $timezone,
            'admin_email' => (string) ($values[self::KEY_ADMIN_EMAIL] ?? $defaults['admin_email']),
            'footer_text' => (string) ($values[self::KEY_FOOTER_TEXT] ?? $defaults['footer_text']),
        ];
    }

    /** @param array{name: string, description: string, logo: string|null, favicon: string|null, timezone: string, admin_email: string, footer_text: string} $values */
    public function update(array $values): void
    {
        $this->settings->setMany([
            self::KEY_NAME => $values['name'],
            self::KEY_DESCRIPTION => $values['description'],
            self::KEY_LOGO => $values['logo'],
            self::KEY_FAVICON => $values['favicon'],
            self::KEY_TIMEZONE => $values['timezone'],
            self::KEY_ADMIN_EMAIL => $values['admin_email'],
            self::KEY_FOOTER_TEXT => $values['footer_text'],
        ]);

        $this->applyTimezone($values['timezone']);
    }

    public function name(): string
    {
        return $this->values()['name'];
    }

    public function description(): string
    {
        return $this->values()['description'];
    }

    public function logoUrl(): ?string
    {
        return $this->values()['logo_url'];
    }

    public function faviconUrl(): ?string
    {
        return $this->values()['favicon_url'];
    }

    public function footerText(): string
    {
        return $this->values()['footer_text'];
    }

    public function applyConfiguredTimezone(): void
    {
        $defaults = $this->defaults();
        $timezone = $this->settings->get(self::KEY_TIMEZONE, $defaults['timezone']) ?? $defaults['timezone'];

        $this->applyTimezone($this->normalizeTimezone($timezone, $defaults['timezone']));
    }

    private function applyTimezone(string $timezone): void
    {
        if (! in_array($timezone, timezone_identifiers_list(), true)) {
            return;
        }

        config()->set('app.timezone', $timezone);
        date_default_timezone_set($timezone);
    }

    /** @return array{name: string, description: string, timezone: string, admin_email: string, footer_text: string} */
    private function defaults(): array
    {
        $applicationName = trim((string) config('app.name', 'L2Forge CMS'));
        $siteName = trim((string) config('cms.site_defaults.name', $applicationName));
        $timezone = (string) config('cms.site_defaults.timezone', config('app.timezone', 'UTC'));

        return [
            'name' => $siteName !== '' ? $siteName : ($applicationName !== '' ? $applicationName : 'L2Forge CMS'),
            'description' => (string) config('cms.site_defaults.description', ''),
            'timezone' => $this->normalizeTimezone($timezone, 'UTC'),
            'admin_email' => (string) config('cms.site_defaults.admin_email', ''),
            'footer_text' => (string) config('cms.site_defaults.footer_text', '© 2026 L2Forge-CMS'),
        ];
    }

    private function normalizeTimezone(string $timezone, string $fallback): string
    {
        if (in_array($timezone, timezone_identifiers_list(), true)) {
            return $timezone;
        }

        return in_array($fallback, timezone_identifiers_list(), true) ? $fallback : 'UTC';
    }

    private function nonEmptyString(?string $value, string $fallback): string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : $fallback;
    }
}
