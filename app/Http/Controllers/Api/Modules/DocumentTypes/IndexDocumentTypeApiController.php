<?php

namespace App\Http\Controllers\Api\Modules\DocumentTypes;

use App\Http\Controllers\Api\Modules\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentTypeResource;
use App\Models\DocumentType;
use Illuminate\Http\Request;

class IndexDocumentTypeApiController extends Controller
{
    use RespondsWithApi;

    public function __invoke(Request $request)
    {
        $query = DocumentType::query()
            ->withCount('projects')
            ->orderBy('code');

        if (!$request->boolean('include_inactive')) {
            $query->where('is_active', true);
        }

        return $this->success(DocumentTypeResource::collection($query->get()));
    }
}
