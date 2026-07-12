<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminLoginLog;
use App\Models\News;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'admin' => Auth::guard('admin')->user(),
            'adminCount' => Admin::query()->count(),
            'newsCount' => News::query()->count(),
            'recentLogins' => AdminLoginLog::query()
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }
}
