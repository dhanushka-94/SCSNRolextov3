<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_sync_operations', function (Blueprint $table) {
            $table->id();
            $table->string('actor_type', 20);
            $table->unsignedBigInteger('actor_id');
            $table->string('client_id', 120);
            $table->string('type', 40);
            $table->json('response')->nullable();
            $table->timestamps();

            $table->unique(['actor_type', 'actor_id', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_sync_operations');
    }
};
