<?php

namespace App\Services;

use App\Models\Question;

/**
 * Leitner-style spaced repetition: wrong answers drop back to bucket 1 and
 * resurface almost immediately; each correct answer promotes the question
 * to a bucket with a longer delay before it's due again.
 */
class ReviewQueue
{
    private const MAX_BUCKET = 5;

    private const INTERVAL_DAYS = [
        1 => 1,
        2 => 2,
        3 => 4,
        4 => 7,
        5 => 14,
    ];

    public function recordResult(Question $question, bool $isCorrect): void
    {
        $bucket = $isCorrect
            ? min($question->review_bucket + 1, self::MAX_BUCKET)
            : 1;

        $question->update([
            'review_bucket' => $bucket,
            'review_due_at' => now()->addDays(self::INTERVAL_DAYS[$bucket]),
        ]);
    }
}
