<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'action' => $this->action,
            'previous_status' => $this->previous_status,
            'new_status' => $this->new_status,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'user' => new UserSummaryResource($this->whenLoaded('user')),
        ];
    }
}
