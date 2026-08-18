<?php

use App\Models\Attempt;
use App\Models\Topic;
use App\Services\AiProvider;

function makeAttemptTopic(): Topic
{
    return Topic::create(['name' => 'DSA', 'category' => 'general', 'description' => 'x']);
}

function throwingAiProvider(): AiProvider
{
    return new class implements AiProvider
    {
        public function generateQuestions($topic, string $difficulty, int $count): array
        {
            throw new RuntimeException('AI provider should not be called');
        }

        public function gradeAnswer($question, string $answer): array
        {
            throw new RuntimeException('AI provider should not be called');
        }

        public function explainCodeResult($question, string $code, array $executionResult): string
        {
            throw new RuntimeException('AI provider should not be called');
        }
    };
}

it('grades a correct MCQ answer locally without calling the AI provider', function () {
    $this->app->instance(AiProvider::class, throwingAiProvider());
    $topic = makeAttemptTopic();
    $question = $topic->questions()->create([
        'type' => 'mcq', 'difficulty' => 'easy', 'prompt' => 'x',
        'reference_answer' => 'Paris', 'options' => ['London', 'Paris'], 'status' => 'approved', 'generated_by' => 'manual',
    ]);

    $this->post("/questions/{$question->id}/attempts", ['answer' => 'Paris', 'difficulty' => 'easy'])
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('attempt.is_correct', true)
            ->where('attempt.score', 100)
            ->where('attempt.feedback', null));

    expect(Attempt::where('question_id', $question->id)->first()->feedback)->toBeNull();
});

it('grades an incorrect MCQ answer locally as incorrect', function () {
    $this->app->instance(AiProvider::class, throwingAiProvider());
    $topic = makeAttemptTopic();
    $question = $topic->questions()->create([
        'type' => 'mcq', 'difficulty' => 'easy', 'prompt' => 'x',
        'reference_answer' => 'Paris', 'options' => ['London', 'Paris'], 'status' => 'approved', 'generated_by' => 'manual',
    ]);

    $this->post("/questions/{$question->id}/attempts", ['answer' => 'London', 'difficulty' => 'easy'])
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('attempt.is_correct', false)
            ->where('attempt.score', 0));
});

it('still grades short-answer questions via the AI provider', function () {
    $topic = makeAttemptTopic();
    $question = $topic->questions()->create([
        'type' => 'short_answer', 'difficulty' => 'easy', 'prompt' => 'x',
        'reference_answer' => 'y', 'status' => 'approved', 'generated_by' => 'manual',
    ]);

    $this->app->instance(AiProvider::class, new class implements AiProvider
    {
        public function generateQuestions($topic, string $difficulty, int $count): array
        {
            return [];
        }

        public function gradeAnswer($question, string $answer): array
        {
            return ['is_correct' => true, 'score' => 90, 'feedback' => 'well reasoned'];
        }

        public function explainCodeResult($question, string $code, array $executionResult): string
        {
            return 'ok';
        }
    });

    $this->post("/questions/{$question->id}/attempts", ['answer' => 'y-ish', 'difficulty' => 'easy'])
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('attempt.is_correct', true)
            ->where('attempt.score', 90)
            ->where('attempt.feedback', 'well reasoned'));
});

it('fetches AI feedback for an MCQ attempt on demand', function () {
    $topic = makeAttemptTopic();
    $question = $topic->questions()->create([
        'type' => 'mcq', 'difficulty' => 'easy', 'prompt' => 'x',
        'reference_answer' => 'Paris', 'options' => ['London', 'Paris'], 'status' => 'approved', 'generated_by' => 'manual',
    ]);
    $attempt = $question->attempts()->create([
        'submitted_answer' => 'Paris', 'is_correct' => true, 'score' => 100, 'feedback' => null,
    ]);

    $fakeAi = new class implements AiProvider
    {
        public int $calls = 0;

        public function generateQuestions($topic, string $difficulty, int $count): array
        {
            return [];
        }

        public function gradeAnswer($question, string $answer): array
        {
            $this->calls++;

            return ['is_correct' => true, 'score' => 100, 'feedback' => 'Paris is the capital of France.'];
        }

        public function explainCodeResult($question, string $code, array $executionResult): string
        {
            return 'ok';
        }
    };
    $this->app->instance(AiProvider::class, $fakeAi);

    $this->post("/questions/{$question->id}/attempts/{$attempt->id}/feedback", ['difficulty' => 'easy'])
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('attempt.feedback', 'Paris is the capital of France.'));

    expect($attempt->fresh()->feedback)->toBe('Paris is the capital of France.');

    // A second request should not call the AI provider again since feedback is already saved.
    $this->post("/questions/{$question->id}/attempts/{$attempt->id}/feedback", ['difficulty' => 'easy'])
        ->assertOk();

    expect($fakeAi->calls)->toBe(1);
});

it('rejects on-demand feedback for non-MCQ attempts', function () {
    $this->app->instance(AiProvider::class, throwingAiProvider());
    $topic = makeAttemptTopic();
    $question = $topic->questions()->create([
        'type' => 'short_answer', 'difficulty' => 'easy', 'prompt' => 'x',
        'reference_answer' => 'y', 'status' => 'approved', 'generated_by' => 'manual',
    ]);
    $attempt = $question->attempts()->create([
        'submitted_answer' => 'y', 'is_correct' => true, 'score' => 100, 'feedback' => 'already graded',
    ]);

    $this->post("/questions/{$question->id}/attempts/{$attempt->id}/feedback", ['difficulty' => 'easy'])
        ->assertStatus(422);
});
