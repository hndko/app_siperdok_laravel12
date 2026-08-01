<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MeApiController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user()->load('roles');

        return response()->json([
            'status' => 'success',
            'message' => 'OK',
            'data' => [
                'user' => $user,
                'role' => $user->getRoleNames()->first(),
                'notifications' => $user->notifications()->latest()->limit(10)->get(),
                'unread_notifications_count' => $user->notifications()->where('is_read', false)->count(),
            ],
        ]);
    }
}
