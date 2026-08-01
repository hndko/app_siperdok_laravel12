<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VerificationChecklistResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'checklist_item_id' => $this->checklist_item_id,
            'status' => $this->status,
            'notes' => $this->notes,
            'checked_at' => $this->checked_at,
            'item' => [
                'id' => $this->item?->id,
                'name' => $this->item?->name,
                'description' => $this->item?->description,
                'is_required' => (bool) $this->item?->is_required,
                'is_active' => (bool) $this->item?->is_active,
                'sort_order' => $this->item?->sort_order,
            ],
            'reviewer' => new UserSummaryResource($this->whenLoaded('reviewer')),
        ];
    }
}
