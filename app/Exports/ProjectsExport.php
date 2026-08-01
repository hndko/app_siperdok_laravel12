<?php

namespace App\Exports;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProjectsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private readonly User $user,
        private readonly array $filters = [],
    ) {}

    public function query(): Builder
    {
        return Project::query()
            ->select([
                'id',
                'project_number',
                'title',
                'applicant_id',
                'evaluator_id',
                'document_type_id',
                'status',
                'submitted_at',
                'approved_at',
                'rejected_at',
                'created_at',
            ])
            ->with([
                'documentType:id,code,name',
                'applicant:id,name,company_name',
                'evaluator:id,name',
            ])
            ->visibleTo($this->user)
            ->filter($this->filters)
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nomor Permohonan',
            'Judul Project',
            'Jenis Dokumen',
            'Pemohon / Perusahaan',
            'Penilai',
            'Status',
            'Tanggal Pengajuan',
            'Tanggal Keputusan',
            'Tanggal Dibuat',
        ];
    }

    public function map($project): array
    {
        return [
            $project->id,
            $project->project_number,
            $project->title,
            $project->documentType ? $project->documentType->code.' - '.$project->documentType->name : '-',
            $project->applicant ? $project->applicant->name.' ('.($project->applicant->company_name ?: '-').')' : '-',
            $project->evaluator?->name ?: 'Belum Ditugaskan',
            strtoupper($project->status),
            $project->submitted_at?->format('Y-m-d H:i') ?: '-',
            $project->approved_at?->format('Y-m-d H:i') ?: ($project->rejected_at?->format('Y-m-d H:i') ?: '-'),
            $project->created_at?->format('Y-m-d H:i') ?: '-',
        ];
    }
}
