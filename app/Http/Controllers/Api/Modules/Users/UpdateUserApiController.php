<?php

namespace App\Http\Controllers\Api\Modules\Users;

use App\Http\Controllers\Api\Modules\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UpdateUserApiController extends Controller
{
    use RespondsWithApi;

    public function __invoke(Request $request, int $id)
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        $user = User::query()->with('roles')->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'nip_nik' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'string', Rule::in(['admin', 'pemohon', 'penilai'])],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'nip_nik' => $validated['nip_nik'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
        ]);

        $user->syncRoles([$validated['role']]);

        return $this->success(
            $user->fresh('roles'),
            'Data pengguna berhasil diperbarui.',
        );
    }
}
