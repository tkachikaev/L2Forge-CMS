<?php

namespace Tests\Feature;

use App\Models\Admin;
use Database\Seeders\BrowserTestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BrowserTestSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_browser_test_administrator_is_created_from_configuration(): void
    {
        config()->set('browser_tests.admin.email', 'configured-browser-admin@example.test');
        config()->set('browser_tests.admin.password', 'ConfiguredBrowserPassword123!');

        $this->seed(BrowserTestSeeder::class);

        $admin = Admin::query()
            ->where('email', 'configured-browser-admin@example.test')
            ->firstOrFail();

        $this->assertSame('Browser Test Admin', $admin->name);
        $this->assertTrue($admin->is_active);
        $this->assertSame('ru', $admin->locale);
        $this->assertTrue(Hash::check('ConfiguredBrowserPassword123!', $admin->password));
    }
}
