<?php

namespace App\Http\Requests;

use App\Models\ProjectVerificationChecklist;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUpdateVerificationChecklistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') || $this->user()?->hasRole('penilai');
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.checklist_item_id' => ['required', 'integer', 'exists:verification_checklist_items,id'],
            'items.*.status' => ['required', 'string', Rule::in(ProjectVerificationChecklist::STATUSES)],
            'items.*.notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
