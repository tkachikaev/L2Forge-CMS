<?php

namespace App\Http\Controllers;

use App\Services\Servers\ServerMonitorCoordinator;
use App\Services\Servers\ServerStatusOverview;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;

final class ServerMonitorStatusController extends Controller
{
    public function __invoke(
        ServerMonitorCoordinator $coordinator,
        ServerStatusOverview $statuses,
    ): JsonResponse {
        $refresh = $coordinator->refreshIfDue();
        $monitor = $statuses->get();

        $response = response()->json([
            'refreshing' => $refresh['refreshing'],
            'fresh' => ! $coordinator->isDue(),
            'monitor' => $this->payload($monitor),
        ]);

        return $response
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    /**
     * @param  array{
     *     total_online:int,
     *     partial:bool,
     *     checked_at:CarbonInterface|null,
     *     game_servers:list<array{id:int,name:string,state:string,availability_state:string,players:int|null,checked_at:CarbonInterface|null}>,
     *     login_servers:list<array{id:int,name:string,state:string,checked_at:CarbonInterface|null}>
     * }  $monitor
     * @return array<string,mixed>
     */
    private function payload(array $monitor): array
    {
        $games = array_map(static fn (array $server): array => [
            'id' => $server['id'],
            'state' => $server['state'],
            'availability_state' => $server['availability_state'],
            'players' => $server['players'],
            'admin_state_label' => match ($server['state']) {
                'online' => __('Running'),
                'offline' => __('Unavailable'),
                default => __('Status pending'),
            },
            'public_state_label' => match ($server['availability_state']) {
                'online' => __('In game'),
                'offline' => __('Unavailable'),
                default => __('Status pending'),
            },
            'admin_online_label' => $server['players'] !== null
                ? __(':count online', ['count' => number_format($server['players'], 0, '.', ' ')])
                : '—',
            'public_online_label' => $server['players'] !== null
                ? __('Online: :count', ['count' => number_format($server['players'], 0, '.', ' ')])
                : __('Online temporarily unavailable'),
        ], $monitor['game_servers']);

        $logins = array_map(static fn (array $server): array => [
            'id' => $server['id'],
            'state' => $server['state'],
            'state_label' => match ($server['state']) {
                'online' => __('Running'),
                'offline' => __('Unavailable'),
                default => __('Status pending'),
            },
        ], $monitor['login_servers']);

        return [
            'total_online' => $monitor['total_online'],
            'total_online_formatted' => number_format($monitor['total_online'], 0, '.', ' '),
            'partial' => $monitor['partial'],
            'updated_label' => $monitor['checked_at']
                ? __('Updated :time', ['time' => $monitor['checked_at']->diffForHumans()])
                : __('Not checked yet'),
            'game_servers' => $games,
            'login_servers' => $logins,
        ];
    }
}
