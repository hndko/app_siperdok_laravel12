<?php

namespace App\Services;

use App\Jobs\CreateProjectStatusNotification;
use App\Models\AssessmentLog;
use App\Models\CertificateCounter;
use App\Models\Project;
use App\Models\ProjectVerificationChecklist;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectWorkflowService
{
    public function logSubmission(Project $project, User $actor, string $action, ?string $previousStatus, string $notes): void
    {
        AssessmentLog::create([
            'project_id' => $project->id,
            'user_id' => $actor->id,
            'action' => $action,
            'previous_status' => $previousStatus,
            'new_status' => $project->status,
            'notes' => $notes,
        ]);

        if ($project->status === Project::STATUS_SUBMITTED) {
            $this->queueNotification($project, $actor, 'submitted', 'Permohonan Dokumen Dikirim', 'Permohonan dokumen '.$project->project_number.' telah dikirim untuk proses penilaian.', 'info');
        }
    }

    public function startReview(int $projectId, User $actor, ?string $notes = null): Project
    {
        return DB::transaction(function () use ($projectId, $actor, $notes) {
            $project = Project::query()->lockForUpdate()->findOrFail($projectId);

            if (! ($actor->hasRole('admin') || $actor->hasRole('penilai'))) {
                abort(403, 'Anda tidak berhak memulai review permohonan ini.');
            }

            if ($project->status !== Project::STATUS_SUBMITTED) {
                throw ValidationException::withMessages([
                    'status' => 'Review hanya dapat dimulai dari status submitted.',
                ]);
            }

            $previousStatus = $project->status;
            $project->update([
                'status' => Project::STATUS_IN_REVIEW,
                'evaluator_id' => $actor->id,
            ]);

            AssessmentLog::create([
                'project_id' => $project->id,
                'user_id' => $actor->id,
                'action' => 'start_review',
                'previous_status' => $previousStatus,
                'new_status' => Project::STATUS_IN_REVIEW,
                'notes' => $notes ?: 'Penilai memulai proses verifikasi administrasi dan review dokumen.',
            ]);

            $this->queueNotification($project, $actor, 'start_review', 'Permohonan Mulai Direview', 'Permohonan dokumen '.$project->project_number.' sedang dalam proses penilaian.', 'info');

            return $project->refresh();
        });
    }

    public function decide(int $projectId, User $actor, string $decision, string $notes): Project
    {
        return DB::transaction(function () use ($projectId, $actor, $decision, $notes) {
            $project = Project::query()->lockForUpdate()->findOrFail($projectId);

            if (! ($actor->hasRole('admin') || $actor->hasRole('penilai'))) {
                abort(403, 'Anda tidak berhak menilai permohonan ini.');
            }

            if ($project->status !== Project::STATUS_IN_REVIEW) {
                throw ValidationException::withMessages([
                    'status' => 'Keputusan hanya dapat diberikan pada permohonan berstatus in_review.',
                ]);
            }

            if (! $actor->hasRole('admin') && $project->evaluator_id !== $actor->id) {
                abort(403, 'Permohonan ini sedang ditangani penilai lain.');
            }

            if (! in_array($decision, [Project::STATUS_APPROVED, Project::STATUS_REVISION, Project::STATUS_REJECTED], true)) {
                throw ValidationException::withMessages([
                    'decision' => 'Keputusan penilaian tidak valid.',
                ]);
            }

            $this->validateRequiredChecklistsForDecision($project, $decision);

            $previousStatus = $project->status;
            $now = now();

            $updateData = [
                'status' => $decision,
                'evaluator_id' => $actor->id,
            ];

            $action = match ($decision) {
                Project::STATUS_APPROVED => 'approve',
                Project::STATUS_REVISION => 'request_revision',
                Project::STATUS_REJECTED => 'reject',
            };

            if ($decision === Project::STATUS_APPROVED) {
                $updateData['approved_at'] = $now;
                $updateData['rejected_at'] = null;
            }

            if ($decision === Project::STATUS_REJECTED) {
                $updateData['rejected_at'] = $now;
                $updateData['approved_at'] = null;
            }

            $project->update($updateData);

            AssessmentLog::create([
                'project_id' => $project->id,
                'user_id' => $actor->id,
                'action' => $action,
                'previous_status' => $previousStatus,
                'new_status' => $decision,
                'notes' => $notes,
            ]);

            [$title, $message, $type] = $this->decisionNotification($project, $decision, $notes);
            $this->queueNotification($project, $actor, $action, $title, $message, $type);

            return $project->refresh();
        });
    }

    public function issueCertificate(int $projectId, User $actor): Project
    {
        return DB::transaction(function () use ($projectId, $actor) {
            $project = Project::query()->lockForUpdate()->findOrFail($projectId);

            if (! ($actor->hasRole('admin') || $actor->hasRole('penilai'))) {
                abort(403, 'Anda tidak berhak menerbitkan certificate.');
            }

            if ($project->status === Project::STATUS_CERTIFICATE_ISSUED) {
                return $project->refresh();
            }

            if ($project->status !== Project::STATUS_APPROVED) {
                throw ValidationException::withMessages([
                    'status' => 'Certificate hanya dapat diterbitkan dari status approved.',
                ]);
            }

            if ($project->certificate_number || $project->certificate_issued_at) {
                throw ValidationException::withMessages([
                    'certificate' => 'Certificate sudah pernah diterbitkan.',
                ]);
            }

            $this->validateRequiredChecklistsForDecision($project, Project::STATUS_APPROVED);

            if (! $project->project_number || ! $project->title || ! $project->applicant_id || ! $project->approved_at) {
                throw ValidationException::withMessages([
                    'certificate' => 'Data permohonan belum lengkap untuk penerbitan certificate.',
                ]);
            }

            $issuedAt = now();
            $certificateNumber = $this->nextCertificateNumber($issuedAt);
            $previousStatus = $project->status;

            $project->update([
                'status' => Project::STATUS_CERTIFICATE_ISSUED,
                'certificate_number' => $certificateNumber,
                'certificate_issued_at' => $issuedAt,
                'certificate_issued_by' => $actor->id,
            ]);

            AssessmentLog::create([
                'project_id' => $project->id,
                'user_id' => $actor->id,
                'action' => 'issue_certificate',
                'previous_status' => $previousStatus,
                'new_status' => Project::STATUS_CERTIFICATE_ISSUED,
                'notes' => 'Certificate diterbitkan dengan nomor '.$certificateNumber.'.',
            ]);

            $this->queueNotification(
                $project,
                $actor,
                'certificate_issued',
                'Dokumen Pengajuan Telah Diterbitkan',
                'Dokumen pengajuan '.$project->title.' telah disahkan dan diterbitkan dengan nomor '.$certificateNumber.'.',
                'success',
            );

            return $project->refresh();
        });
    }

    private function decisionNotification(Project $project, string $decision, string $notes): array
    {
        return match ($decision) {
            Project::STATUS_APPROVED => [
                'Permohonan Dokumen DISETUJUI',
                'Permohonan dokumen '.$project->project_number.' telah disetujui oleh penilai.',
                'success',
            ],
            Project::STATUS_REVISION => [
                'Permohonan Dokumen Memerlukan Revisi',
                'Permohonan dokumen '.$project->project_number.' memerlukan revisi. Catatan: '.$notes,
                'warning',
            ],
            Project::STATUS_REJECTED => [
                'Permohonan Dokumen DITOLAK',
                'Permohonan dokumen '.$project->project_number.' ditolak. Alasan: '.$notes,
                'danger',
            ],
        };
    }

    private function queueNotification(Project $project, User $actor, string $event, string $title, string $message, string $type): void
    {
        CreateProjectStatusNotification::dispatch(
            projectId: $project->id,
            userId: $project->applicant_id,
            actorId: $actor->id,
            event: $event,
            title: $title,
            message: $message,
            type: $type,
        )->afterCommit();
    }

    private function validateRequiredChecklistsForDecision(Project $project, string $decision): void
    {
        $required = ProjectVerificationChecklist::query()
            ->where('project_id', $project->id)
            ->whereHas('item', fn ($query) => $query->where('is_required', true)->where('is_active', true));

        $requiredTotal = (clone $required)->count();
        $pendingCount = (clone $required)->where('status', ProjectVerificationChecklist::STATUS_PENDING)->count();
        $failedCount = (clone $required)->where('status', ProjectVerificationChecklist::STATUS_FAILED)->count();

        if ($requiredTotal === 0 || $pendingCount > 0) {
            throw ValidationException::withMessages([
                'checklists' => 'Seluruh checklist wajib harus selesai sebelum keputusan diberikan.',
            ]);
        }

        if ($decision === Project::STATUS_APPROVED && $failedCount > 0) {
            throw ValidationException::withMessages([
                'checklists' => 'Permohonan tidak dapat disetujui karena masih ada checklist wajib yang gagal.',
            ]);
        }
    }

    private function nextCertificateNumber(\DateTimeInterface $issuedAt): string
    {
        $year = (int) $issuedAt->format('Y');
        $month = (int) $issuedAt->format('m');

        CertificateCounter::upsert(
            [[
                'year' => $year,
                'month' => $month,
                'next_number' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]],
            ['year', 'month'],
            ['updated_at'],
        );

        $counter = CertificateCounter::query()
            ->where('year', $year)
            ->where('month', $month)
            ->lockForUpdate()
            ->firstOrFail();

        $number = $counter->next_number;
        $counter->increment('next_number');

        return sprintf('CERT/%d/%02d/%06d', $year, $month, $number);
    }
}
