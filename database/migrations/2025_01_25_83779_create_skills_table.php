<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('category');
            $table->unsignedTinyInteger('proficiency_level')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->json('attachments')->nullable();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['category', 'is_active']);
            $table->index('proficiency_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skills');
    }
};
