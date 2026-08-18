<?php

namespace App\Services;

use Illuminate\Process\Exceptions\ProcessTimedOutException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CodeExecutionService
{
    public const DEFAULT_TIMEOUT_SECONDS = 5;

    private const EXTENSIONS = [
        'javascript' => 'js',
        'php' => 'php',
    ];

    private const COMMANDS = [
        'javascript' => ['node'],
        'php' => ['php', '-d', 'display_errors=stderr', '-d', 'display_startup_errors=0'],
    ];

    public function __construct(
        private readonly int $timeoutSeconds = self::DEFAULT_TIMEOUT_SECONDS,
    ) {}

    /**
     * Run submitted code against each test case and report pass/fail per case.
     *
     * @param  array<int, array{input: mixed, expected_output: mixed}>  $testCases
     * @return array<int, array{input: mixed, expected_output: mixed, actual_output: mixed, passed: bool, error: ?string}>
     */
    public function run(string $language, string $code, array $testCases): array
    {
        if (! isset(self::COMMANDS[$language])) {
            throw new InvalidArgumentException("Unsupported language: {$language}");
        }

        return array_map(fn (array $case) => $this->runCase($language, $code, $case), $testCases);
    }

    private function runCase(string $language, string $code, array $case): array
    {
        // ponytail: subprocess isolation only (fresh temp dir + timeout) — no OS-level
        // network/firewall sandboxing. Upgrade to a container runner if untrusted code matters.
        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'code-exec-'.Str::random(16);
        File::makeDirectory($dir, recursive: true);

        try {
            $file = $dir.DIRECTORY_SEPARATOR.'solution.'.self::EXTENSIONS[$language];
            File::put($file, $code);

            $input = json_encode($case['input'] ?? null);

            $result = Process::path($dir)
                ->timeout($this->timeoutSeconds)
                ->input($input)
                ->run([...self::COMMANDS[$language], $file]);

            $actualRaw = trim($result->output());
            $expected = $case['expected_output'] ?? null;
            $actual = json_decode($actualRaw, true);
            $errorOutput = trim($result->errorOutput());

            return [
                'input' => $case['input'] ?? null,
                'expected_output' => $expected,
                'actual_output' => $actualRaw === '' ? null : ($actual ?? $actualRaw),
                'passed' => $result->successful() && json_encode($actual) === json_encode($expected),
                'error' => $result->successful() ? null : ($errorOutput !== '' ? $errorOutput : 'Process exited with a non-zero status.'),
            ];
        } catch (ProcessTimedOutException) {
            return [
                'input' => $case['input'] ?? null,
                'expected_output' => $case['expected_output'] ?? null,
                'actual_output' => null,
                'passed' => false,
                'error' => 'Execution timed out after '.$this->timeoutSeconds.' seconds.',
            ];
        } finally {
            File::deleteDirectory($dir);
        }
    }
}
