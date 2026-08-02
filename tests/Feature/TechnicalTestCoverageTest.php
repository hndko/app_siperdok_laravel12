<?php

namespace Tests\Feature;

use App\Jobs\CreateProjectStatusNotification;
use App\Models\AssessmentLog;
use App\Models\DocumentType;
use App\Models\Notification;
use App\Models\Project;
use App\Models\ProjectVerificationChecklist;
use App\Models\User;
use App\Models\VerificationChecklistItem;
use Database\Seeders\DocumentTypeSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\VerificationChecklistItemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TechnicalTestCoverageTest extends TestCase
{
    use RefreshDatabase;

    private DocumentType $documentType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(DocumentTypeSeeder::class);
        $this->seed(VerificationChecklistItemSeeder::class);
        $this->documentType = DocumentType::first();
    }

    public function test_pemohon_can_view_own_project_via_api(): void
    {
        $pemohon = $this->userWithRole('pemohon');
        $project = $this->projectFor($pemohon, Project::STATUS_SUBMITTED);

        $response = $this->actingAs($pemohon, 'sanctum')->getJson("/api/v1/projects/{$project->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $project->id);
    }

    public function test_pemohon_cannot_view_another_users_project_via_api(): void
    {
        $pemohon = $this->userWithRole('pemohon');
        $otherPemohon = $this->userWithRole('pemohon');
        $project = $this->projectFor($otherPemohon, Project::STATUS_SUBMITTED);

        $response = $this->actingAs($pemohon, 'sanctum')->getJson("/api/v1/projects/{$project->id}");

        $response->assertForbidden();
    }

    public function test_pemohon_cannot_view_another_users_history_via_api(): void
    {
        $pemohon = $this->userWithRole('pemohon');
        $otherPemohon = $this->userWithRole('pemohon');
        $project = $this->projectFor($otherPemohon, Project::STATUS_SUBMITTED);

        AssessmentLog::create([
            'project_id' => $project->id,
            'user_id' => $otherPemohon->id,
            'action' => 'submit',
            'previous_status' => null,
            'new_status' => Project::STATUS_SUBMITTED,
            'notes' => 'Private log.',
        ]);

        $response = $this->actingAs($pemohon, 'sanctum')->getJson('/api/v1/assessments/history');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_penilai_can_start_review(): void
    {
        Queue::fake();

        $pemohon = $this->userWithRole('pemohon');
        $penilai = $this->userWithRole('penilai');
        $project = $this->projectFor($pemohon, Project::STATUS_SUBMITTED);

        $response = $this->actingAs($penilai, 'sanctum')->postJson("/api/v1/assessments/{$project->id}/start-review");

        $response->assertOk();
        $project->refresh();

        $this->assertSame(Project::STATUS_IN_REVIEW, $project->status);
        $this->assertSame($penilai->id, $project->evaluator_id);
        $this->assertDatabaseHas('assessment_logs', [
            'project_id' => $project->id,
            'action' => 'start_review',
            'previous_status' => Project::STATUS_SUBMITTED,
            'new_status' => Project::STATUS_IN_REVIEW,
        ]);
        Queue::assertPushed(CreateProjectStatusNotification::class);
    }

    public function test_unauthorized_user_cannot_start_review(): void
    {
        $pemohon = $this->userWithRole('pemohon');
        $project = $this->projectFor($pemohon, Project::STATUS_SUBMITTED);

        $response = $this->actingAs($pemohon, 'sanctum')->postJson("/api/v1/assessments/{$project->id}/start-review");

        $response->assertForbidden();
        $this->assertSame(Project::STATUS_SUBMITTED, $project->fresh()->status);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $pemohon = $this->userWithRole('pemohon');
        $penilai = $this->userWithRole('penilai');
        $project = $this->projectFor($pemohon, Project::STATUS_SUBMITTED);

        $response = $this->actingAs($penilai, 'sanctum')->postJson("/api/v1/assessments/{$project->id}", [
            'decision' => 'approved',
            'notes' => 'Langsung approve tanpa review.',
        ]);

        $response->assertUnprocessable();
        $this->assertSame(Project::STATUS_SUBMITTED, $project->fresh()->status);
    }

    public function test_project_delete_follows_business_rules(): void
    {
        $pemohon = $this->userWithRole('pemohon');
        $draft = $this->projectFor($pemohon, Project::STATUS_DRAFT);
        $submitted = $this->projectFor($pemohon, Project::STATUS_SUBMITTED);

        $this->actingAs($pemohon, 'sanctum')->deleteJson("/api/v1/projects/{$submitted->id}")->assertForbidden();
        $this->actingAs($pemohon, 'sanctum')->deleteJson("/api/v1/projects/{$draft->id}")->assertOk();

        $this->assertDatabaseMissing('projects', ['id' => $draft->id]);
        $this->assertDatabaseHas('projects', ['id' => $submitted->id]);
    }

    public function test_excel_export_can_be_downloaded_by_penilai(): void
    {
        $pemohon = $this->userWithRole('pemohon');
        $penilai = $this->userWithRole('penilai');
        $this->projectFor($pemohon, Project::STATUS_SUBMITTED);

        $response = $this->actingAs($penilai, 'sanctum')->get('/api/v1/exports/projects/xlsx?status=submitted');

        $response->assertOk();
        $this->assertStringContainsString('spreadsheetml', $response->headers->get('content-type'));
    }

    public function test_project_index_limits_pagination_and_uses_list_payload(): void
    {
        $pemohon = $this->userWithRole('pemohon');
        $this->projectFor($pemohon, Project::STATUS_SUBMITTED);

        $response = $this->actingAs($pemohon, 'sanctum')
            ->getJson('/api/v1/projects?per_page=100');

        $response->assertOk()
            ->assertJsonPath('meta.per_page', 100)
            ->assertJsonMissingPath('data.0.documents')
            ->assertJsonMissingPath('data.0.assessment_logs')
            ->assertJsonMissingPath('data.0.verification_checklists')
            ->assertJsonMissingPath('data.0.applicant.password');
    }

    public function test_project_index_rejects_unbounded_per_page(): void
    {
        $pemohon = $this->userWithRole('pemohon');

        $this->actingAs($pemohon, 'sanctum')
            ->getJson('/api/v1/projects?per_page=101')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('per_page');
    }

    public function test_dashboard_query_count_is_bounded(): void
    {
        Cache::flush();

        $pemohon = $this->userWithRole('pemohon');
        $this->projectFor($pemohon, Project::STATUS_SUBMITTED);
        $this->projectFor($pemohon, Project::STATUS_APPROVED);

        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        $this->actingAs($pemohon, 'sanctum')
            ->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('data.total_projects', 2);

        $this->assertLessThanOrEqual(12, $queryCount, 'Dashboard menjalankan terlalu banyak query untuk statistik ringkas.');
    }

    public function test_penilai_can_save_verification_checklist_without_duplicates(): void
    {
        $pemohon = $this->userWithRole('pemohon');
        $penilai = $this->userWithRole('penilai');
        $project = $this->projectFor($pemohon, Project::STATUS_SUBMITTED);

        $this->actingAs($penilai, 'sanctum')->postJson("/api/v1/assessments/{$project->id}/start-review")->assertOk();
        $payload = $this->checklistPayload(ProjectVerificationChecklist::STATUS_PASSED);

        $this->actingAs($penilai, 'sanctum')
            ->putJson("/api/v1/projects/{$project->id}/verification-checklists", ['items' => $payload])
            ->assertOk()
            ->assertJsonCount(VerificationChecklistItem::where('is_active', true)->count(), 'data.items');

        $this->actingAs($penilai, 'sanctum')
            ->putJson("/api/v1/projects/{$project->id}/verification-checklists", ['items' => $payload])
            ->assertOk();

        $this->assertSame(VerificationChecklistItem::where('is_active', true)->count(), ProjectVerificationChecklist::where('project_id', $project->id)->count());
    }

    public function test_approval_requires_completed_verification_checklist(): void
    {
        $pemohon = $this->userWithRole('pemohon');
        $penilai = $this->userWithRole('penilai');
        $project = $this->projectFor($pemohon, Project::STATUS_SUBMITTED);

        $this->actingAs($penilai, 'sanctum')->postJson("/api/v1/assessments/{$project->id}/start-review")->assertOk();

        $response = $this->actingAs($penilai, 'sanctum')->postJson("/api/v1/assessments/{$project->id}", [
            'decision' => Project::STATUS_APPROVED,
            'notes' => 'Mencoba approve tanpa checklist.',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('checklists');
        $this->assertSame(Project::STATUS_IN_REVIEW, $project->fresh()->status);
    }

    public function test_certificate_can_be_issued_after_approval(): void
    {
        $pemohon = $this->userWithRole('pemohon');
        $penilai = $this->userWithRole('penilai');
        $project = $this->projectFor($pemohon, Project::STATUS_APPROVED, $penilai);
        $project->update(['approved_at' => now()]);
        $this->createChecklistRows($project, $penilai, ProjectVerificationChecklist::STATUS_PASSED);

        $response = $this->actingAs($penilai, 'sanctum')
            ->postJson("/api/v1/projects/{$project->id}/issue-certificate");

        $response->assertOk()
            ->assertJsonPath('data.status', Project::STATUS_CERTIFICATE_ISSUED);

        $project->refresh();
        $this->assertSame(Project::STATUS_CERTIFICATE_ISSUED, $project->status);
        $this->assertNotNull($project->certificate_number);
        $this->assertNotNull($project->certificate_issued_at);
        $this->assertSame($penilai->id, $project->certificate_issued_by);
    }

    public function test_certificate_pdf_requires_issued_certificate(): void
    {
        $pemohon = $this->userWithRole('pemohon');
        $penilai = $this->userWithRole('penilai');
        $project = $this->projectFor($pemohon, Project::STATUS_APPROVED, $penilai);

        $this->actingAs($penilai, 'sanctum')
            ->get("/api/v1/exports/projects/{$project->id}/certificate")
            ->assertUnprocessable();
    }

    public function test_user_can_mark_own_notifications_as_read(): void
    {
        $pemohon = $this->userWithRole('pemohon');
        $other = $this->userWithRole('pemohon');
        $notification = Notification::create([
            'user_id' => $pemohon->id,
            'title' => 'Status baru',
            'message' => 'Permohonan diperbarui.',
            'type' => 'info',
        ]);
        $otherNotification = Notification::create([
            'user_id' => $other->id,
            'title' => 'Private',
            'message' => 'Notifikasi user lain.',
            'type' => 'info',
        ]);

        $this->actingAs($pemohon, 'sanctum')
            ->patchJson("/api/v1/notifications/{$notification->id}/read")
            ->assertOk()
            ->assertJsonPath('data.notification.is_read', true);

        $this->assertNotNull($notification->fresh()->read_at);
        $this->assertFalse($otherNotification->fresh()->is_read);
    }

    public function test_all_application_controller_routes_point_to_existing_methods(): void
    {
        $checkedRoutes = 0;

        foreach (Route::getRoutes() as $route) {
            $action = $route->getActionName();

            if (! str_contains($action, 'App\\Http\\Controllers\\')) {
                continue;
            }

            $checkedRoutes++;

            if (! str_contains($action, '@')) {
                $this->assertTrue(
                    class_exists($action) && method_exists($action, '__invoke'),
                    "{$route->uri()} points to missing invokable {$action}"
                );

                continue;
            }

            [$controller, $method] = explode('@', $action);

            $this->assertTrue(
                method_exists($controller, $method),
                "{$route->uri()} points to missing {$controller}@{$method}"
            );
        }

        $this->assertGreaterThan(0, $checkedRoutes);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function projectFor(User $pemohon, string $status, ?User $penilai = null): Project
    {
        return Project::create([
            'project_number' => 'PRJ-'.fake()->unique()->numerify('########'),
            'title' => 'Permohonan Test',
            'applicant_id' => $pemohon->id,
            'evaluator_id' => $penilai?->id,
            'document_type_id' => $this->documentType->id,
            'status' => $status,
            'description' => 'Dokumen untuk pengujian.',
            'submitted_at' => $status === Project::STATUS_DRAFT ? null : now(),
        ]);
    }

    private function checklistPayload(string $requiredStatus): array
    {
        return VerificationChecklistItem::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (VerificationChecklistItem $item) => [
                'checklist_item_id' => $item->id,
                'status' => $item->is_required ? $requiredStatus : ProjectVerificationChecklist::STATUS_NOT_APPLICABLE,
                'notes' => $item->is_required ? 'Sudah diverifikasi.' : 'Tidak wajib.',
            ])
            ->all();
    }

    private function createChecklistRows(Project $project, User $penilai, string $requiredStatus): void
    {
        VerificationChecklistItem::where('is_active', true)->get()->each(function (VerificationChecklistItem $item) use ($project, $penilai, $requiredStatus) {
            ProjectVerificationChecklist::create([
                'project_id' => $project->id,
                'checklist_item_id' => $item->id,
                'reviewer_id' => $penilai->id,
                'status' => $item->is_required ? $requiredStatus : ProjectVerificationChecklist::STATUS_NOT_APPLICABLE,
                'notes' => 'Checklist test.',
                'checked_at' => now(),
            ]);
        });
    }
}
