<?php

namespace App\Http\Controllers\Api\Modules\Dashboard;

use App\Http\Controllers\Api\Modules\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class ShowDashboardApiController extends Controller
{
    use RespondsWithApi;

    public function __construct(private readonly DashboardService $dashboard) {}

    public function __invoke(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        return $this->success($this->dashboard->dataFor($user));
    }
}
