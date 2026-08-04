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
            ->has('draftQuestions', 1));
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
