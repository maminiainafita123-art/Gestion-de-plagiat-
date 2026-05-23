<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlagiarismResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'submission_a_id',
        'submission_b_id',
        'winnowing_score',
        'jaccard_bloom_score',
        'combined_score',
        'algorithm_used',
        'matched_positions',
    ];

    protected $casts = [
        'winnowing_score' => 'float',
        'jaccard_bloom_score' => 'float',
        'combined_score' => 'float',
        'matched_positions' => 'array',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function submissionA(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'submission_a_id');
    }

    public function submissionB(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'submission_b_id');
    }
}