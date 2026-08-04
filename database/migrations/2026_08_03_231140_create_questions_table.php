<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['mcq', 'short_answer', 'coding']);
            $table->string('difficulty');
            $table->text('prompt');
            $table->text('reference_answer');
            $table->enum('language', ['javascript', 'php'])->nullable();
            $table->json('test_cases')->nullable();
            $table->enum('status', ['draft', 'approved'])->default('draft');
            $table->string('generated_by')->default('manual');
            $table->unsignedTinyInteger('review_bucket')->default(1);
            $table->timestamp('review_due_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
