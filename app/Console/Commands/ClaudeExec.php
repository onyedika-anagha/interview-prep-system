<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

/**
 * Runs the `claude` CLI in a fresh, plain PHP process. On Windows, spawning
 * the (Bun-compiled) claude binary directly from the built-in dev server's
 * request-handling process (cli-server SAPI) crashes it with
 * STATUS_STACK_BUFFER_OVERRUN — a freshly-started CLI-SAPI process doesn't
 * have this problem, so ClaudeCli::run() shells out to this command instead
 * of spawning claude directly.
 */
class ClaudeExec extends Command
{
    protected $signature = 'claude:exec';

    protected $description = 'Run the claude CLI with a prompt read from stdin';

    public function handle(): int
    {
        $prompt = stream_get_contents(STDIN);

        $binary = config('services.claude.cli_path', 'claude');
        $command = escapeshellarg($binary).' -p '.escapeshellarg($prompt).' --output-format json';

        $result = Process::timeout(120)->run($command);

        fwrite(STDOUT, $result->output());
        fwrite(STDERR, $result->errorOutput());

        return $result->exitCode() ?? self::FAILURE;
    }
}
