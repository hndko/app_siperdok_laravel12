<?php

namespace App\Http\Controllers\Api\Modules\DocumentTypes;

use App\Http\Controllers\Api\Modules\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentTypeResource;
use App\Models\DocumentType;

class IndexDocumentTypeApiController extends Controller
{
    use RespondsWithApi;

    public function __invoke()
    {
        $documentTypes = DocumentType::where('is_active', true)->get();

        return $this->success(DocumentTypeResource::collection($documentTypes));
    }
}
