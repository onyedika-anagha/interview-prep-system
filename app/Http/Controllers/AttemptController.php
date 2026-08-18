<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
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
        } elseif ($question->type === 'mcq') {
            // The submitted answer is always one of the option strings (radio-selected, not
            // typed), and reference_answer is guaranteed to exactly match one option, so this
            // is a deterministic comparison — no need to spend an AI call grading it.
            $isCorrect = $validated['answer'] === $question->reference_answer;
            $score = $isCorrect ? 100 : 0;
            $feedback = null;
            $executionResult = null;
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

        return $this->render($question, $attempt, $validated['difficulty'], $validated['exclude'] ?? '', $validated['type'] ?? null);
    }

    /**
     * Fetch an AI explanation for an already-graded MCQ attempt, on demand — grading itself
     * already happened locally in store() and is not recomputed here.
     */
    public function feedback(Request $request, Question $question, Attempt $attempt, AiProvider $ai): Response
    {
        abort_unless($attempt->question_id === $question->id, 404);
        abort_unless($question->type === 'mcq', 422, 'Only MCQ attempts support on-demand AI feedback.');

        $validated = $request->validate([
            'difficulty' => 'required|string',
            'exclude' => 'nullable|string',
            'type' => 'nullable|in:mcq,short_answer,coding',
        ]);

        if ($attempt->feedback === null) {
            $grading = $ai->gradeAnswer($question, $attempt->submitted_answer);
            $attempt->update(['feedback' => $grading['feedback']]);
        }

        return $this->render($question, $attempt, $validated['difficulty'], $validated['exclude'] ?? '', $validated['type'] ?? null);
    }

    private function render(Question $question, Attempt $attempt, string $difficulty, string $exclude, ?string $type): Response
    {
        $excludeIds = array_values(array_unique(array_filter([
            ...explode(',', $exclude),
            (string) $question->id,
        ])));

        return Inertia::render('quiz/result', [
            'topic' => $question->topic->only(['id', 'name', 'slug']),
            'difficulty' => $difficulty,
            'type' => $type,
            'question' => $question->only(['id', 'type', 'prompt', 'reference_answer']),
            'attempt' => $attempt->only(['id', 'is_correct', 'score', 'feedback', 'execution_result']),
            'excludeIds' => $excludeIds,
        ]);
    }
}
