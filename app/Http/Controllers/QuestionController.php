<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Topic;
use App\Services\AiProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class QuestionController extends Controller
{
    public function index(): Response
    {
        return $this->renderManage();
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
                $errors[] = "Row {$index}: ".$rowValidator->errors()->first();

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

    public function approve(Question $question): Response
    {
        $question->update(['status' => 'approved']);

        return $this->renderManage();
    }

    public function destroy(Question $question): Response
    {
        $question->delete();

        return $this->renderManage();
    }

    private function renderManage(array $extra = []): Response
    {
        return Inertia::render('questions/manage', array_merge([
            'topics' => Topic::orderBy('name')->get(['id', 'name', 'slug']),
            'draftQuestions' => Question::with('topic:id,name')
                ->where('status', 'draft')
                ->latest()
                ->get(['id', 'topic_id', 'type', 'difficulty', 'prompt', 'generated_by', 'created_at']),
            'result' => null,
        ], $extra));
    }
}
