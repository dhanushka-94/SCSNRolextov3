<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->string('module', 20);
            $table->string('code', 40);
            $table->string('label');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['module', 'code']);
            $table->index(['module', 'sort_order']);
        });

        Schema::create('planter_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planter_id')->constrained()->cascadeOnDelete();
            $table->string('round', 20);
            $table->string('status', 30)->default('queued');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('outcome_notes')->nullable();
            $table->timestamps();

            $table->unique(['planter_id', 'round']);
            $table->index(['round', 'status']);
        });

        Schema::create('planter_audit_checklist_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planter_audit_id')->constrained('planter_audits')->cascadeOnDelete();
            $table->foreignId('audit_checklist_item_id')->constrained('audit_checklist_items')->cascadeOnDelete();
            $table->string('result', 20)->default('pending');
            $table->text('comment')->nullable();
            $table->foreignId('answered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->unique(['planter_audit_id', 'audit_checklist_item_id'], 'planter_audit_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planter_audit_checklist_answers');
        Schema::dropIfExists('planter_audits');
        Schema::dropIfExists('audit_checklist_items');
    }
};
