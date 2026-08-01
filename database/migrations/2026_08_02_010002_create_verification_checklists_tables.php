<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('project_verification_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('checklist_item_id')->constrained('verification_checklist_items')->onDelete('restrict');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'checklist_item_id']);
            $table->index(['project_id', 'status']);
            $table->index(['checklist_item_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_verification_checklists');
        Schema::dropIfExists('verification_checklist_items');
    }
};
