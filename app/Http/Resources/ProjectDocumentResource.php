<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_name' => $this->document_name,
            'file_name' => $this->file_name,
            'file_size' => $this->file_size,
            'mime_type' => $this->mime_type,
            'version' => $this->version,
            'created_at' => $this->created_at,
        ];
    }
}
