<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'submitted_answer',
        'is_correct',
        'score',
        'feedback',
        'execution_result',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'execution_result' => 'array',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
