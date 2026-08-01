<?php

namespace App\Http\Controllers\Api\Notifications;

use App\Http\Controllers\Api\Modules\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;

class IndexNotificationApiController extends Controller
{
    use RespondsWithApi;

    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $notifications = Notification::query()
            ->where('user_id', $request->user()->id)
            ->orderBy('is_read')
            ->orderByDesc('created_at')
            ->cursorPaginate($validated['per_page'] ?? 10)
            ->withQueryString();

        return $this->success([
            'notifications' => NotificationResource::collection($notifications),
            'unread_count' => Notification::query()
                ->where('user_id', $request->user()->id)
                ->where('is_read', false)
                ->count(),
        ]);
    }
}
