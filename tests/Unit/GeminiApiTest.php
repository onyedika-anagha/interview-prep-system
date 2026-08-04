<?php

use App\Models\Question;
use App\Models\Topic;
use App\Services\GeminiApi;
use Illuminate\Support\Facades\Http;

it('parses a successful response into an array', function () {
    $questions = json_encode([
        ['type' => 'mcq', 'difficulty' => 'easy', 'prompt' => 'What is PHP?', 'reference_answer' => 'A language', 'test_cases' => null],
    ]);

    Http::fake([
        '*' => Http::response([
            'candidates' => [
                ['content' => ['parts' => [['text' => $questions]]]],
            ],
        ]),
    ]);

    $topic = new Topic(['name' => 'PHP', 'description' => 'PHP basics']);

    $result = (new GeminiApi)->generateQuestions($topic, 'easy', 1);

    expect($result)->toBeArray()->toHaveCount(1)
        ->and($result[0]['type'])->toBe('mcq');
});

it('throws when the API responds with an error', function () {
    Http::fake([
        '*' => Http::response(['error' => 'bad request'], 400),
    ]);

    $question = new Question(['type' => 'mcq', 'difficulty' => 'easy', 'prompt' => 'x', 'reference_answer' => 'y']);

    (new GeminiApi)->gradeAnswer($question, 'my answer');
})->throws(RuntimeException::class, 'Gemini API request failed');

it('throws when the response has no candidate text', function () {
    Http::fake([
        '*' => Http::response(['candidates' => []]),
    ]);

    $question = new Question(['type' => 'mcq', 'difficulty' => 'easy', 'prompt' => 'x', 'reference_answer' => 'y']);

    (new GeminiApi)->gradeAnswer($question, 'my answer');
})->throws(RuntimeException::class, 'unexpected response shape');
