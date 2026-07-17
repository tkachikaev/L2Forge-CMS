<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\GameServerDeletionConfirmationRequired;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveGameServerSettingsRequest;
use App\Models\GameServer;
use App\Services\Servers\GameServerAdministration;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GameServerController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.game-server');
    }

    public function store(
        SaveGameServerSettingsRequest $request,
        GameServerAdministration $servers,
    ): RedirectResponse {
        $servers->save(null, $this->values($request->validated()));

        return redirect()
            ->route('admin.settings.game-server')
            ->with('status', __('Game server added.'));
    }

    public function update(
        SaveGameServerSettingsRequest $request,
        GameServer $gameServer,
        GameServerAdministration $servers,
    ): RedirectResponse {
        $servers->save($gameServer, $this->values($request->validated()));

        return redirect()
            ->route('admin.settings.game-server')
            ->with('status', __('Game server settings saved.'));
    }

    public function destroy(
        GameServer $gameServer,
        GameServerAdministration $servers,
    ): RedirectResponse {
        $name = $gameServer->name;

        try {
            $servers->delete($gameServer);
        } catch (GameServerDeletionConfirmationRequired $exception) {
            return redirect()
                ->route('admin.settings.game-server')
                ->with('warning', __('This GameServer cannot be deleted through the legacy endpoint because :count game accounts would become unavailable. Use the current server card confirmation.', [
                    'count' => $exception->impact['accounts_becoming_unavailable'],
                ]));
        }

        return redirect()
            ->route('admin.settings.game-server')
            ->with('status', __('Game server :name deleted.', ['name' => $name]));
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{name: string, rates: string|null, chronicle: string|null, mode: string|null, translations: array<string, string>}
     */
    private function values(array $validated): array
    {
        $translations = [];
        foreach ((array) ($validated['translations'] ?? []) as $locale => $translation) {
            if (is_array($translation)) {
                $translations[(string) $locale] = (string) ($translation['name'] ?? '');
            }
        }

        return [
            'name' => (string) ($validated['server_name'] ?? ''),
            'translations' => $translations,
            'rates' => isset($validated['server_rates']) ? (string) $validated['server_rates'] : null,
            'chronicle' => isset($validated['server_chronicle']) ? (string) $validated['server_chronicle'] : null,
            'mode' => isset($validated['server_mode']) ? (string) $validated['server_mode'] : null,
        ];
    }
}
