<?php

namespace Tests\Feature;

use App\Models\DocumentType;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\DocumentTypeSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(DocumentTypeSeeder::class);
    }

    public function test_full_document_approval_business_workflow()
    {
        Storage::fake('public');

        $pemohon = User::factory()->create([
            'email' => 'pemohon_workflow@siperdok.go.id',
            'password' => bcrypt('password123'),
        ]);
        $pemohon->assignRole('pemohon');

        $penilai = User::factory()->create([
            'email' => 'penilai_workflow@siperdok.go.id',
            'password' => bcrypt('password123'),
        ]);
        $penilai->assignRole('penilai');

        $docType = DocumentType::first();

        // 1. Pemohon creates a new Project submission
        $file = UploadedFile::fake()->create('berkas_amdal.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($pemohon)->post('/projects', [
            'title' => 'Permohonan AMDAL Test Workflow',
            'document_type_id' => $docType->id,
            'description' => 'Uji coba alur permohonan dokumen.',
            'document' => $file,
            'submit_action' => 'submit',
        ]);

        $project = Project::where('title', 'Permohonan AMDAL Test Workflow')->first();
        $this->assertNotNull($project);
        $this->assertEquals(Project::STATUS_SUBMITTED, $project->status);

        // 2. Penilai reviews and requests revision
        $response = $this->actingAs($penilai)->post("/assessments/{$project->id}/process", [
            'decision' => 'revision',
            'notes' => 'Tolong lengkapi peta lokasi kegiatan.',
        ]);

        $project->refresh();
        $this->assertEquals(Project::STATUS_REVISION, $project->status);

        // 3. Pemohon updates & resubmits
        $newFile = UploadedFile::fake()->create('berkas_amdal_rev1.pdf', 1200, 'application/pdf');
        $response = $this->actingAs($pemohon)->put("/projects/{$project->id}", [
            'title' => 'Permohonan AMDAL Test Workflow (Revisi 1)',
            'document_type_id' => $docType->id,
            'description' => 'Peta lokasi sudah ditambahkan.',
            'document' => $newFile,
            'submit_action' => 'submit',
        ]);

        $project->refresh();
        $this->assertEquals(Project::STATUS_SUBMITTED, $project->status);

        // 4. Penilai approves the project
        $response = $this->actingAs($penilai)->post("/assessments/{$project->id}/process", [
            'decision' => 'approved',
            'notes' => 'Dokumen lengkap dan memenuhi syarat.',
        ]);

        $project->refresh();
        $this->assertEquals(Project::STATUS_APPROVED, $project->status);
        $this->assertNotNull($project->approved_at);
    }
}
