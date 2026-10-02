<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('checksum', 64)->nullable();
            $table->unsignedInteger('revision_number')->default(0);
            $table->timestamps();

            $table->index('application_id');
        });

        Schema::create('application_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->string('decision'); 
            $table->text('note')->nullable();
            $table->unsignedInteger('revision_number')->default(0);
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['application_id', 'reviewed_at']);
            $table->index('reviewer_id');
        });

        if (!Schema::hasColumn('application_status_logs', 'actor_id')) {
            Schema::table('application_status_logs', function (Blueprint $table) {
                $table->renameColumn('changed_by', 'actor_id');
            });
        }
        if (!Schema::hasColumn('application_status_logs', 'note')) {
            Schema::table('application_status_logs', function (Blueprint $table) {
                $table->renameColumn('notes', 'note');
            });
        }
        if (!Schema::hasColumn('application_status_logs', 'metadata')) {
            Schema::table('application_status_logs', function (Blueprint $table) {
                $table->json('metadata')->nullable()->after('note');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('application_reviews');
        Schema::dropIfExists('application_documents');
    }
};
