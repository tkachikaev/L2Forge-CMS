<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Servers\ServerMonitorCoordinator;
use App\Services\Servers\ServerStatusOverview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(
        ServerStatusOverview $statuses,
        ServerMonitorCoordinator $monitorCoordinator,
    ): View {
        return view('admin.dashboard', [
            'admin' => Auth::guard('admin')->user(),
            'monitor' => $statuses->get(),
            'monitorRefreshDue' => $monitorCoordinator->isDue(),
        ]);
    }

    public function refresh(ServerMonitorCoordinator $monitorCoordinator): RedirectResponse
    {
        $result = $monitorCoordinator->refreshIfDue(force: true);

        return redirect()
            ->route('admin.dashboard')
            ->with('status', $result['refreshing']
                ? __('Server status refresh is already running.')
                : __('Server status refreshed.'));
    }
}
