<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('certificate_number')->nullable()->unique()->after('rejected_at');
            $table->timestamp('certificate_issued_at')->nullable()->after('certificate_number');
            $table->foreignId('certificate_issued_by')->nullable()->after('certificate_issued_at')->constrained('users')->onDelete('set null');

            $table->index(['status', 'certificate_issued_at']);
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('certificate_issued_by');
            $table->dropIndex(['status', 'certificate_issued_at']);
            $table->dropUnique(['certificate_number']);
            $table->dropColumn(['certificate_number', 'certificate_issued_at']);
        });
    }
};
