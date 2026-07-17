<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class BrowserTestSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('testing')) {
            throw new RuntimeException('BrowserTestSeeder may run only in the testing environment.');
        }

        $email = (string) config('browser_tests.admin.email');
        $password = (string) config('browser_tests.admin.password');

        Admin::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Browser Test Admin',
                'password' => Hash::make($password),
                'is_active' => true,
                'locale' => 'ru',
            ],
        );
    }
}
