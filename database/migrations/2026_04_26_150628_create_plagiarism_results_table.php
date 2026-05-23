
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plagiarism_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->foreignId('submission_a_id')->constrained('submissions')->onDelete('cascade');
            $table->foreignId('submission_b_id')->constrained('submissions')->onDelete('cascade');
            $table->float('winnowing_score')->nullable();
            $table->float('jaccard_bloom_score')->nullable();
            $table->float('combined_score')->nullable();
            $table->string('algorithm_used'); // 'winnowing', 'jaccard_bloom', 'both'
            $table->json('matched_positions')->nullable();
            $table->timestamps();

            $table->unique(['submission_a_id', 'submission_b_id', 'algorithm_used']);
            $table->index(['exam_id', 'combined_score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plagiarism_results');
    }
};