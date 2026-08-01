<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_required',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function projectChecklists()
    {
        return $this->hasMany(ProjectVerificationChecklist::class, 'checklist_item_id');
    }
}
