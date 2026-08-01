<?php

namespace App\Http\Controllers\Api\Modules\Exports;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportCertificatePdfApiController extends Controller
{
    public function __invoke(int $id)
    {
        $project = Project::with(['applicant', 'evaluator', 'certificateIssuer', 'documentType', 'documents.uploader'])->findOrFail($id);

        $this->authorize('view', $project);
        abort_unless($project->status === Project::STATUS_CERTIFICATE_ISSUED, 422, 'Certificate resmi hanya dapat diunduh setelah dokumen diterbitkan.');

        $submittedAt = $project->submitted_at ? $project->submitted_at->format('d F Y') : '-';
        $approvedAt = $project->approved_at ? $project->approved_at->format('d F Y') : date('d F Y');
        $issuedAt = $project->certificate_issued_at ? $project->certificate_issued_at->format('d F Y') : '-';
        $docTypeName = $project->documentType ? $project->documentType->name : '-';
        $docTypeCode = $project->documentType ? $project->documentType->code : '-';
        $applicantName = $project->applicant ? $project->applicant->name : '-';
        $companyName = $project->applicant ? $project->applicant->company_name : '-';
        $issuerName = $project->certificateIssuer ? $project->certificateIssuer->name : ($project->evaluator?->name ?? 'Pejabat Penerbit');
        $issuerNip = $project->certificateIssuer ? ($project->certificateIssuer->nip_nik ?? '-') : ($project->evaluator?->nip_nik ?? '-');
        $certificateNumber = $project->certificate_number;

        $html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Surat Pengesahan Kelayakan Dokumen</title>
            <style>
                body { font-family: Helvetica, Arial, sans-serif; color: #333; line-height: 1.5; margin: 20px; }
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
            <div class="header">
                <p class="logo-title">REPUBLIK INDONESIA</p>
                <p class="sub-title">SISTEM INFORMASI PERSETUJUAN DOKUMEN KELAYAKAN (SIPERDOK)</p>
                <p class="sub-title">Kementerian / Instansi Lingkungan Hidup dan Kehutanan</p>
            </div>

            <div class="doc-title">SURAT PENGESAHAN KELAYAKAN DOKUMEN</div>
            <div class="doc-num">Nomor Certificate: {$certificateNumber}</div>

            <div class="content">
                <p>Berdasarkan hasil evaluasi dan penilaian teknis yang dilakukan oleh Tim Evaluator Dokumen Kelayakan, bersama ini menerangkan bahwa permohonan dokumen kelayakan berikut:</p>
                <table class="table-info">
                    <tr><td class="label">Nomor Permohonan</td><td>: {$project->project_number}</td></tr>
                    <tr><td class="label">Judul Kegiatan / Proyek</td><td>: {$project->title}</td></tr>
                    <tr><td class="label">Jenis Dokumen</td><td>: {$docTypeName} ({$docTypeCode})</td></tr>
                    <tr><td class="label">Nama Pemohon</td><td>: {$applicantName}</td></tr>
                    <tr><td class="label">Perusahaan / Instansi</td><td>: {$companyName}</td></tr>
                    <tr><td class="label">Tanggal Pengajuan</td><td>: {$submittedAt}</td></tr>
                    <tr><td class="label">Tanggal Disetujui</td><td>: {$approvedAt}</td></tr>
                    <tr><td class="label">Tanggal Diterbitkan</td><td>: {$issuedAt}</td></tr>
                </table>
                <div class="stamp-box">
                    <p class="stamp-title">STATUS: CERTIFICATE DITERBITKAN</p>
                    <p style="margin: 5px 0 0 0; font-size: 9pt; color: #555;">Dokumen dinyatakan LENGKAP, SAH, dan diterbitkan secara elektronik melalui SIPERDOK.</p>
                </div>
                <p>Demikian Surat Pengesahan Kelayakan Dokumen ini diterbitkan secara elektronik melalui sistem SIPERDOK untuk dipergunakan sebagaimana mestinya.</p>
            </div>

            <div class="footer-sig">
                <div class="sig-box">
                    <p>Ditetapkan di Jakarta<br>Pada Tanggal: {$issuedAt}</p>
                    <p style="font-weight: bold;">Pejabat Penerbit Dokumen Elektronik</p>
                    <br><br><br>
                    <p style="font-weight: bold; text-decoration: underline;">{$issuerName}</p>
                    <p style="font-size: 9pt; color: #666;">NIP. {$issuerNip}</p>
                </div>
            </div>
        </body>
        </html>
        HTML;

        return Pdf::loadHTML($html)->download('Surat_Pengesahan_Kelayakan_'.$project->project_number.'.pdf');
    }
}
