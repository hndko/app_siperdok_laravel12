<?php

namespace App\Services;

use App\Jobs\CreateProjectStatusNotification;
use App\Models\AssessmentLog;
use App\Models\Project;
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

            if (!($actor->hasRole('admin') || $actor->hasRole('penilai'))) {
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

            if (!($actor->hasRole('admin') || $actor->hasRole('penilai'))) {
                abort(403, 'Anda tidak berhak menilai permohonan ini.');
            }

            if ($project->status !== Project::STATUS_IN_REVIEW) {
                throw ValidationException::withMessages([
                    'status' => 'Keputusan hanya dapat diberikan pada permohonan berstatus in_review.',
                ]);
            }

            if (!$actor->hasRole('admin') && $project->evaluator_id !== $actor->id) {
                abort(403, 'Permohonan ini sedang ditangani penilai lain.');
            }

            if (!in_array($decision, [Project::STATUS_APPROVED, Project::STATUS_REVISION, Project::STATUS_REJECTED], true)) {
                throw ValidationException::withMessages([
                    'decision' => 'Keputusan penilaian tidak valid.',
                ]);
            }

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
}
