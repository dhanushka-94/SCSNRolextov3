<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planter_id')->constrained()->cascadeOnDelete();
            $table->string('certificate_number')->unique();
            $table->uuid('public_token')->unique();
            $table->string('status', 20)->default('issued');
            $table->string('outcome', 30);
            $table->boolean('is_current')->default(true);
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->text('revoke_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['planter_id', 'is_current']);
            $table->index(['status', 'issued_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
