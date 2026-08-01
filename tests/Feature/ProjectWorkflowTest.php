<?php

namespace Tests\Feature;

use App\Models\DocumentType;
use App\Models\Project;
use App\Models\ProjectVerificationChecklist;
use App\Models\User;
use Database\Seeders\DocumentTypeSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\VerificationChecklistItemSeeder;
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
        $this->seed(VerificationChecklistItemSeeder::class);
    }

    public function test_full_document_approval_business_workflow()
    {
        Storage::fake('public');

        $pemohon = User::factory()->create([
            'email' => 'pemohon_workflow@example.com',
            'password' => bcrypt('password'),
        ]);
        $pemohon->assignRole('pemohon');

        $penilai = User::factory()->create([
            'email' => 'penilai_workflow@example.com',
            'password' => bcrypt('password'),
        ]);
        $penilai->assignRole('penilai');

        $docType = DocumentType::first();

        // 1. Pemohon creates a new Project submission
        $file = UploadedFile::fake()->create('berkas_amdal.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($pemohon, 'sanctum')->withHeaders(['Accept' => 'application/json'])->post('/api/v1/projects', [
            'title' => 'Permohonan AMDAL Test Workflow',
            'document_type_id' => $docType->id,
            'description' => 'Uji coba alur permohonan dokumen.',
            'document' => $file,
            'submit_action' => 'submit',
        ]);
        $response->assertCreated();

        $project = Project::where('title', 'Permohonan AMDAL Test Workflow')->first();
        $this->assertNotNull($project);
        $this->assertEquals(Project::STATUS_SUBMITTED, $project->status);

        // 2. Penilai starts review and requests revision
        $response = $this->actingAs($penilai, 'sanctum')->postJson("/api/v1/assessments/{$project->id}/start-review");
        $response->assertOk();

        $project->refresh();
        $this->assertEquals(Project::STATUS_IN_REVIEW, $project->status);

        $this->saveChecklist($penilai, $project, ProjectVerificationChecklist::STATUS_FAILED);

        $response = $this->actingAs($penilai, 'sanctum')->postJson("/api/v1/assessments/{$project->id}", [
            'decision' => 'revision',
            'notes' => 'Tolong lengkapi peta lokasi kegiatan.',
        ]);
        $response->assertOk();

        $project->refresh();
        $this->assertEquals(Project::STATUS_REVISION, $project->status);

        // 3. Pemohon updates & resubmits
        $newFile = UploadedFile::fake()->create('berkas_amdal_rev1.pdf', 1200, 'application/pdf');
        $response = $this->actingAs($pemohon, 'sanctum')->withHeaders(['Accept' => 'application/json'])->post("/api/v1/projects/{$project->id}", [
            'title' => 'Permohonan AMDAL Test Workflow (Revisi 1)',
            'document_type_id' => $docType->id,
            'description' => 'Peta lokasi sudah ditambahkan.',
            'document' => $newFile,
            'submit_action' => 'submit',
        ]);
        $response->assertOk();

        $project->refresh();
        $this->assertEquals(Project::STATUS_SUBMITTED, $project->status);

        // 4. Penilai starts review again and approves the project
        $this->actingAs($penilai, 'sanctum')->postJson("/api/v1/assessments/{$project->id}/start-review");
        $this->saveChecklist($penilai, $project->fresh(), ProjectVerificationChecklist::STATUS_PASSED);

        $response = $this->actingAs($penilai, 'sanctum')->postJson("/api/v1/assessments/{$project->id}", [
            'decision' => 'approved',
            'notes' => 'Dokumen lengkap dan memenuhi syarat.',
        ]);
        $response->assertOk();

        $project->refresh();
        $this->assertEquals(Project::STATUS_APPROVED, $project->status);
        $this->assertNotNull($project->approved_at);
    }

    private function saveChecklist(User $penilai, Project $project, string $requiredStatus): void
    {
        $items = $this->actingAs($penilai, 'sanctum')
            ->getJson("/api/v1/projects/{$project->id}/verification-checklists")
            ->json('data.items');

        $payload = collect($items)->map(fn (array $item) => [
            'checklist_item_id' => $item['checklist_item_id'],
            'status' => $item['item']['is_required'] ? $requiredStatus : ProjectVerificationChecklist::STATUS_NOT_APPLICABLE,
            'notes' => $item['item']['is_required'] ? 'Sudah diverifikasi.' : 'Tidak wajib.',
        ])->all();

        $this->actingAs($penilai, 'sanctum')
            ->putJson("/api/v1/projects/{$project->id}/verification-checklists", ['items' => $payload])
            ->assertOk();
    }
}
