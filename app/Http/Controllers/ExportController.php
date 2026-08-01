<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
{
    public function exportProjectsCsv(Request $request)
    {
        $query = Project::with(['applicant', 'evaluator', 'documentType']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $projects = $query->orderBy('created_at', 'desc')->limit(1000)->get();

        $csvHeader = ['ID', 'Nomor Permohonan', 'Judul Project', 'Jenis Dokumen', 'Pemohon / Perusahaan', 'Penilai', 'Status', 'Tanggal Pengajuan', 'Tanggal Keputusan'];
        
        $callback = function () use ($projects, $csvHeader) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $csvHeader);

            foreach ($projects as $prj) {
                fputcsv($file, [
                    $prj->id,
                    $prj->project_number,
                    $prj->title,
                    $prj->documentType->name ?? '-',
                    $prj->applicant->name . ' (' . ($prj->applicant->company_name ?? '-') . ')',
                    $prj->evaluator->name ?? 'Belum Ditugaskan',
                    strtoupper($prj->status),
                    $prj->submitted_at ? $prj->submitted_at->format('Y-m-d H:i') : '-',
                    $prj->approved_at ? $prj->approved_at->format('Y-m-d H:i') : ($prj->rejected_at ? $prj->rejected_at->format('Y-m-d H:i') : '-'),
                ]);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Laporan_Permohonan_Dokumen_' . date('Ymd_His') . '.csv"',
        ]);
    }

    public function exportCertificatePdf($id)
    {
        $project = Project::with(['applicant', 'evaluator', 'documentType', 'documents'])->findOrFail($id);

        if ($project->status !== Project::STATUS_APPROVED) {
            return back()->with('error', 'Dokumen pengesahan hanya dapat diterbitkan untuk permohonan yang telah DISETUJU.');
        }

        $pdf = Pdf::loadView('exports.certificate_pdf', compact('project'));
        return $pdf->download('Surat_Pengesahan_Kelayakan_' . $project->project_number . '.pdf');
    }
}
