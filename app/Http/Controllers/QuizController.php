<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Topic;
use App\Services\CodeExecutionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuizController extends Controller
{
    private const QUESTION_COLUMNS = ['id', 'type', 'difficulty', 'prompt', 'options', 'language', 'test_cases', 'review_due_at'];

    /**
     * Show the next question for this topic/difficulty, skipping any already
     * seen this quiz session (tracked client-side, passed back as `exclude`).
     * An optional `type` narrows the pool to a single question type (e.g.
     * coding-only practice).
     */
    public function show(Request $request, Topic $topic): Response
    {
        $validated = $request->validate([
            'difficulty' => 'required|string',
            'exclude' => 'nullable|string',
            'type' => 'nullable|in:mcq,short_answer,coding',
        ]);

        $excludeIds = array_filter(explode(',', $validated['exclude'] ?? ''));
        $type = $validated['type'] ?? null;

        $question = Question::query()
            ->where('topic_id', $topic->id)
            ->where('difficulty', $validated['difficulty'])
            ->where('status', 'approved')
            ->when($type, fn ($query) => $query->where('type', $type))
            ->whereNotIn('id', $excludeIds)
            // Due-for-review questions first, then never-attempted (null due date), then not-yet-due.
            // Binds "now" as a parameter rather than a DB-specific NOW() so this works on sqlite too.
            ->orderByRaw('CASE
                WHEN review_due_at IS NOT NULL AND review_due_at <= ? THEN 0
                WHEN review_due_at IS NULL THEN 1
                ELSE 2
            END', [now()])
            ->orderBy('id')
            ->first(self::QUESTION_COLUMNS);

        return $this->render($topic, $validated['difficulty'], $question, $excludeIds, null, $type);
    }

    /**
     * Re-show a specific, already-seen question (the last one added to
     * `exclude`), so the learner can step back one question. Removes it from
     * `exclude` so it's treated as the current question again; it's
     * re-excluded naturally once they submit or skip it again.
     */
    public function back(Request $request, Question $question): Response
    {
        $validated = $request->validate([
            'difficulty' => 'required|string',
            'exclude' => 'nullable|string',
            'type' => 'nullable|in:mcq,short_answer,coding',
        ]);

        $excludeIds = array_values(array_diff(
            array_filter(explode(',', $validated['exclude'] ?? '')),
            [(string) $question->id],
        ));

        return $this->render($question->topic, $validated['difficulty'], $question, $excludeIds, null, $validated['type'] ?? null);
    }

    /**
     * Run a coding submission against the question's test cases without
     * grading it, so learners get a lower-stakes way to check their work
     * before a real graded attempt.
     */
    public function run(Request $request, Question $question, CodeExecutionService $executor): Response
    {
        if ($question->type !== 'coding' || $question->language === null) {
            abort(422, 'Only coding questions can be run.');
        }

        $validated = $request->validate([
            'answer' => 'required|string',
            'difficulty' => 'required|string',
            'exclude' => 'nullable|string',
            'type' => 'nullable|in:mcq,short_answer,coding',
        ]);

        $runResult = $executor->run($question->language, $validated['answer'], $question->test_cases ?? []);
        $excludeIds = array_filter(explode(',', $validated['exclude'] ?? ''));

        return $this->render($question->topic, $validated['difficulty'], $question, $excludeIds, $runResult, $validated['type'] ?? null);
    }

    private function render(
        Topic $topic,
        string $difficulty,
        ?Question $question,
        array $excludeIds,
        ?array $runResult = null,
        ?string $type = null,
    ): Response {
        return Inertia::render('quiz/show', [
            'topic' => $topic->only(['id', 'name', 'slug']),
            'difficulty' => $difficulty,
            'type' => $type,
            'question' => $this->questionPayload($question),
            'excludeIds' => $excludeIds,
            'totalQuestions' => Question::where('topic_id', $topic->id)
                ->where('difficulty', $difficulty)
                ->where('status', 'approved')
                ->when($type, fn ($query) => $query->where('type', $type))
                ->count(),
            'executionTimeoutSeconds' => CodeExecutionService::DEFAULT_TIMEOUT_SECONDS,
            'runResult' => $runResult,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function questionPayload(?Question $question): ?array
    {
        if ($question === null) {
            return null;
        }

        return [
            'id' => $question->id,
            'type' => $question->type,
            'difficulty' => $question->difficulty,
            'prompt' => $question->prompt,
            'options' => $question->options,
            'language' => $question->language,
            'test_cases' => $question->test_cases,
            'review_status' => match (true) {
                $question->review_due_at === null => 'new',
                $question->review_due_at->isPast() => 'due',
                default => 'scheduled',
            },
        ];
    }
}
