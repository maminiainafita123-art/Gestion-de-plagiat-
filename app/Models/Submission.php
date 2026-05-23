<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'student_id',
        'original_filename',
        'stored_path',
        'concatenated_content',
        'file_count',
        'total_lines',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function fingerprints(): HasMany
    {
        return $this->hasMany(Fingerprint::class);
    }

    public function resultsAsA(): HasMany
    {
        return $this->hasMany(PlagiarismResult::class, 'submission_a_id');
    }

    public function resultsAsB(): HasMany
    {
        return $this->hasMany(PlagiarismResult::class, 'submission_b_id');
    }
}