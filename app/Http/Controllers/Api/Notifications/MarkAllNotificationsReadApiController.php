<?php

namespace App\Http\Controllers\Api\Notifications;

use App\Http\Controllers\Api\Modules\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class MarkAllNotificationsReadApiController extends Controller
{
    use RespondsWithApi;

    public function __invoke(Request $request)
    {
        $updated = Notification::query()
            ->where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return $this->success([
            'updated' => $updated,
            'unread_count' => 0,
        ], 'Semua notifikasi ditandai sudah dibaca.');
    }
}
