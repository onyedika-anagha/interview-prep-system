<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'type',
        'difficulty',
        'prompt',
        'reference_answer',
        'language',
        'test_cases',
        'status',
        'generated_by',
        'review_bucket',
        'review_due_at',
    ];

    protected function casts(): array
    {
        return [
            'test_cases' => 'array',
            'review_due_at' => 'datetime',
        ];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }
}
