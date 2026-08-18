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
        // Routed through a fresh `php artisan claude:exec` process rather than
        // spawned directly: on Windows, launching the claude binary straight
        // from the built-in dev server's request-handling process crashes it
        // (STATUS_STACK_BUFFER_OVERRUN). A plain, freshly-started PHP process
        // doesn't have this problem, so ClaudeExec does the actual spawning.
        // -d display_errors=stderr keeps PHP warnings (e.g. extension version
        // notices) out of stdout, which must contain nothing but the JSON
        // claude:exec prints.
        $command = escapeshellarg(PHP_BINARY).' -d display_errors=stderr '.escapeshellarg(base_path('artisan')).' claude:exec';

        // `php artisan serve` starts its worker with most environment
        // variables stripped (see ServeCommand::$passthroughVariables), and
        // Symfony's default env-inheritance for spawned processes filters
        // against $_SERVER, which is similarly thin here. Symfony/Windows
        // needs a real TEMP dir to redirect a child's stdout/stderr, so pass
        // these through explicitly rather than relying on inheritance.
        $env = array_filter([
            'TEMP' => getenv('TEMP'),
            'TMP' => getenv('TMP'),
            'USERPROFILE' => getenv('USERPROFILE'),
            'COMSPEC' => getenv('COMSPEC'),
            'APPDATA' => getenv('APPDATA'),
            'LOCALAPPDATA' => getenv('LOCALAPPDATA'),
            'HOMEDRIVE' => getenv('HOMEDRIVE'),
            'HOMEPATH' => getenv('HOMEPATH'),
            'PATH' => getenv('PATH'),
            'SystemRoot' => getenv('SystemRoot'),
        ]);

        $result = Process::timeout(120)->env($env)->input($prompt)->run($command);

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
