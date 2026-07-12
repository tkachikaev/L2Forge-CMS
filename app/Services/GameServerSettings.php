<?php

namespace App\Services;

use App\Models\GameServer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

final class GameServerSettings
{
    /**
     * @return array<int, array{
     *     id: int,
     *     name: string,
     *     rates: string,
     *     chronicle: string,
     *     mode: string,
     *     show_rates: bool,
     *     show_chronicle: bool,
     *     show_mode: bool
     * }>
     */
    public function all(): array
    {
        if (! $this->tableExists()) {
            return [];
        }

        try {
            return GameServer::query()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(fn (GameServer $server): array => $this->normalize($server))
                ->all();
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     rates: string,
     *     chronicle: string,
     *     mode: string,
     *     show_rates: bool,
     *     show_chronicle: bool,
     *     show_mode: bool
     * }|null
     */
    public function primary(): ?array
    {
        return $this->all()[0] ?? null;
    }

    /** @param array{name: string, rates?: string|null, chronicle?: string|null, mode?: string|null} $values */
    public function create(array $values): GameServer
    {
        $this->ensureTableExists();

        return DB::transaction(function () use ($values): GameServer {
            $nextSortOrder = ((int) GameServer::query()->max('sort_order')) + 1;

            return GameServer::query()->create([
                'name' => trim($values['name']),
                'rates' => $this->nullableString($values['rates'] ?? null),
                'chronicle' => $this->nullableString($values['chronicle'] ?? null),
                'mode' => $this->nullableString($values['mode'] ?? null),
                'sort_order' => $nextSortOrder,
            ]);
        });
    }

    /** @param array{name: string, rates?: string|null, chronicle?: string|null, mode?: string|null} $values */
    public function update(GameServer $server, array $values): void
    {
        $server->update([
            'name' => trim($values['name']),
            'rates' => $this->nullableString($values['rates'] ?? null),
            'chronicle' => $this->nullableString($values['chronicle'] ?? null),
            'mode' => $this->nullableString($values['mode'] ?? null),
        ]);
    }

    public function delete(GameServer $server): void
    {
        $server->delete();
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     rates: string,
     *     chronicle: string,
     *     mode: string,
     *     show_rates: bool,
     *     show_chronicle: bool,
     *     show_mode: bool
     * }
     */
    private function normalize(GameServer $server): array
    {
        $rates = trim((string) $server->rates);
        $chronicle = trim((string) $server->chronicle);
        $mode = trim((string) $server->mode);

        return [
            'id' => (int) $server->id,
            'name' => trim((string) $server->name),
            'rates' => $rates,
            'chronicle' => $chronicle,
            'mode' => $mode,
            'show_rates' => $rates !== '',
            'show_chronicle' => $chronicle !== '',
            'show_mode' => $this->modeIsVisible($mode),
        ];
    }

    private function modeIsVisible(string $mode): bool
    {
        return $mode !== '' && mb_strtolower($mode) !== 'none';
    }

    private function nullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function ensureTableExists(): void
    {
        if (! $this->tableExists()) {
            throw new RuntimeException('Game servers table is not available. Run database migrations first.');
        }
    }

    private function tableExists(): bool
    {
        try {
            return Schema::hasTable('game_servers');
        } catch (Throwable) {
            return false;
        }
    }
}
