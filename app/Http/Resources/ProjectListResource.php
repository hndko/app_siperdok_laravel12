<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_number' => $this->project_number,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'submitted_at' => $this->submitted_at,
            'approved_at' => $this->approved_at,
            'rejected_at' => $this->rejected_at,
            'certificate_number' => $this->certificate_number,
            'certificate_issued_at' => $this->certificate_issued_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'document_type' => new DocumentTypeResource($this->whenLoaded('documentType')),
            'applicant' => new UserSummaryResource($this->whenLoaded('applicant')),
            'evaluator' => new UserSummaryResource($this->whenLoaded('evaluator')),
        ];
    }
}
