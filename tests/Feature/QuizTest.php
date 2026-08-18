<?php

use App\Models\Attempt;
use App\Models\Question;
use App\Models\Topic;

it('shows the next approved question with its total count and review status', function () {
    $topic = Topic::create(['name' => 'DSA', 'category' => 'general', 'description' => 'x']);
    $topic->questions()->create([
        'type' => 'short_answer', 'difficulty' => 'easy', 'prompt' => 'x',
        'reference_answer' => 'y', 'status' => 'approved', 'generated_by' => 'manual',
    ]);
    $topic->questions()->create([
        'type' => 'short_answer', 'difficulty' => 'easy', 'prompt' => 'draft, not shown',
        'reference_answer' => 'y', 'status' => 'draft', 'generated_by' => 'manual',
    ]);

    $this->get("/topics/{$topic->slug}/quiz?difficulty=easy")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('question.prompt', 'x')
            ->where('question.review_status', 'new')
            ->where('totalQuestions', 1)
            ->has('executionTimeoutSeconds'));
});

it('runs a coding submission against test cases without recording an attempt', function () {
    $topic = Topic::create(['name' => 'DSA', 'category' => 'general', 'description' => 'x']);
    $php = <<<'PHP'
        <?php
        $data = json_decode(file_get_contents('php://stdin'), true);
        echo json_encode($data['a'] + $data['b']);
        PHP;

    $question = $topic->questions()->create([
        'type' => 'coding', 'difficulty' => 'easy', 'prompt' => 'add two numbers',
        'reference_answer' => $php, 'language' => 'php', 'status' => 'approved', 'generated_by' => 'manual',
        'test_cases' => [['input' => ['a' => 2, 'b' => 3], 'expected_output' => 5]],
    ]);

    $this->post("/questions/{$question->id}/run", ['answer' => $php, 'difficulty' => 'easy'])
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('runResult.0.passed', true));

    expect(Attempt::where('question_id', $question->id)->count())->toBe(0);
});

it('rejects running a non-coding question', function () {
    $topic = Topic::create(['name' => 'DSA', 'category' => 'general', 'description' => 'x']);
    $question = $topic->questions()->create([
        'type' => 'short_answer', 'difficulty' => 'easy', 'prompt' => 'x',
        'reference_answer' => 'y', 'status' => 'approved', 'generated_by' => 'manual',
    ]);

    $this->post("/questions/{$question->id}/run", ['answer' => 'anything', 'difficulty' => 'easy'])
        ->assertStatus(422);
});

it('filters quiz questions to a single type', function () {
    $topic = Topic::create(['name' => 'DSA', 'category' => 'general', 'description' => 'x']);
    $topic->questions()->create([
        'type' => 'mcq', 'difficulty' => 'easy', 'prompt' => 'mcq question',
        'reference_answer' => 'y', 'options' => ['y', 'n'], 'status' => 'approved', 'generated_by' => 'manual',
    ]);
    $topic->questions()->create([
        'type' => 'coding', 'difficulty' => 'easy', 'prompt' => 'coding question',
        'reference_answer' => 'y', 'language' => 'php', 'status' => 'approved', 'generated_by' => 'manual',
    ]);

    $this->get("/topics/{$topic->slug}/quiz?difficulty=easy&type=coding")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('question.prompt', 'coding question')
            ->where('type', 'coding')
            ->where('totalQuestions', 1));
});

it('goes back to the previously shown question', function () {
    $topic = Topic::create(['name' => 'DSA', 'category' => 'general', 'description' => 'x']);
    $previous = $topic->questions()->create([
        'type' => 'short_answer', 'difficulty' => 'easy', 'prompt' => 'previous question',
        'reference_answer' => 'y', 'status' => 'approved', 'generated_by' => 'manual',
    ]);

    $this->get("/questions/{$previous->id}/back?difficulty=easy&exclude={$previous->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('question.prompt', 'previous question')
            ->where('excludeIds', []));
});
