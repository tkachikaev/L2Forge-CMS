<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveGameServerSettingsRequest;
use App\Http\Requests\Admin\SaveGeneralSettingsRequest;
use App\Models\GameServer;
use App\Services\GameServerSettings;
use App\Services\Settings\SettingsImageStorage;
use App\Services\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class SettingsController extends Controller
{
    public function general(SiteSettings $siteSettings): View
    {
        return view('admin.settings.general', [
            'settings' => $siteSettings->values(),
            'timezones' => timezone_identifiers_list(),
        ]);
    }

    public function updateGeneral(
        SaveGeneralSettingsRequest $request,
        SiteSettings $siteSettings,
        SettingsImageStorage $images,
    ): RedirectResponse {
        $validated = $request->validated();
        $current = $siteSettings->values();
        $storedLogo = null;
        $storedFavicon = null;

        try {
            if ($request->hasFile('logo')) {
                $storedLogo = $images->store($request->file('logo'), 'logo');
            }

            if ($request->hasFile('favicon')) {
                $storedFavicon = $images->store($request->file('favicon'), 'favicon');
            }

            $logo = $storedLogo
                ?? ($request->boolean('remove_logo') ? null : $current['logo']);
            $favicon = $storedFavicon
                ?? ($request->boolean('remove_favicon') ? null : $current['favicon']);

            $siteSettings->update([
                'name' => (string) $validated['site_name'],
                'description' => (string) ($validated['site_description'] ?? ''),
                'logo' => $logo,
                'favicon' => $favicon,
                'timezone' => (string) $validated['timezone'],
                'admin_email' => (string) ($validated['admin_email'] ?? ''),
                'footer_text' => (string) ($validated['footer_text'] ?? ''),
            ]);
        } catch (Throwable $exception) {
            if ($storedLogo !== null) {
                $images->delete($storedLogo, 'logo');
            }

            if ($storedFavicon !== null) {
                $images->delete($storedFavicon, 'favicon');
            }

            throw $exception;
        }

        if ($current['logo'] !== null && $current['logo'] !== $logo) {
            $images->delete($current['logo'], 'logo');
        }

        if ($current['favicon'] !== null && $current['favicon'] !== $favicon) {
            $images->delete($current['favicon'], 'favicon');
        }

        return redirect()
            ->route('admin.settings.general')
            ->with('status', 'Основные настройки сохранены.');
    }

    public function gameServer(GameServerSettings $gameServerSettings): View
    {
        return view('admin.settings.game-server', [
            'servers' => $gameServerSettings->all(),
        ]);
    }

    public function storeGameServer(
        SaveGameServerSettingsRequest $request,
        GameServerSettings $gameServerSettings,
    ): RedirectResponse {
        $validated = $request->validated();

        $gameServerSettings->create($this->gameServerValues($validated));

        return redirect()
            ->route('admin.settings.game-server')
            ->with('status', 'Игровой сервер добавлен.');
    }

    public function updateGameServer(
        SaveGameServerSettingsRequest $request,
        GameServer $gameServer,
        GameServerSettings $gameServerSettings,
    ): RedirectResponse {
        $validated = $request->validated();

        $gameServerSettings->update($gameServer, $this->gameServerValues($validated));

        return redirect()
            ->route('admin.settings.game-server')
            ->with('status', 'Настройки игрового сервера сохранены.');
    }

    public function destroyGameServer(
        GameServer $gameServer,
        GameServerSettings $gameServerSettings,
    ): RedirectResponse {
        $name = $gameServer->name;
        $gameServerSettings->delete($gameServer);

        return redirect()
            ->route('admin.settings.game-server')
            ->with('status', 'Игровой сервер «'.$name.'» удалён.');
    }

    /**
     * @param array<string, mixed> $validated
     * @return array{name: string, rates: string|null, chronicle: string|null, mode: string|null}
     */
    private function gameServerValues(array $validated): array
    {
        return [
            'name' => (string) $validated['server_name'],
            'rates' => isset($validated['server_rates']) ? (string) $validated['server_rates'] : null,
            'chronicle' => isset($validated['server_chronicle']) ? (string) $validated['server_chronicle'] : null,
            'mode' => isset($validated['server_mode']) ? (string) $validated['server_mode'] : null,
        ];
    }

    public function loginServer(): View
    {
        return $this->placeholder(
            title: 'Логин сервер',
            description: 'Здесь появятся параметры подключения и состояния логин-сервера.',
        );
    }

    private function placeholder(string $title, string $description): View
    {
        return view('admin.settings.placeholder', compact('title', 'description'));
    }
}
