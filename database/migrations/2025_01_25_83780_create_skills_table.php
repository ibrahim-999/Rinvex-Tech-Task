<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skill_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->string('action');
            $table->json('payload')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['skill_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skill_activities');
    }
};
