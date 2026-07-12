<?php

namespace Tests\Feature;

use App\Models\CmsSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UpgradeGameServerMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_071_settings_are_copied_to_the_first_game_server(): void
    {
        Schema::drop('game_servers');

        CmsSetting::query()->updateOrCreate(['key' => 'server.name'], ['value' => 'Legacy x7']);
        CmsSetting::query()->updateOrCreate(['key' => 'server.rates'], ['value' => 'x7']);
        CmsSetting::query()->updateOrCreate(['key' => 'server.chronicle'], ['value' => 'Interlude']);
        CmsSetting::query()->updateOrCreate(['key' => 'server.mode'], ['value' => 'PvP']);

        $migration = require database_path('migrations/2026_07_12_000500_create_game_servers_table.php');
        $migration->up();

        $this->assertDatabaseHas('game_servers', [
            'name' => 'Legacy x7',
            'rates' => 'x7',
            'chronicle' => 'Interlude',
            'mode' => 'PvP',
            'sort_order' => 0,
        ]);
    }
}
