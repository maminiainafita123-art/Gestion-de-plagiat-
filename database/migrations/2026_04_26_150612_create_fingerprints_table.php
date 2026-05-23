
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fingerprints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('submissions')->onDelete('cascade');
            $table->bigInteger('hash_value');
            $table->integer('position')->nullable();
            $table->integer('line_number')->nullable();
            $table->string('algorithm'); // 'winnowing' ou 'jaccard_bloom'
            $table->timestamps();

            $table->index(['submission_id', 'algorithm']);
            $table->index(['hash_value', 'algorithm']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fingerprints');
    }
};