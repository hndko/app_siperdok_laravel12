<?php

namespace App\Http\Controllers\Api\Modules\Users;

use App\Http\Controllers\Api\Modules\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class IndexUserApiController extends Controller
{
    use RespondsWithApi;

    public function __invoke(Request $request)
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'max:50'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $users = User::query()
            ->with('roles')
            ->when($validated['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('nip_nik', 'like', "%{$search}%");
                });
            })
            ->when($validated['role'] ?? null, fn ($query, $role) => $query->role($role))
            ->orderByDesc('created_at')
            ->cursorPaginate($validated['per_page'] ?? 20)
            ->withQueryString();

        return $this->success($users);
    }
}
