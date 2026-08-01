<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;

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
                    ($prj->applicant->name ?? '-') . ' (' . ($prj->applicant->company_name ?? '-') . ')',
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

    public function previewCertificate($id)
    {
        $project = Project::with(['applicant', 'evaluator', 'documentType', 'documents'])->findOrFail($id);

        if ($project->status !== Project::STATUS_APPROVED) {
            return back()->with('error', 'Surat pengesahan hanya tersedia untuk permohonan yang telah DISETUJU.');
        }

        return Inertia::render('Exports/CertificatePreview', compact('project'));
    }

    public function exportCertificatePdf($id)
    {
        $project = Project::with(['applicant', 'evaluator', 'documentType', 'documents.uploader'])->findOrFail($id);

        if ($project->status !== Project::STATUS_APPROVED) {
            return back()->with('error', 'Dokumen pengesahan hanya dapat diterbitkan untuk permohonan yang telah DISETUJU.');
        }

        $submittedAt = $project->submitted_at ? $project->submitted_at->format('d F Y') : '-';
        $approvedAt = $project->approved_at ? $project->approved_at->format('d F Y') : date('d F Y');
        $docTypeName = $project->documentType ? $project->documentType->name : '-';
        $docTypeCode = $project->documentType ? $project->documentType->code : '-';
        $applicantName = $project->applicant ? $project->applicant->name : '-';
        $companyName = $project->applicant ? $project->applicant->company_name : '-';
        $evaluatorName = $project->evaluator ? $project->evaluator->name : 'Dr. Hendra Penilai';
        $evaluatorNip = $project->evaluator ? ($project->evaluator->nip_nik ?? '197505052002121002') : '197505052002121002';
        $year = date('Y');

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Surat Pengesahan Kelayakan Dokumen</title>
            <style>
                body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.5; margin: 20px; }
                .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
                .logo-title { font-size: 18pt; font-weight: bold; text-transform: uppercase; margin: 0; }
                .sub-title { font-size: 11pt; color: #555; margin: 0; }
                .doc-title { text-align: center; font-size: 14pt; font-weight: bold; margin-top: 20px; text-decoration: underline; text-transform: uppercase; }
                .doc-num { text-align: center; font-size: 10pt; color: #444; margin-bottom: 25px; }
                .content { margin-bottom: 25px; font-size: 11pt; }
                .table-info { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                .table-info td { padding: 6px 10px; vertical-align: top; }
                .table-info td.label { font-weight: bold; width: 35%; }
                .stamp-box { border: 2px solid #28a745; background-color: #f8fff9; padding: 15px; text-align: center; margin-top: 30px; margin-bottom: 30px; border-radius: 6px; }
                .stamp-title { font-size: 14pt; font-weight: bold; color: #28a745; margin: 0; }
                .footer-sig { width: 100%; margin-top: 40px; }
                .sig-box { width: 45%; float: right; text-align: center; }
            </style>
        </head>
        <body>
            <div class='header'>
                <p class='logo-title'>REPUBLIK INDONESIA</p>
                <p class='sub-title'>SISTEM INFORMASI PERSETUJUAN DOKUMEN KELAYAKAN (SIPERDOK)</p>
                <p class='sub-title'>Kementerian / Instansi Lingkungan Hidup dan Kehutanan</p>
            </div>

            <div class='doc-title'>SURAT PENGESAHAN KELAYAKAN DOKUMEN</div>
            <div class='doc-num'>Nomor Penerbitan: SK-SIPERDOK/{$year}/{$project->project_number}</div>

            <div class='content'>
                <p>Berdasarkan hasil evaluasi dan penilaian teknis yang dilakukan oleh Tim Evaluator Dokumen Kelayakan, bersama ini menerangkan bahwa permohonan dokumen kelayakan berikut:</p>

                <table class='table-info'>
                    <tr>
                        <td class='label'>Nomor Permohonan</td>
                        <td>: {$project->project_number}</td>
                    </tr>
                    <tr>
                        <td class='label'>Judul Kegiatan / Proyek</td>
                        <td>: {$project->title}</td>
                    </tr>
                    <tr>
                        <td class='label'>Jenis Dokumen</td>
                        <td>: {$docTypeName} ({$docTypeCode})</td>
                    </tr>
                    <tr>
                        <td class='label'>Nama Pemohon</td>
                        <td>: {$applicantName}</td>
                    </tr>
                    <tr>
                        <td class='label'>Perusahaan / Instansi</td>
                        <td>: {$companyName}</td>
                    </tr>
                    <tr>
                        <td class='label'>Tanggal Pengajuan</td>
                        <td>: {$submittedAt}</td>
                    </tr>
                    <tr>
                        <td class='label'>Tanggal Disetujui</td>
                        <td>: {$approvedAt}</td>
                    </tr>
                </table>

                <div class='stamp-box'>
                    <p class='stamp-title'>STATUS: DISETUJU (APPROVED)</p>
                    <p style='margin: 5px 0 0 0; font-size: 9pt; color: #555;'>Dokumen dinyatakan LENGKAP, SAH, dan MEMENUHI SYARAT KELAYAKAN LINGKUNGAN</p>
                </div>

                <p>Demikian Surat Pengesahan Kelayakan Dokumen ini diterbitkan secara elektronik melalui sistem SIPERDOK untuk dipergunakan sebagaimana mestinya.</p>
            </div>

            <div class='footer-sig'>
                <div class='sig-box'>
                    <p>Ditetapkan di Jakarta<br>Pada Tanggal: {$approvedAt}</p>
                    <p style='font-weight: bold;'>An. Tim Evaluator Penilai Dokumen</p>
                    <br><br><br>
                    <p style='font-weight: bold; text-decoration: underline;'>{$evaluatorName}</p>
                    <p style='font-size: 9pt; color: #666;'>NIP. {$evaluatorNip}</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $pdf = Pdf::loadHTML($html);
        return $pdf->download('Surat_Pengesahan_Kelayakan_' . $project->project_number . '.pdf');
    }
}
