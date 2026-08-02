<?php

namespace App\Http\Controllers\Api\Modules\Projects;

use App\Http\Controllers\Api\Modules\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;

class ShowProjectApiController extends Controller
{
    use RespondsWithApi;

    public function __invoke(Request $request, int $id)
    {
        $project = Project::query()
            ->with([
                'documentType:id,code,name,description,required_files,is_active',
                'applicant:id,name,email,phone,nip_nik,company_name',
                'evaluator:id,name,email,phone,nip_nik,company_name',
                'certificateIssuer:id,name,email,phone,nip_nik,company_name',
                'documents:id,project_id,uploaded_by,document_name,file_name,file_path,file_size,mime_type,version,created_at',
                'documents.uploader:id,name,email,phone,nip_nik,company_name',
                'assessmentLogs:id,project_id,user_id,action,previous_status,new_status,notes,created_at',
                'assessmentLogs.user:id,name,email,phone,nip_nik,company_name',
                'verificationChecklists:id,project_id,checklist_item_id,reviewer_id,status,notes,checked_at',
                'verificationChecklists.item:id,name,description,is_required,is_active,sort_order',
                'verificationChecklists.reviewer:id,name,email,phone,nip_nik,company_name',
            ])
            ->findOrFail($id);

        $this->authorize('view', $project);

        return $this->success(new ProjectResource($project));
    }
}
