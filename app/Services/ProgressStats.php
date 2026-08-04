<?php

namespace App\Services;

use App\Models\Topic;
use Illuminate\Support\Collection;

class ProgressStats
{
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
