<?php

namespace App\Jobs;

use App\Models\ProjectDocument;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessProjectDocumentUpload implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(
        public int $projectId,
        public int $uploadedBy,
        public string $temporaryPath,
        public string $documentName,
        public string $fileName,
        public int $fileSize,
        public ?string $mimeType,
        public ?int $version = null,
    ) {}

    public function handle(): void
    {
        if (! Storage::disk('local')->exists($this->temporaryPath)) {
            Log::warning('Queued project document upload file is missing.', [
                'project_id' => $this->projectId,
                'temporary_path' => $this->temporaryPath,
            ]);

            return;
        }

        $finalPath = 'project_documents/'.$this->projectId.'/'.$this->fileName;

        Storage::disk('public')->put(
            $finalPath,
            Storage::disk('local')->get($this->temporaryPath),
        );

        Storage::disk('local')->delete($this->temporaryPath);

        ProjectDocument::firstOrCreate(
            [
                'project_id' => $this->projectId,
                'file_name' => $this->fileName,
            ],
            [
                'document_name' => $this->documentName,
                'file_path' => $finalPath,
                'file_size' => $this->fileSize,
                'mime_type' => $this->mimeType,
                'version' => $this->version ?? ((ProjectDocument::where('project_id', $this->projectId)->max('version') ?? 0) + 1),
                'uploaded_by' => $this->uploadedBy,
            ],
        );
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('Failed to process queued project document upload.', [
            'project_id' => $this->projectId,
            'uploaded_by' => $this->uploadedBy,
            'temporary_path' => $this->temporaryPath,
            'error' => $exception?->getMessage(),
        ]);
    }
}
