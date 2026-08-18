<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Topic;
use App\Services\AiProvider;
use App\Services\CodeExecutionService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class QuestionController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->renderManage([], $request->only(['topic_id', 'type', 'difficulty']));
    }

    public function generate(Request $request, AiProvider $ai): Response
    {
        $validated = $request->validate([
            'topic_id' => 'required|exists:topics,id',
            'difficulty' => 'required|string',
            'count' => 'required|integer|min:1|max:20',
        ]);

        $topic = Topic::findOrFail($validated['topic_id']);
        $generated = $ai->generateQuestions($topic, $validated['difficulty'], $validated['count']);

        foreach ($generated as $question) {
            $topic->questions()->create([
                'type' => $question['type'],
                'difficulty' => $question['difficulty'] ?? $validated['difficulty'],
                'prompt' => $question['prompt'],
                'reference_answer' => $question['reference_answer'],
                'options' => $question['options'] ?? null,
                'language' => $question['language'] ?? null,
                'test_cases' => $question['test_cases'] ?? null,
                'status' => 'draft',
                'generated_by' => config('services.ai.provider'),
            ]);
        }

        return $this->renderManage(['result' => ['type' => 'generated', 'created' => count($generated), 'errors' => []]]);
    }

    public function store(Request $request): Response
    {
        $validated = $request->validate(array_merge(['topic_id' => 'required|exists:topics,id'], Question::rules()));

        Topic::findOrFail($validated['topic_id'])->questions()->create(array_merge(
            Arr::except($validated, 'topic_id'),
            ['status' => 'draft', 'generated_by' => 'manual'],
        ));

        return $this->renderManage(['result' => ['type' => 'added', 'created' => 1, 'errors' => []]]);
    }

    public function import(Request $request): Response
    {
        $validated = $request->validate([
            'topic_id' => 'required|exists:topics,id',
            'file' => 'required|file|max:2048',
        ]);

        $topic = Topic::findOrFail($validated['topic_id']);
        $rows = json_decode((string) file_get_contents($request->file('file')->getRealPath()), true);

        $created = 0;
        $errors = [];

        if (! is_array($rows)) {
            $errors[] = 'File must contain a JSON array of questions.';
            $rows = [];
        }

        foreach ($rows as $index => $row) {
            $rowValidator = Validator::make(is_array($row) ? $row : [], Question::rules());

            if ($rowValidator->fails()) {
                $rowLabel = is_int($index) ? $index + 1 : $index;
                $errors[] = "Row {$rowLabel}: ".implode(' ', $rowValidator->errors()->all());

                continue;
            }

            $topic->questions()->create(array_merge(
                $rowValidator->validated(),
                ['status' => 'draft', 'generated_by' => 'manual'],
            ));
            $created++;
        }

        return $this->renderManage(['result' => ['type' => 'imported', 'created' => $created, 'errors' => $errors]]);
    }

    /**
     * Run a reference answer against its test cases before saving, closing the
     * gap where a bad reference answer only surfaces once a student's correct
     * submission gets marked wrong.
     */
    public function verify(Request $request, CodeExecutionService $executor): Response
    {
        $validated = $request->validate([
            'language' => 'required|in:javascript,php',
            'reference_answer' => 'required|string',
            'test_cases' => 'required|array|min:1',
        ]);

        $results = $executor->run($validated['language'], $validated['reference_answer'], $validated['test_cases']);

        return $this->renderManage(['verification' => $results]);
    }

    public function approve(Question $question): Response
    {
        $question->update(['status' => 'approved']);

        return $this->renderManage();
    }

    public function update(Request $request, Question $question): Response
    {
        $validated = $request->validate(Question::rules());

        $question->update($validated);

        return $this->renderManage();
    }

    public function destroy(Question $question): Response
    {
        $question->delete();

        return $this->renderManage();
    }

    public function bulkApprove(Request $request): Response
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:questions,id',
        ]);

        Question::whereIn('id', $validated['ids'])->where('status', 'draft')->update(['status' => 'approved']);

        return $this->renderManage();
    }

    public function bulkReject(Request $request): Response
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:questions,id',
        ]);

        Question::whereIn('id', $validated['ids'])->where('status', 'draft')->delete();

        return $this->renderManage();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function renderManage(array $extra = [], array $filters = []): Response
    {
        $query = Question::with('topic:id,name')->where('status', 'draft');

        if (! empty($filters['topic_id'])) {
            $query->where('topic_id', $filters['topic_id']);
        }
        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (! empty($filters['difficulty'])) {
            $query->where('difficulty', $filters['difficulty']);
        }

        return Inertia::render('questions/manage', array_merge([
            'topics' => Topic::orderBy('name')->get(['id', 'name', 'slug']),
            'draftQuestions' => $query->latest()
                ->paginate(10, [
                    'id', 'topic_id', 'type', 'difficulty', 'prompt', 'reference_answer',
                    'options', 'language', 'test_cases', 'generated_by', 'created_at',
                ])
                ->withQueryString(),
            'filters' => (object) $filters,
            'result' => null,
            'verification' => null,
        ], $extra));
    }
}
