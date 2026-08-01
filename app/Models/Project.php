<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_number',
        'title',
        'applicant_id',
        'evaluator_id',
        'document_type_id',
        'status',
        'description',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'certificate_number',
        'certificate_issued_at',
        'certificate_issued_by',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'certificate_issued_at' => 'datetime',
    ];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_IN_REVIEW = 'in_review';

    public const STATUS_REVISION = 'revision';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CERTIFICATE_ISSUED = 'certificate_issued';

    public const REVIEWABLE_STATUSES = [
        self::STATUS_SUBMITTED,
        self::STATUS_IN_REVIEW,
        self::STATUS_REVISION,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_CERTIFICATE_ISSUED,
    ];

    public const TERMINAL_STATUSES = [
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_CERTIFICATE_ISSUED,
    ];

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasRole('admin')) {
            return $query;
        }

        if ($user->hasRole('pemohon')) {
            return $query->where('applicant_id', $user->id);
        }

        if ($user->hasRole('penilai')) {
            return $query->whereIn('status', self::REVIEWABLE_STATUSES);
        }

        return $query->whereRaw('1 = 0');
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $q, string $search) {
                $q->where(function (Builder $inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('project_number', 'like', "%{$search}%")
                        ->orWhereHas('applicant', function (Builder $applicant) use ($search) {
                            $applicant->where('name', 'like', "%{$search}%")
                                ->orWhere('company_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['document_type_id'] ?? null, fn (Builder $q, mixed $id) => $q->where('document_type_id', $id))
            ->when($filters['applicant_id'] ?? null, fn (Builder $q, mixed $id) => $q->where('applicant_id', $id))
            ->when($filters['evaluator_id'] ?? null, fn (Builder $q, mixed $id) => $q->where('evaluator_id', $id))
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $q, string $date) => $q->whereDate('created_at', '<=', $date));
    }

    public function applicant()
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function documents()
    {
        return $this->hasMany(ProjectDocument::class);
    }

    public function assessmentLogs()
    {
        return $this->hasMany(AssessmentLog::class)->orderBy('created_at', 'desc');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function verificationChecklists()
    {
        return $this->hasMany(ProjectVerificationChecklist::class);
    }

    public function certificateIssuer()
    {
        return $this->belongsTo(User::class, 'certificate_issued_by');
    }
}
