<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fingerprint extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'hash_value',
        'position',
        'line_number',
        'algorithm',
    ];

    protected $casts = [
        'hash_value' => 'integer',
        'position' => 'integer',
        'line_number' => 'integer',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }
}