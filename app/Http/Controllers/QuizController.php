<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Topic;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuizController extends Controller
{
    /**
     * Show the next question for this topic/difficulty, skipping any already
     * seen this quiz session (tracked client-side, passed back as `exclude`).
     */
    public function show(Request $request, Topic $topic): Response
    {
        $validated = $request->validate([
            'difficulty' => 'required|string',
            'exclude' => 'nullable|string',
        ]);

        $excludeIds = array_filter(explode(',', $validated['exclude'] ?? ''));

        $question = Question::query()
            ->where('topic_id', $topic->id)
            ->where('difficulty', $validated['difficulty'])
            ->where('status', 'approved')
            ->whereNotIn('id', $excludeIds)
            // Due-for-review questions first, then never-attempted (null due date), then not-yet-due.
            ->orderByRaw('CASE
                WHEN review_due_at IS NOT NULL AND review_due_at <= NOW() THEN 0
                WHEN review_due_at IS NULL THEN 1
                ELSE 2
            END')
            ->orderBy('id')
            ->first(['id', 'type', 'difficulty', 'prompt', 'language']);

        return Inertia::render('quiz/show', [
            'topic' => $topic->only(['id', 'name', 'slug']),
            'difficulty' => $validated['difficulty'],
            'question' => $question,
            'excludeIds' => $excludeIds,
        ]);
    }
}
