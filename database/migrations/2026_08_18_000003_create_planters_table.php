<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planters', function (Blueprint $table) {
            $table->id();
            $table->string('temporary_id', 24)->unique();
            $table->string('name');
            $table->string('nic', 20)->unique();
            $table->string('email')->unique();
            $table->string('phone', 30);
            $table->string('district', 60);
            $table->text('address');
            $table->string('password');
            $table->string('status', 20)->default('pending')->index();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planters');
    }
};
