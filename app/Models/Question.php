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

    /**
     * Shared field validation, reused by the MCP-facing API controller, the
     * web management controller, and JSON bulk import (one rule set per row).
     *
     * @return array<string, string>
     */
    public static function rules(): array
    {
        return [
            'type' => 'required|in:mcq,short_answer,coding',
            'difficulty' => 'required|string',
            'prompt' => 'required|string',
            'reference_answer' => 'required|string',
            'language' => 'nullable|in:javascript,php',
            'test_cases' => 'nullable|array',
        ];
    }
}
