<?php

namespace App\Http\Controllers\Api\Modules\Users;

use App\Http\Controllers\Api\Modules\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            ->select(['id', 'name', 'email', 'phone', 'nip_nik', 'company_name', 'created_at'])
            ->with('roles')
            ->when($validated['search'] ?? null, function ($query, $search) {
                $operator = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
                $keyword = '%'.str_replace(['%', '_'], ['\%', '\_'], $search).'%';

                $query->where(function ($q) use ($operator, $keyword) {
                    $q->where('name', $operator, $keyword)
                        ->orWhere('email', $operator, $keyword)
                        ->orWhere('company_name', $operator, $keyword)
                        ->orWhere('nip_nik', $operator, $keyword);
                });
            })
            ->when($validated['role'] ?? null, fn ($query, $role) => $query->role($role))
            ->orderByDesc('created_at')
            ->cursorPaginate($validated['per_page'] ?? 20)
            ->withQueryString();

        return $this->success($users);
    }
}
