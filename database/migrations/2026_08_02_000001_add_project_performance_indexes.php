<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->index(['document_type_id', 'status'], 'projects_document_type_status_index');
            $table->index(['applicant_id', 'created_at'], 'projects_applicant_created_index');
            $table->index(['evaluator_id', 'created_at'], 'projects_evaluator_created_index');
            $table->index('submitted_at', 'projects_submitted_at_index');
        });

        Schema::table('assessment_logs', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'assessment_logs_user_created_index');
            $table->index(['action', 'created_at'], 'assessment_logs_action_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('assessment_logs', function (Blueprint $table) {
            $table->dropIndex('assessment_logs_user_created_index');
            $table->dropIndex('assessment_logs_action_created_index');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('projects_document_type_status_index');
            $table->dropIndex('projects_applicant_created_index');
            $table->dropIndex('projects_evaluator_created_index');
            $table->dropIndex('projects_submitted_at_index');
        });
    }
};
