<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\Question;
use App\Models\Topic;
use Illuminate\Support\Collection;

class ProgressStats
{
    /**
     * @return array{total_attempts: int, accuracy: int, review_due_count: int, draft_count: int}
     */
    public function overall(): array
    {
        $totalAttempts = Attempt::count();
        $correctAttempts = Attempt::where('is_correct', true)->count();

        return [
            'total_attempts' => $totalAttempts,
            'accuracy' => $totalAttempts > 0 ? (int) round($correctAttempts / $totalAttempts * 100) : 0,
            'review_due_count' => Question::where('status', 'approved')
                ->whereNotNull('review_due_at')
                ->where('review_due_at', '<=', now())
                ->count(),
            'draft_count' => Question::where('status', 'draft')->count(),
        ];
    }

    /**
     * @return Collection<int, array{id: int, name: string, slug: string, attempt_count: int, accuracy: int, current_streak: int}>
     */
    public function perTopic(): Collection
    {
        return Topic::with('questions.attempts')->orderBy('name')->get()->map(function (Topic $topic) {
            $attempts = $topic->questions->flatMap->attempts->sortByDesc('created_at')->values();

            $total = $attempts->count();
            $correct = $attempts->where('is_correct', true)->count();

            $streak = 0;
            foreach ($attempts as $attempt) {
                if (! $attempt->is_correct) {
                    break;
                }
                $streak++;
            }

            return [
                'id' => $topic->id,
                'name' => $topic->name,
                'slug' => $topic->slug,
                'attempt_count' => $total,
                'accuracy' => $total > 0 ? (int) round($correct / $total * 100) : 0,
                'current_streak' => $streak,
            ];
        });
    }
}
