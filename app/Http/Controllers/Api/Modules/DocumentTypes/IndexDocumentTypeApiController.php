<?php

namespace App\Http\Controllers\Api\Modules\DocumentTypes;

use App\Http\Controllers\Api\Modules\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentTypeResource;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class IndexDocumentTypeApiController extends Controller
{
    use RespondsWithApi;

    public function __invoke(Request $request)
    {
        $includeInactive = $request->boolean('include_inactive') && $request->user()?->hasRole('admin');
        $cacheKey = $includeInactive ? 'document-types:all:v1' : 'document-types:active:v1';

        $documentTypes = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($includeInactive) {
            return DocumentType::query()
                ->select(['id', 'code', 'name', 'description', 'required_files', 'is_active'])
                ->when(! $includeInactive, fn ($query) => $query->where('is_active', true))
                ->orderBy('code')
                ->get();
        });

        return $this->success(DocumentTypeResource::collection($documentTypes));
    }
}
