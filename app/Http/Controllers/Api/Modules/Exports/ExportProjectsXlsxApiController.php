<?php

namespace App\Http\Controllers\Api\Modules\Exports;

use App\Exports\ProjectsExport;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportProjectsXlsxApiController extends Controller
{
    public function __invoke(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $this->authorize('export', Project::class);

        $filters = $request->validate([
            'status' => ['nullable', 'string', 'in:draft,submitted,in_review,revision,approved,rejected'],
            'search' => ['nullable', 'string', 'max:100'],
            'document_type_id' => ['nullable', 'integer', 'exists:document_types,id'],
            'applicant_id' => ['nullable', 'integer', 'exists:users,id'],
            'evaluator_id' => ['nullable', 'integer', 'exists:users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        return Excel::download(
            new ProjectsExport($user, $filters),
            'Laporan_Permohonan_Dokumen_' . date('Ymd_His') . '.xlsx',
        );
    }
}
