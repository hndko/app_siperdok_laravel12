<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectVerificationChecklist extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PASSED = 'passed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_NOT_APPLICABLE = 'not_applicable';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PASSED,
        self::STATUS_FAILED,
        self::STATUS_NOT_APPLICABLE,
    ];

    protected $fillable = [
        'project_id',
        'checklist_item_id',
        'reviewer_id',
        'status',
        'notes',
        'checked_at',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function item()
    {
        return $this->belongsTo(VerificationChecklistItem::class, 'checklist_item_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
