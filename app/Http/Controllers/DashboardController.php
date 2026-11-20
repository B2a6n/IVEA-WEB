<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DashboardRequest;
use App\Services\DashboardAnalyticsService;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function index(DashboardRequest $request, DashboardAnalyticsService $service): View
    {
        return view('dashboard.index', ['dashboard' => $service->overview($request->toFilters())]);
    }
}
