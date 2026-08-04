<?php

namespace App\Console\Commands;

use App\Models\Topic;
use App\Services\AiProvider;
use Illuminate\Console\Command;

class GenerateQuestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'questions:generate {topic : Topic slug} {difficulty} {count=5}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new questions for a topic via the configured AI provider';

    public function handle(AiProvider $ai): int
    {
        $topic = Topic::where('slug', $this->argument('topic'))->first();

        if (! $topic) {
            $this->error("No topic found with slug \"{$this->argument('topic')}\".");

            return self::FAILURE;
        }

        $difficulty = $this->argument('difficulty');
        $count = (int) $this->argument('count');

        $this->info("Generating {$count} \"{$difficulty}\" questions for \"{$topic->name}\"...");

        $questions = $ai->generateQuestions($topic, $difficulty, $count);

        foreach ($questions as $question) {
            $topic->questions()->create([
                'type' => $question['type'],
                'difficulty' => $question['difficulty'] ?? $difficulty,
                'prompt' => $question['prompt'],
                'reference_answer' => $question['reference_answer'],
                'language' => $question['language'] ?? null,
                'test_cases' => $question['test_cases'] ?? null,
                'status' => 'draft',
                'generated_by' => config('services.ai.provider'),
            ]);
        }

        $this->info(count($questions).' question(s) created as drafts.');

        return self::SUCCESS;
    }
}
