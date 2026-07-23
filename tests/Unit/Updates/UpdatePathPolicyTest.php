<?php

namespace Tests\Unit\Updates;

use App\Services\Updates\UpdatePathPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UpdatePathPolicyTest extends TestCase
{
    #[DataProvider('safeTargets')]
    public function test_release_targets_are_accepted(string $target): void
    {
        $this->assertTrue((new UpdatePathPolicy)->isSafeTarget($target));
    }

    #[DataProvider('unsafeTargets')]
    public function test_runtime_and_traversal_targets_are_rejected(string $target): void
    {
        $this->assertFalse((new UpdatePathPolicy)->isSafeTarget($target));
    }

    /** @return array<string, array{string}> */
    public static function safeTargets(): array
    {
        return [
            'application file' => ['core/app/Services/Example.php'],
            'migration' => ['core/database/migrations/2026_07_23_000000_example.php'],
            'public asset' => ['public/assets/admin/css/app.css'],
        ];
    }

    /** @return array<string, array{string}> */
    public static function unsafeTargets(): array
    {
        return [
            'environment' => ['core/.env'],
            'environment backup' => ['core/.env.production'],
            'storage' => ['core/storage/logs/laravel.log'],
            'vendor' => ['core/vendor/autoload.php'],
            'sqlite runtime' => ['core/database/database.sqlite'],
            'custom sqlite runtime' => ['core/database/production.sqlite'],
            'nested public tree' => ['core/public/index.php'],
            'git metadata' => ['core/.git/config'],
            'split path configuration' => ['core/bootstrap/kaevcms-public-path.php'],
            'bootstrap cache' => ['core/bootstrap/cache/config.php'],
            'uploads' => ['public/uploads/news/cover.webp'],
            'public storage' => ['public/storage/private.txt'],
            'traversal' => ['core/../.env'],
            'absolute' => ['/core/app.php'],
            'windows absolute' => ['C:/core/app.php'],
            'unknown root' => ['resources/view.blade.php'],
        ];
    }
}
