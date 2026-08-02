<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'applicant_id' => $this->applicant_id,
            'evaluator_id' => $this->evaluator_id,
            'document_type_id' => $this->document_type_id,
            'project_number' => $this->project_number,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'submitted_at' => $this->submitted_at,
            'approved_at' => $this->approved_at,
            'rejected_at' => $this->rejected_at,
            'certificate_number' => $this->certificate_number,
            'certificate_issued_at' => $this->certificate_issued_at,
            'certificate_issued_by' => $this->certificate_issued_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'document_type' => new DocumentTypeResource($this->whenLoaded('documentType')),
            'applicant' => new UserSummaryResource($this->whenLoaded('applicant')),
            'evaluator' => new UserSummaryResource($this->whenLoaded('evaluator')),
            'certificate_issuer' => new UserSummaryResource($this->whenLoaded('certificateIssuer')),
            'documents' => ProjectDocumentResource::collection($this->whenLoaded('documents')),
            'assessment_logs' => AssessmentLogResource::collection($this->whenLoaded('assessmentLogs')),
            'verification_checklists' => VerificationChecklistResource::collection($this->whenLoaded('verificationChecklists')),
            'permissions' => [
                'can_issue_certificate' => $request->user()?->can('issueCertificate', $this->resource) ?? false,
            ],
        ];
    }
}
