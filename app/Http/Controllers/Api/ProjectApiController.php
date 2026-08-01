<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssessmentLog;
use App\Models\Notification;
use App\Models\Project;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectApiController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $query = Project::with(['documentType', 'applicant', 'evaluator']);

        if ($user->hasRole('pemohon')) {
            $query->where('applicant_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $projects = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $projects,
        ]);
    }

    public function show(Request $request, $id)
    {
        $project = Project::with(['documentType', 'applicant', 'evaluator', 'documents', 'assessmentLogs.user'])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $project,
        ]);
    }

    public function assess(Request $request, $id)
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user->hasRole('penilai') && !$user->hasRole('admin')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'decision' => 'required|in:approved,revision,rejected',
            'notes' => 'required|string|min:5',
        ]);

        $project = Project::findOrFail($id);

        DB::beginTransaction();
        try {
            $prevStatus = $project->status;
            $newStatus = $validated['decision'];
            $now = now();

            $project->update([
                'status' => $newStatus,
                'evaluator_id' => $user->id,
                'approved_at' => ($newStatus === 'approved') ? $now : $project->approved_at,
                'rejected_at' => ($newStatus === 'rejected') ? $now : $project->rejected_at,
            ]);

            AssessmentLog::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'action' => ($newStatus === 'approved') ? 'approve' : (($newStatus === 'revision') ? 'request_revision' : 'reject'),
                'previous_status' => $prevStatus,
                'new_status' => $newStatus,
                'notes' => $validated['notes'],
            ]);

            Notification::create([
                'user_id' => $project->applicant_id,
                'project_id' => $project->id,
                'title' => 'Pembaruan Status Permohonan: ' . strtoupper($newStatus),
                'message' => $validated['notes'],
                'type' => ($newStatus === 'approved') ? 'success' : (($newStatus === 'revision') ? 'warning' : 'danger'),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Penilaian permohonan berhasil diperbarui.',
                'data' => $project,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
