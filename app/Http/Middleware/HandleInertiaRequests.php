<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'nip_nik' => $user->nip_nik,
                    'company_name' => $user->company_name,
                ] : null,
                'role' => $user ? ($user->getRoleNames()->first() ?? 'pemohon') : null,
            ],
            'notifications' => $user ? $user->notifications()->with('project')->latest()->limit(5)->get() : [],
            'unreadNotificationsCount' => $user ? $user->notifications()->where('is_read', false)->count() : 0,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
            ],
        ];
    }
}
