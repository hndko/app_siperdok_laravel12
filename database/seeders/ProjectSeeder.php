<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $pemohonIds = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'pemohon')
            ->pluck('model_id')
            ->toArray();

        $penilaiIds = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'penilai')
            ->pluck('model_id')
            ->toArray();

        $docTypes = DocumentType::all(['id', 'code', 'name'])->toArray();

        if (empty($pemohonIds) || empty($docTypes)) {
            return;
        }

        $statuses = ['draft', 'submitted', 'in_review', 'revision', 'approved', 'rejected'];

        $projectsBatch = [];
        $documentsBatch = [];
        $logsBatch = [];
        $now = now();

        $totalProjects = 10000;
        $chunkSize = 1000;

        for ($i = 1; $i <= $totalProjects; $i++) {
            $applicantId = $pemohonIds[array_rand($pemohonIds)];
            $docType = $docTypes[array_rand($docTypes)];
            $docTypeId = $docType['id'];

            // Weighted status distribution
            if ($i <= 2000) {
                $status = 'draft';
            } elseif ($i <= 4500) {
                $status = 'submitted';
            } elseif ($i <= 6000) {
                $status = 'in_review';
            } elseif ($i <= 7500) {
                $status = 'revision';
            } elseif ($i <= 9200) {
                $status = 'approved';
            } else {
                $status = 'rejected';
            }

            $evaluatorId = in_array($status, ['in_review', 'revision', 'approved', 'rejected'])
                ? $penilaiIds[array_rand($penilaiIds)]
                : null;

            $createdAt = $now->copy()->subDays(rand(1, 180))->subMinutes(rand(1, 1440));
            $submittedAt = ($status !== 'draft') ? $createdAt->copy()->addHours(rand(1, 12)) : null;
            $approvedAt = ($status === 'approved') ? $submittedAt->copy()->addDays(rand(1, 14)) : null;
            $rejectedAt = ($status === 'rejected') ? $submittedAt->copy()->addDays(rand(1, 14)) : null;

            $projectNum = 'PRJ-'.$createdAt->format('Ym').'-'.str_pad($i, 5, '0', STR_PAD_LEFT);
            $title = "Permohonan Document {$docType['code']} - Proyek Usaha #".rand(1000, 9999);

            $projectsBatch[] = [
                'id' => $i,
                'project_number' => $projectNum,
                'title' => $title,
                'applicant_id' => $applicantId,
                'evaluator_id' => $evaluatorId,
                'document_type_id' => $docTypeId,
                'status' => $status,
                'description' => "Pengajuan dokumen kelayakan lingkungan {$docType['name']} untuk operasional kawasan industri dan pembangunan.",
                'submitted_at' => $submittedAt ? $submittedAt->toDateTimeString() : null,
                'approved_at' => $approvedAt ? $approvedAt->toDateTimeString() : null,
                'rejected_at' => $rejectedAt ? $rejectedAt->toDateTimeString() : null,
                'created_at' => $createdAt->toDateTimeString(),
                'updated_at' => ($approvedAt ?? $rejectedAt ?? $submittedAt ?? $createdAt)->toDateTimeString(),
            ];

            // Project Document record
            $documentsBatch[] = [
                'project_id' => $i,
                'document_name' => "Berkas_Kelayakan_{$docType['code']}.pdf",
                'file_path' => "documents/sample_berkas_{$i}.pdf",
                'file_name' => "Berkas_Kelayakan_{$docType['code']}.pdf",
                'file_size' => rand(500000, 5000000),
                'mime_type' => 'application/pdf',
                'version' => ($status === 'revision') ? 2 : 1,
                'uploaded_by' => $applicantId,
                'created_at' => $createdAt->toDateTimeString(),
                'updated_at' => $createdAt->toDateTimeString(),
            ];

            // Initial log: Draft / Submission
            $logsBatch[] = [
                'project_id' => $i,
                'user_id' => $applicantId,
                'action' => ($status === 'draft') ? 'create_draft' : 'submit',
                'previous_status' => null,
                'new_status' => ($status === 'draft') ? 'draft' : 'submitted',
                'notes' => ($status === 'draft') ? 'Membuat draft permohonan baru.' : 'Mengirimkan permohonan untuk proses penilaian.',
                'created_at' => $createdAt->toDateTimeString(),
                'updated_at' => $createdAt->toDateTimeString(),
            ];

            // Additional logs based on progress
            if ($status === 'revision') {
                $logsBatch[] = [
                    'project_id' => $i,
                    'user_id' => $evaluatorId ?? $applicantId,
                    'action' => 'request_revision',
                    'previous_status' => 'submitted',
                    'new_status' => 'revision',
                    'notes' => 'Mohon perbaiki data Peta Lokasi dan Lampiran Uji Laboratorium Air Limbah.',
                    'created_at' => $submittedAt ? $submittedAt->copy()->addDays(2)->toDateTimeString() : $createdAt->toDateTimeString(),
                    'updated_at' => $submittedAt ? $submittedAt->copy()->addDays(2)->toDateTimeString() : $createdAt->toDateTimeString(),
                ];
            } elseif ($status === 'approved') {
                $logsBatch[] = [
                    'project_id' => $i,
                    'user_id' => $evaluatorId ?? $applicantId,
                    'action' => 'approve',
                    'previous_status' => 'in_review',
                    'new_status' => 'approved',
                    'notes' => 'Dokumen permohonan dinyatakan LENGKAP dan MEMENUHI SYARAT kelayakan.',
                    'created_at' => $approvedAt ? $approvedAt->toDateTimeString() : $createdAt->toDateTimeString(),
                    'updated_at' => $approvedAt ? $approvedAt->toDateTimeString() : $createdAt->toDateTimeString(),
                ];
            } elseif ($status === 'rejected') {
                $logsBatch[] = [
                    'project_id' => $i,
                    'user_id' => $evaluatorId ?? $applicantId,
                    'action' => 'reject',
                    'previous_status' => 'in_review',
                    'new_status' => 'rejected',
                    'notes' => 'Permohonan ditolak karena lokasi tidak sesuai dengan Tata Ruang Wilayah (RTRW).',
                    'created_at' => $rejectedAt ? $rejectedAt->toDateTimeString() : $createdAt->toDateTimeString(),
                    'updated_at' => $rejectedAt ? $rejectedAt->toDateTimeString() : $createdAt->toDateTimeString(),
                ];
            }

            // Flush chunks to DB
            if (count($projectsBatch) >= $chunkSize) {
                DB::table('projects')->insert($projectsBatch);
                DB::table('project_documents')->insert($documentsBatch);
                DB::table('assessment_logs')->insert($logsBatch);

                $projectsBatch = [];
                $documentsBatch = [];
                $logsBatch = [];
            }
        }

        if (! empty($projectsBatch)) {
            DB::table('projects')->insert($projectsBatch);
            DB::table('project_documents')->insert($documentsBatch);
            DB::table('assessment_logs')->insert($logsBatch);
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement(<<<'SQL'
                SELECT setval(
                    pg_get_serial_sequence('projects', 'id'),
                    GREATEST((SELECT COALESCE(MAX(id), 0) FROM projects), 1),
                    (SELECT COUNT(*) > 0 FROM projects)
                )
            SQL);
        }
    }
}
