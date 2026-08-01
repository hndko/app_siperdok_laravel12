<?php

namespace App\Http\Controllers\Pemohon;

use App\Http\Controllers\Controller;
use App\Models\AssessmentLog;
use App\Models\DocumentType;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $query = Project::with(['applicant', 'documentType', 'evaluator'])
            ->where('applicant_id', $user->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('project_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('document_type_id')) {
            $query->where('document_type_id', $request->document_type_id);
        }

        $projects = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $documentTypes = DocumentType::where('is_active', true)->get();

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'documentTypes' => $documentTypes,
            'filters' => $request->only(['search', 'status', 'document_type_id']),
        ]);
    }

    public function create()
    {
        $documentTypes = DocumentType::where('is_active', true)->get();
        return Inertia::render('Projects/Create', compact('documentTypes'));
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document_type_id' => ['required', 'exists:document_types,id'],
            'description' => ['nullable', 'string'],
            'document' => ['required', 'file', 'mimes:pdf,docx,doc,png,jpg,jpeg', 'max:10240'],
            'submit_action' => ['required', 'in:draft,submit'],
        ]);

        DB::beginTransaction();
        try {
            $projectNum = 'PRJ-' . date('Ym') . '-' . str_pad(Project::max('id') + 1, 5, '0', STR_PAD_LEFT);
            $status = ($validated['submit_action'] === 'submit') ? Project::STATUS_SUBMITTED : Project::STATUS_DRAFT;

            $project = Project::create([
                'project_number' => $projectNum,
                'title' => $validated['title'],
                'applicant_id' => $user->id,
                'document_type_id' => $validated['document_type_id'],
                'status' => $status,
                'description' => $validated['description'] ?? null,
                'submitted_at' => ($status === Project::STATUS_SUBMITTED) ? now() : null,
            ]);

            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('project_documents/' . $project->id, $filename, 'public');

                ProjectDocument::create([
                    'project_id' => $project->id,
                    'document_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_name' => $filename,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getClientMimeType(),
                    'version' => 1,
                    'uploaded_by' => $user->id,
                ]);
            }

            AssessmentLog::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'action' => ($status === Project::STATUS_SUBMITTED) ? 'submit' : 'create_draft',
                'previous_status' => null,
                'new_status' => $status,
                'notes' => ($status === Project::STATUS_SUBMITTED) 
                    ? 'Pengajuan dokumen kelayakan dibuat dan dikirimkan untuk proses penilaian.' 
                    : 'Draft pengajuan dokumen kelayakan disimpan.',
            ]);

            DB::commit();

            $msg = ($status === Project::STATUS_SUBMITTED)
                ? 'Permohonan dokumen berhasil dikirimkan untuk Penilaian!'
                : 'Draft permohonan berhasil disimpan.';

            return redirect()->route('projects.show', $project->id)->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan permohonan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        /** @var User $user */
        $user = Auth::user();

        $project = Project::with(['documentType', 'applicant', 'evaluator', 'documents.uploader', 'assessmentLogs.user'])
            ->findOrFail($id);

        if (!$user->hasRole('admin') && !$user->hasRole('penilai') && $project->applicant_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        return Inertia::render('Projects/Show', compact('project'));
    }

    public function edit($id)
    {
        /** @var User $user */
        $user = Auth::user();

        $project = Project::with(['documentType', 'documents.uploader', 'assessmentLogs.user'])->findOrFail($id);

        if ($project->applicant_id !== $user->id && !$user->hasRole('admin')) {
            abort(403, 'Akses ditolak.');
        }

        if (!in_array($project->status, [Project::STATUS_DRAFT, Project::STATUS_REVISION])) {
            return redirect()->route('projects.show', $id)
                ->with('error', 'Permohonan dokumen yang sudah dikirim atau dinilai tidak dapat diubah.');
        }

        $documentTypes = DocumentType::where('is_active', true)->get();

        return Inertia::render('Projects/Edit', compact('project', 'documentTypes'));
    }

    public function update(Request $request, $id)
    {
        /** @var User $user */
        $user = Auth::user();

        $project = Project::findOrFail($id);

        if ($project->applicant_id !== $user->id && !$user->hasRole('admin')) {
            abort(403, 'Akses ditolak.');
        }

        if (!in_array($project->status, [Project::STATUS_DRAFT, Project::STATUS_REVISION])) {
            return redirect()->route('projects.show', $id)
                ->with('error', 'Permohonan dokumen tidak dapat diubah.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document_type_id' => ['required', 'exists:document_types,id'],
            'description' => ['nullable', 'string'],
            'document' => ['nullable', 'file', 'mimes:pdf,docx,doc,png,jpg,jpeg', 'max:10240'],
            'submit_action' => ['required', 'in:draft,submit'],
        ]);

        DB::beginTransaction();
        try {
            $prevStatus = $project->status;
            $newStatus = ($validated['submit_action'] === 'submit') ? Project::STATUS_SUBMITTED : $prevStatus;

            $project->update([
                'title' => $validated['title'],
                'document_type_id' => $validated['document_type_id'],
                'description' => $validated['description'] ?? null,
                'status' => $newStatus,
                'submitted_at' => ($newStatus === Project::STATUS_SUBMITTED) ? now() : $project->submitted_at,
            ]);

            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('project_documents/' . $project->id, $filename, 'public');

                $latestVersion = ProjectDocument::where('project_id', $project->id)->max('version') ?? 0;

                ProjectDocument::create([
                    'project_id' => $project->id,
                    'document_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_name' => $filename,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getClientMimeType(),
                    'version' => $latestVersion + 1,
                    'uploaded_by' => $user->id,
                ]);
            }

            $action = ($prevStatus === Project::STATUS_REVISION && $newStatus === Project::STATUS_SUBMITTED) 
                ? 'resubmit' 
                : (($newStatus === Project::STATUS_SUBMITTED) ? 'submit' : 'update_draft');

            AssessmentLog::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'action' => $action,
                'previous_status' => $prevStatus,
                'new_status' => $newStatus,
                'notes' => ($action === 'resubmit') 
                    ? 'Pemohon telah mengunggah perbaikan dokumen dan mengirimkan ulang permohonan.' 
                    : 'Pembaruan data permohonan.',
            ]);

            DB::commit();

            return redirect()->route('projects.show', $project->id)->with('success', 'Permohonan dokumen berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui permohonan: ' . $e->getMessage());
        }
    }
}
