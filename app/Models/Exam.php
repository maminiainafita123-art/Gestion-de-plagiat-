<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'professor_id',
        'title',
        'description',
        'deadline',
        'allowed_extensions',
        'excluded_filenames',
        'status',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'allowed_extensions' => 'array',
        'excluded_filenames' => 'array',
    ];

    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function plagiarismResults(): HasMany
    {
        return $this->hasMany(PlagiarismResult::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open' && $this->deadline->isFuture();
    }
}