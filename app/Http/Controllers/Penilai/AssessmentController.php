<?php

namespace App\Http\Controllers\Penilai;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AssessmentController extends Controller
{
    public function __construct(private readonly ProjectWorkflowService $workflow) {}

    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $query = Project::with(['applicant', 'documentType', 'evaluator'])
            ->visibleTo($user);

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

        return Inertia::render('Assessments/Index', [
            'projects' => $projects,
            'documentTypes' => $documentTypes,
            'filters' => $request->only(['search', 'status', 'document_type_id']),
        ]);
    }

    public function review($id)
    {
        $project = Project::with(['documentType', 'applicant', 'evaluator', 'documents.uploader', 'assessmentLogs.user'])
            ->findOrFail($id);

        $this->authorize('view', $project);

        return Inertia::render('Assessments/Review', [
            'project' => $project,
            'canStartReview' => Auth::user()->can('startReview', $project),
            'canAssess' => Auth::user()->can('assess', $project),
        ]);
    }

    public function startReview(Request $request, $id)
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->workflow->startReview((int) $id, Auth::user(), $validated['notes'] ?? null);

            return redirect()->route('assessments.review', $id)
                ->with('success', 'Review permohonan berhasil dimulai.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Failed to start project review.', ['project_id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Gagal memulai review permohonan.');
        }
    }

    public function processDecision(Request $request, $id)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'decision' => ['required', 'in:approved,revision,rejected'],
            'notes' => ['required', 'string', 'min:5'],
        ]);

        try {
            $project = $this->workflow->decide((int) $id, $user, $validated['decision'], $validated['notes']);

            return redirect()->route('assessments.review', $project->id)
                ->with('success', 'Keputusan penilaian berhasil diperbarui.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Failed to process project decision.', ['project_id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Gagal memproses keputusan.');
        }
    }

    public function history(Request $request)
    {
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

        return Inertia::render('Assessments/History', [
            'logs' => $logs,
            'filters' => $request->only(['search', 'action']),
        ]);
    }
}
