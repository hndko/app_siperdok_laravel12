<?php

namespace App\Http\Controllers\Penilai;

use App\Http\Controllers\Controller;
use App\Models\AssessmentLog;
use App\Models\DocumentType;
use App\Models\Notification;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Eager load applicant, documentType, and evaluator to prevent N+1 queries
        $query = Project::with(['applicant', 'documentType', 'evaluator'])
            ->where('status', '!=', Project::STATUS_DRAFT);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('project_number', 'like', "%{$search}%")
                  ->orWhereHas('applicant', function ($a) use ($search) {
                      $a->where('name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('document_type_id')) {
            $query->where('document_type_id', $request->document_type_id);
        }

        $projects = $query->orderBy('updated_at', 'desc')->paginate(15)->withQueryString();
        $documentTypes = DocumentType::where('is_active', true)->get();

        return view('assessments.index', compact('projects', 'documentTypes'));
    }

    public function review($id)
    {
        // Eager load documentType, applicant, evaluator, documents with uploader, and assessmentLogs with user
        $project = Project::with(['documentType', 'applicant', 'evaluator', 'documents.uploader', 'assessmentLogs.user'])
            ->findOrFail($id);

        return view('assessments.review', compact('project'));
    }

    public function processDecision(Request $request, $id)
    {
        /** @var User $user */
        $user = Auth::user();

        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'decision' => ['required', 'in:approved,revision,rejected'],
            'notes' => ['required', 'string', 'min:5'],
        ]);

        DB::beginTransaction();
        try {
            $prevStatus = $project->status;
            $newStatus = $validated['decision'];
            $now = now();

            $updateData = [
                'status' => $newStatus,
                'evaluator_id' => $user->id,
            ];

            if ($newStatus === Project::STATUS_APPROVED) {
                $updateData['approved_at'] = $now;
                $action = 'approve';
                $title = 'Permohonan Dokumen DISETUJUI!';
                $msgNotification = 'Selamat! Permohonan dokumen ' . $project->project_number . ' telah DISETUJUI oleh Penilai. Dokumen kelayakan kini terbit.';
                $notifType = 'success';
            } elseif ($newStatus === Project::STATUS_REVISION) {
                $action = 'request_revision';
                $title = 'Permohonan Dokumen Memerlukan REVISI';
                $msgNotification = 'Permohonan dokumen ' . $project->project_number . ' memerlukan perbaikan. Catatan Penilai: ' . $validated['notes'];
                $notifType = 'warning';
            } else {
                $updateData['rejected_at'] = $now;
                $action = 'reject';
                $title = 'Permohonan Dokumen DITOLAK';
                $msgNotification = 'Mohon maaf, permohonan dokumen ' . $project->project_number . ' DITOLAK. Alasan: ' . $validated['notes'];
                $notifType = 'danger';
            }

            $project->update($updateData);

            // Create Assessment Audit Log
            AssessmentLog::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'action' => $action,
                'previous_status' => $prevStatus,
                'new_status' => $newStatus,
                'notes' => $validated['notes'],
            ]);

            // Notify Applicant
            Notification::create([
                'user_id' => $project->applicant_id,
                'project_id' => $project->id,
                'title' => $title,
                'message' => $msgNotification,
                'type' => $notifType,
            ]);

            DB::commit();

            return redirect()->route('assessments.review', $project->id)
                ->with('success', 'Keputusan penilaian berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses keputusan: ' . $e->getMessage());
        }
    }

    public function history(Request $request)
    {
        // Eager load nested project relationships (documentType, applicant, evaluator) and user
        $query = AssessmentLog::with(['project.documentType', 'project.applicant', 'project.evaluator', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('project', function ($q) use ($search) {
                $q->where('project_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('assessments.history', compact('logs'));
    }
}
