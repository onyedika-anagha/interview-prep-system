<?php

use App\Models\Attempt;
use App\Models\Question;
use App\Models\Topic;

it('lists topics', function () {
    Topic::create(['name' => 'DSA', 'category' => 'general', 'description' => 'x']);

    $this->getJson('/api/topics')
        ->assertOk()
        ->assertJsonPath('topics.0.name', 'DSA')
        ->assertJsonStructure(['topics' => [['id', 'name', 'slug', 'category', 'description']]]);
});

it('adds a question to a topic by slug, defaulting to approved and generated_by claude', function () {
    $topic = Topic::create(['name' => 'DSA', 'category' => 'general', 'description' => 'x']);

    $this->postJson('/api/questions', [
        'topic_slug' => $topic->slug,
        'type' => 'short_answer',
        'difficulty' => 'easy',
        'prompt' => 'What is Big O?',
        'reference_answer' => 'A complexity bound.',
    ])->assertCreated()
        ->assertJsonPath('question.status', 'approved')
        ->assertJsonPath('question.generated_by', 'claude');

    expect(Question::where('topic_id', $topic->id)->count())->toBe(1);
});

it('rejects a question for an unknown topic slug', function () {
    $this->postJson('/api/questions', [
        'topic_slug' => 'does-not-exist',
        'type' => 'short_answer',
        'difficulty' => 'easy',
        'prompt' => 'x',
        'reference_answer' => 'y',
    ])->assertStatus(422);
});

it('updates an existing question', function () {
    $topic = Topic::create(['name' => 'DSA', 'category' => 'general', 'description' => 'x']);
    $question = $topic->questions()->create([
        'type' => 'short_answer', 'difficulty' => 'easy', 'prompt' => 'old',
        'reference_answer' => 'y', 'status' => 'draft', 'generated_by' => 'manual',
    ]);

    $this->patchJson("/api/questions/{$question->id}", ['prompt' => 'new', 'status' => 'approved'])
        ->assertOk()
        ->assertJsonPath('question.prompt', 'new')
        ->assertJsonPath('question.status', 'approved');
});

it('adds a resource to a topic', function () {
    $topic = Topic::create(['name' => 'DSA', 'category' => 'general', 'description' => 'x']);

    $this->postJson('/api/resources', [
        'topic_slug' => $topic->slug,
        'title' => 'Big O cheat sheet',
        'url' => 'https://example.com/big-o',
    ])->assertCreated()
        ->assertJsonPath('resource.title', 'Big O cheat sheet');
});

it('lists attempts, optionally filtered by topic', function () {
    $topic = Topic::create(['name' => 'DSA', 'category' => 'general', 'description' => 'x']);
    $question = $topic->questions()->create([
        'type' => 'short_answer', 'difficulty' => 'easy', 'prompt' => 'x',
        'reference_answer' => 'y', 'status' => 'approved', 'generated_by' => 'manual',
    ]);
    Attempt::create(['question_id' => $question->id, 'submitted_answer' => 'a', 'is_correct' => true, 'score' => 100]);

    $this->getJson("/api/attempts?topic_slug={$topic->slug}")
        ->assertOk()
        ->assertJsonCount(1, 'attempts');

    $this->getJson('/api/attempts')
        ->assertOk()
        ->assertJsonCount(1, 'attempts');

    $this->getJson('/api/attempts?topic_slug=does-not-exist')
        ->assertStatus(422);
});

it('returns per-topic progress', function () {
    $topic = Topic::create(['name' => 'DSA', 'category' => 'general', 'description' => 'x']);
    $question = $topic->questions()->create([
        'type' => 'short_answer', 'difficulty' => 'easy', 'prompt' => 'x',
        'reference_answer' => 'y', 'status' => 'approved', 'generated_by' => 'manual',
    ]);
    Attempt::create(['question_id' => $question->id, 'submitted_answer' => 'a', 'is_correct' => true, 'score' => 100]);

    $this->getJson('/api/progress')
        ->assertOk()
        ->assertJsonPath('progress.0.accuracy', 100)
        ->assertJsonPath('progress.0.attempt_count', 1);
});
