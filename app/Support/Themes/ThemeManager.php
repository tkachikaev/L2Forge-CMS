<?php
namespace App\Support\Themes;

use RuntimeException;

final class ThemeManager
{
    private array $manifest = [];

    public function __construct(private readonly string $themesPath, private readonly string $activeTheme) {}

    public function boot(): void
    {
        $path = $this->themePath();
        $manifest = $path.'/theme.json';

        if (!is_dir($path) || !is_file($manifest)) {
            throw new RuntimeException("Theme [{$this->activeTheme}] is missing or invalid.");
        }

        $this->manifest = json_decode(file_get_contents($manifest), true, flags: JSON_THROW_ON_ERROR);
        view()->addNamespace('theme', $path.'/views');
    }

    public function manifest(): array { return $this->manifest; }
    public function name(): string { return $this->activeTheme; }
    public function themePath(): string { return rtrim($this->themesPath, '/').'/'.$this->activeTheme; }
    public function asset(string $path): string { return asset('themes/'.$this->activeTheme.'/assets/'.ltrim($path, '/')); }
}
