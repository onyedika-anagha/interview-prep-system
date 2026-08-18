<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Services\AiProvider;
use App\Services\CodeExecutionService;
use App\Services\ReviewQueue;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttemptController extends Controller
{
    public function store(Request $request, Question $question, AiProvider $ai, CodeExecutionService $executor, ReviewQueue $reviewQueue): Response
    {
        $validated = $request->validate([
            'answer' => 'required|string',
            'difficulty' => 'required|string',
            'exclude' => 'nullable|string',
            'type' => 'nullable|in:mcq,short_answer,coding',
        ]);

        if ($question->type === 'coding') {
            $executionResult = $executor->run($question->language, $validated['answer'], $question->test_cases ?? []);
            $isCorrect = count($executionResult) > 0 && collect($executionResult)->every(fn (array $r) => $r['passed']);
            $score = (int) round(collect($executionResult)->avg(fn (array $r) => $r['passed'] ? 100 : 0));
            $feedback = $ai->explainCodeResult($question, $validated['answer'], $executionResult);
        } else {
            $grading = $ai->gradeAnswer($question, $validated['answer']);
            $isCorrect = (bool) $grading['is_correct'];
            $score = (int) $grading['score'];
            $feedback = $grading['feedback'];
            $executionResult = null;
        }

        $attempt = $question->attempts()->create([
            'submitted_answer' => $validated['answer'],
            'is_correct' => $isCorrect,
            'score' => $score,
            'feedback' => $feedback,
            'execution_result' => $executionResult,
        ]);

        $reviewQueue->recordResult($question, $isCorrect);

        $excludeIds = array_values(array_unique(array_filter([
            ...explode(',', $validated['exclude'] ?? ''),
            (string) $question->id,
        ])));

        return Inertia::render('quiz/result', [
            'topic' => $question->topic->only(['id', 'name', 'slug']),
            'difficulty' => $validated['difficulty'],
            'type' => $validated['type'] ?? null,
            'question' => $question->only(['id', 'type', 'prompt', 'reference_answer']),
            'attempt' => $attempt->only(['is_correct', 'score', 'feedback', 'execution_result']),
            'excludeIds' => $excludeIds,
        ]);
    }
}
