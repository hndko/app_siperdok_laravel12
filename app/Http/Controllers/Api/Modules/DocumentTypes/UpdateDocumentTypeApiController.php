<?php

namespace App\Http\Controllers\Api\Modules\DocumentTypes;

use App\Http\Controllers\Api\Modules\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentTypeResource;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class UpdateDocumentTypeApiController extends Controller
{
    use RespondsWithApi;

    public function __invoke(Request $request, int $id)
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        $documentType = DocumentType::query()->findOrFail($id);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('document_types', 'code')->ignore($documentType->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'required_files_text' => ['nullable', 'string'],
            'is_active' => ['required', Rule::in([true, false, 1, 0, '1', '0', 'true', 'false'])],
        ]);

        $documentType->update([
            'code' => strtoupper(trim($validated['code'])),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'required_files' => $this->parseRequiredFiles($validated['required_files_text'] ?? ''),
            'is_active' => filter_var($validated['is_active'], FILTER_VALIDATE_BOOLEAN),
        ]);

        Cache::forget('document-types:all:v1');
        Cache::forget('document-types:active:v1');

        return $this->success(
            new DocumentTypeResource($documentType->fresh()->loadCount('projects')),
            'Jenis dokumen berhasil diperbarui.',
        );
    }

    private function parseRequiredFiles(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }
}
