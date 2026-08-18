<?php

use App\Models\Question;
use App\Models\Topic;
use App\Services\AiProvider;
use Illuminate\Http\UploadedFile;

it('renders the manage page with topics and draft questions', function () {
    $topic = Topic::create(['name' => 'DSA', 'category' => 'general', 'description' => 'x']);
    $topic->questions()->create([
        'type' => 'short_answer', 'difficulty' => 'easy', 'prompt' => 'x',
        'reference_answer' => 'y', 'status' => 'draft', 'generated_by' => 'manual',
    ]);

    $this->get('/questions/manage')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('questions/manage')
            ->has('topics', 1)
            ->has('draftQuestions.data', 1)
            ->where('draftQuestions.total', 1));
});

it('generates draft questions for a topic via the configured AI provider', function () {
    $topic = Topic::create(['name' => 'DSA', 'category' => 'general', 'description' => 'x']);

    $this->app->instance(AiProvider::class, new class implements AiProvider
    {
        public function generateQuestions($topic, string $difficulty, int $count): array
        {
            return [
                ['type' => 'short_answer', 'difficulty' => $difficulty, 'prompt' => 'q1', 'reference_answer' => 'a1'],
                ['type' => 'short_answer', 'difficulty' => $difficulty, 'prompt' => 'q2', 'reference_answer' => 'a2'],
            ];
        }

        public function gradeAnswer($question, string $answer): array
        {
            return ['is_correct' => true, 'score' => 100, 'feedback' => 'ok'];
        }

        public function explainCodeResult($question, string $code, array $executionResult): string
        {
            return 'ok';
        }
    });

    $this->post('/questions/generate', ['topic_id' => $topic->id, 'difficulty' => 'easy', 'count' => 2])
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('result.created', 2));

    expect(Question::where('topic_id', $topic->id)->where('status', 'draft')->count())->toBe(2);
});

it('adds a manual question as a draft', function () {
    $topic = Topic::create(['name' => 'DSA', 'category' => 'general', 'description' => 'x']);

    $this->post('/questions', [
        'topic_id' => $topic->id,
        'type' => 'short_answer',
        'difficulty' => 'easy',
        'prompt' => 'What is Big O?',
        'reference_answer' => 'A complexity bound.',
    ])->assertOk();

    expect(Question::where('topic_id', $topic->id)->first())
        ->status->toBe('draft')
        ->generated_by->toBe('manual');
});

it('imports valid rows from a JSON file and reports errors for invalid ones', function () {
    $topic = Topic::create(['name' => 'DSA', 'category' => 'general', 'description' => 'x']);

    $rows = [
        ['type' => 'short_answer', 'difficulty' => 'easy', 'prompt' => 'valid', 'reference_answer' => 'a'],
        ['type' => 'not-a-type', 'difficulty' => 'easy', 'prompt' => 'invalid', 'reference_answer' => 'a'],
    ];
    $file = UploadedFile::fake()->createWithContent('questions.json', json_encode($rows));

    $this->post('/questions/import', ['topic_id' => $topic->id, 'file' => $file])
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('result.created', 1)->has('result.errors', 1));

    expect(Question::where('topic_id', $topic->id)->count())->toBe(1);
});

it('rejects an import file that is not a JSON array', function () {
    $topic = Topic::create(['name' => 'DSA', 'category' => 'general', 'description' => 'x']);
    $file = UploadedFile::fake()->createWithContent('questions.json', json_encode(['not' => 'an array']));

    $this->post('/questions/import', ['topic_id' => $topic->id, 'file' => $file])
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('result.created', 0)->has('result.errors', 1));

    expect(Question::where('topic_id', $topic->id)->count())->toBe(0);
});

it('verifies a reference answer against its test cases before saving', function () {
    $php = <<<'PHP'
        <?php
        $data = json_decode(file_get_contents('php://stdin'), true);
        echo json_encode($data['a'] + $data['b']);
        PHP;

    $this->post('/questions/verify', [
        'language' => 'php',
        'reference_answer' => $php,
        'test_cases' => [
            ['input' => ['a' => 2, 'b' => 3], 'expected_output' => 5],
            ['input' => ['a' => 1, 'b' => 1], 'expected_output' => 3],
        ],
    ])
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('verification.0.passed', true)
            ->where('verification.1.passed', false));
});

it('approves and rejects draft questions', function () {
    $topic = Topic::create(['name' => 'DSA', 'category' => 'general', 'description' => 'x']);
    $question = $topic->questions()->create([
        'type' => 'short_answer', 'difficulty' => 'easy', 'prompt' => 'x',
        'reference_answer' => 'y', 'status' => 'draft', 'generated_by' => 'manual',
    ]);

    $this->patch("/questions/{$question->id}/approve")->assertOk();
    expect($question->fresh()->status)->toBe('approved');

    $this->delete("/questions/{$question->id}")->assertOk();
    expect(Question::find($question->id))->toBeNull();
});

it('edits a draft question', function () {
    $topic = Topic::create(['name' => 'DSA', 'category' => 'general', 'description' => 'x']);
    $question = $topic->questions()->create([
        'type' => 'short_answer', 'difficulty' => 'easy', 'prompt' => 'typo here',
        'reference_answer' => 'y', 'status' => 'draft', 'generated_by' => 'manual',
    ]);

    $this->patch("/questions/{$question->id}", [
        'type' => 'short_answer',
        'difficulty' => 'medium',
        'prompt' => 'fixed prompt',
        'reference_answer' => 'y',
    ])->assertOk();

    expect($question->fresh())
        ->prompt->toBe('fixed prompt')
        ->difficulty->toBe('medium')
        ->status->toBe('draft');
});

it('bulk approves and bulk rejects draft questions', function () {
    $topic = Topic::create(['name' => 'DSA', 'category' => 'general', 'description' => 'x']);
    $questions = collect(range(1, 3))->map(fn ($i) => $topic->questions()->create([
        'type' => 'short_answer', 'difficulty' => 'easy', 'prompt' => "q{$i}",
        'reference_answer' => 'y', 'status' => 'draft', 'generated_by' => 'manual',
    ]));

    $this->patch('/questions/bulk/approve', ['ids' => $questions->take(2)->pluck('id')->all()])->assertOk();
    expect(Question::where('status', 'approved')->count())->toBe(2);

    $this->delete('/questions/bulk', ['ids' => [$questions->last()->id]])->assertOk();
    expect(Question::count())->toBe(2);
});

it('filters the draft list by topic, type, and difficulty', function () {
    $dsa = Topic::create(['name' => 'DSA', 'category' => 'general', 'description' => 'x']);
    $php = Topic::create(['name' => 'PHP', 'category' => 'stack', 'description' => 'x']);

    $dsa->questions()->create([
        'type' => 'mcq', 'difficulty' => 'easy', 'prompt' => 'a',
        'reference_answer' => 'y', 'options' => ['y', 'n'], 'status' => 'draft', 'generated_by' => 'manual',
    ]);
    $php->questions()->create([
        'type' => 'short_answer', 'difficulty' => 'hard', 'prompt' => 'b',
        'reference_answer' => 'y', 'status' => 'draft', 'generated_by' => 'manual',
    ]);

    $this->get('/questions/manage?'.http_build_query(['topic_id' => $php->id]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('draftQuestions.data', 1)->where('draftQuestions.data.0.prompt', 'b'));

    $this->get('/questions/manage?'.http_build_query(['type' => 'mcq']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('draftQuestions.data', 1)->where('draftQuestions.data.0.prompt', 'a'));
});
