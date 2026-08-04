<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Topic;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiApi implements AiProvider
{
    use BuildsAiPrompts;

    public function generateQuestions(Topic $topic, string $difficulty, int $count): array
    {
        return $this->parseJsonResponse($this->run($this->generateQuestionsPrompt($topic, $difficulty, $count)));
    }

    public function gradeAnswer(Question $question, string $answer): array
    {
        return $this->parseJsonResponse($this->run($this->gradeAnswerPrompt($question, $answer)));
    }

    public function explainCodeResult(Question $question, string $code, array $executionResult): string
    {
        return trim($this->run($this->explainCodeResultPrompt($question, $code, $executionResult)));
    }

    protected function run(string $prompt): string
    {
        $model = config('services.gemini.model');

        $response = Http::timeout(120)
            ->withHeader('x-goog-api-key', config('services.gemini.api_key'))
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException("Gemini API request failed: {$response->body()}");
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');

        if (! is_string($text)) {
            throw new RuntimeException("Gemini API returned an unexpected response shape: {$response->body()}");
        }

        return $text;
    }
}
