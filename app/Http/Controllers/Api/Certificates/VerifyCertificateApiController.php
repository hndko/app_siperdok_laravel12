<?php

namespace App\Http\Controllers\Api\Certificates;

use App\Http\Controllers\Controller;
use App\Models\Project;

class VerifyCertificateApiController extends Controller
{
    public function __invoke(string $certificateNumber)
    {
        $project = Project::query()
            ->select(['certificate_number', 'title', 'status', 'certificate_issued_at'])
            ->where('certificate_number', $certificateNumber)
            ->where('status', Project::STATUS_CERTIFICATE_ISSUED)
            ->first();

        if (! $project) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'valid' => false,
                    'certificate_number' => $certificateNumber,
                ],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'valid' => true,
                'certificate_number' => $project->certificate_number,
                'title' => $project->title,
                'issued_at' => $project->certificate_issued_at,
            ],
        ]);
    }
}
