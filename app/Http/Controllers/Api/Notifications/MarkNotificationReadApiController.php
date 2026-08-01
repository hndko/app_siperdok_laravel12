<?php

namespace App\Http\Controllers\Api\Notifications;

use App\Http\Controllers\Api\Modules\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;

class MarkNotificationReadApiController extends Controller
{
    use RespondsWithApi;

    public function __invoke(Request $request, int $notification)
    {
        $notificationModel = Notification::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($notification);

        if (! $notificationModel->is_read) {
            $notificationModel->forceFill([
                'is_read' => true,
                'read_at' => now(),
            ])->save();
        }

        return $this->success([
            'notification' => new NotificationResource($notificationModel),
            'unread_count' => Notification::query()
                ->where('user_id', $request->user()->id)
                ->where('is_read', false)
                ->count(),
        ], 'Notifikasi ditandai sudah dibaca.');
    }
}
