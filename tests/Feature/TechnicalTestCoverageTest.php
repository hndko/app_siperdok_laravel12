<?php

namespace Tests\Feature;

use App\Jobs\CreateProjectStatusNotification;
use App\Models\AssessmentLog;
use App\Models\DocumentType;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\DocumentTypeSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $response = $this->actingAs($penilai)->post("/assessments/{$project->id}/start-review");

        $response->assertRedirect("/assessments/{$project->id}/review");
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

        $response = $this->actingAs($pemohon)->post("/assessments/{$project->id}/start-review");

        $response->assertForbidden();
        $this->assertSame(Project::STATUS_SUBMITTED, $project->fresh()->status);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $pemohon = $this->userWithRole('pemohon');
        $penilai = $this->userWithRole('penilai');
        $project = $this->projectFor($pemohon, Project::STATUS_SUBMITTED);

        $response = $this->actingAs($penilai)->post("/assessments/{$project->id}/process", [
            'decision' => 'approved',
            'notes' => 'Langsung approve tanpa review.',
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertSame(Project::STATUS_SUBMITTED, $project->fresh()->status);
    }

    public function test_project_delete_follows_business_rules(): void
    {
        $pemohon = $this->userWithRole('pemohon');
        $draft = $this->projectFor($pemohon, Project::STATUS_DRAFT);
        $submitted = $this->projectFor($pemohon, Project::STATUS_SUBMITTED);

        $this->actingAs($pemohon)->delete("/projects/{$submitted->id}")->assertForbidden();
        $this->actingAs($pemohon)->delete("/projects/{$draft->id}")->assertRedirect('/projects');

        $this->assertDatabaseMissing('projects', ['id' => $draft->id]);
        $this->assertDatabaseHas('projects', ['id' => $submitted->id]);
    }

    public function test_excel_export_can_be_downloaded_by_penilai(): void
    {
        $pemohon = $this->userWithRole('pemohon');
        $penilai = $this->userWithRole('penilai');
        $this->projectFor($pemohon, Project::STATUS_SUBMITTED);

        $response = $this->actingAs($penilai)->get('/export/projects/xlsx?status=submitted');

        $response->assertOk();
        $this->assertStringContainsString('spreadsheetml', $response->headers->get('content-type'));
    }

    public function test_all_application_controller_routes_point_to_existing_methods(): void
    {
        foreach (Route::getRoutes() as $route) {
            $action = $route->getActionName();

            if (!str_contains($action, 'App\\Http\\Controllers\\') || !str_contains($action, '@')) {
                continue;
            }

            [$controller, $method] = explode('@', $action);

            $this->assertTrue(
                method_exists($controller, $method),
                "{$route->uri()} points to missing {$controller}@{$method}"
            );
        }
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
            'project_number' => 'PRJ-' . fake()->unique()->numerify('########'),
            'title' => 'Permohonan Test',
            'applicant_id' => $pemohon->id,
            'evaluator_id' => $penilai?->id,
            'document_type_id' => $this->documentType->id,
            'status' => $status,
            'description' => 'Dokumen untuk pengujian.',
            'submitted_at' => $status === Project::STATUS_DRAFT ? null : now(),
        ]);
    }
}
