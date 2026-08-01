<?php

namespace App\Http\Controllers\Api\Modules\Exports;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ExportProjectsCsvApiController extends Controller
{
    public function __invoke(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $this->authorize('export', Project::class);

        $filters = $request->validate($this->filterRules());

        $query = Project::query()
            ->with(['applicant:id,name,company_name', 'evaluator:id,name', 'documentType:id,code,name'])
            ->visibleTo($user)
            ->filter($filters)
            ->orderByDesc('created_at');

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Nomor Permohonan', 'Judul Project', 'Jenis Dokumen', 'Pemohon / Perusahaan', 'Penilai', 'Status', 'Tanggal Pengajuan', 'Tanggal Keputusan']);

            $query->chunkById(500, function ($projects) use ($file) {
                foreach ($projects as $project) {
                    fputcsv($file, [
                        $project->id,
                        $project->project_number,
                        $project->title,
                        $project->documentType->name ?? '-',
                        ($project->applicant->name ?? '-') . ' (' . ($project->applicant->company_name ?? '-') . ')',
                        $project->evaluator->name ?? 'Belum Ditugaskan',
                        strtoupper($project->status),
                        $project->submitted_at ? $project->submitted_at->format('Y-m-d H:i') : '-',
                        $project->approved_at ? $project->approved_at->format('Y-m-d H:i') : ($project->rejected_at ? $project->rejected_at->format('Y-m-d H:i') : '-'),
                    ]);
                }
            });

            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Laporan_Permohonan_Dokumen_' . date('Ymd_His') . '.csv"',
        ]);
    }

    private function filterRules(): array
    {
        return [
            'status' => ['nullable', 'string', 'in:draft,submitted,in_review,revision,approved,rejected'],
            'search' => ['nullable', 'string', 'max:100'],
            'document_type_id' => ['nullable', 'integer', 'exists:document_types,id'],
            'applicant_id' => ['nullable', 'integer', 'exists:users,id'],
            'evaluator_id' => ['nullable', 'integer', 'exists:users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }
}
