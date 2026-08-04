<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Topic;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class ClaudeCli implements AiProvider
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
        $result = Process::timeout(120)->run(['claude', '-p', $prompt, '--output-format', 'json']);

        if ($result->failed()) {
            throw new RuntimeException("Claude CLI failed (exit {$result->exitCode()}): {$result->errorOutput()} | stdout: {$result->output()}");
        }

        $decoded = json_decode($result->output(), true);

        if (! is_array($decoded) || ! array_key_exists('result', $decoded)) {
            throw new RuntimeException("Claude CLI returned an unexpected response shape: {$result->output()}");
        }

        return $decoded['result'];
    }
}
