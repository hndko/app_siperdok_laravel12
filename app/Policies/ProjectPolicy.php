<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('pemohon')) {
            return $project->applicant_id === $user->id;
        }

        if ($user->hasRole('penilai')) {
            return in_array($project->status, Project::REVIEWABLE_STATUSES, true);
        }

        return false;
    }

    public function startReview(User $user, Project $project): bool
    {
        return ($user->hasRole('admin') || $user->hasRole('penilai'))
            && $project->status === Project::STATUS_SUBMITTED;
    }

    public function assess(User $user, Project $project): bool
    {
        if (! $user->hasRole('admin') && ! $user->hasRole('penilai')) {
            return false;
        }

        if ($project->status !== Project::STATUS_IN_REVIEW) {
            return false;
        }

        return $user->hasRole('admin') || $project->evaluator_id === $user->id;
    }

    public function delete(User $user, Project $project): bool
    {
        if ($project->status !== Project::STATUS_DRAFT) {
            return false;
        }

        return $user->hasRole('admin') || $project->applicant_id === $user->id;
    }

    public function export(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('penilai');
    }

    public function issueCertificate(User $user, Project $project): bool
    {
        return ($user->hasRole('admin') || $user->hasRole('penilai'))
            && $project->status === Project::STATUS_APPROVED;
    }
}
