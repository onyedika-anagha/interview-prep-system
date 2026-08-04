## Relevant Files

- `database/migrations/xxxx_create_topics_table.php` - Topics table (name, auto-generated slug, category: stack/general).
- `database/migrations/xxxx_create_questions_table.php` - Questions table (type, difficulty, prompt, test cases, status).
- `database/migrations/xxxx_create_attempts_table.php` - Attempts table (submitted answer, score, feedback, execution result).
- `database/migrations/xxxx_create_resources_table.php` - Resources table (study material per topic).
- `app/Models/Topic.php` - Topic model; slug auto-generation on create per the repo's slug rule.
- `app/Models/Question.php` - Question model with topic relationship.
- `app/Models/Attempt.php` - Attempt model with question relationship.
- `app/Models/Resource.php` - Resource model with topic relationship.
- `database/seeders/TopicSeeder.php` - Seeds initial stack + general topics.
- `app/Services/AiProvider.php` - Interface implemented by `ClaudeCli` and `GeminiApi`; resolved from the `AI_PROVIDER` config value.
- `app/Services/ClaudeCli.php` - Wraps headless `claude -p` calls for question generation, grading, and explanations.
- `app/Services/GeminiApi.php` - Calls the Gemini API (via `Http` facade) for the same operations, used when `AI_PROVIDER=gemini`.
- `tests/Unit/ClaudeCliTest.php` - Unit tests for `ClaudeCli.php` (mocking the Process facade).
- `tests/Unit/GeminiApiTest.php` - Unit tests for `GeminiApi.php` (mocking the Http facade).
- `app/Console/Commands/GenerateQuestions.php` - Artisan command to trigger question generation for a topic.
- `app/Services/CodeExecutionService.php` - Runs submitted code (JS/PHP) against test cases in a restricted subprocess.
- `tests/Unit/CodeExecutionServiceTest.php` - Unit tests covering pass/fail/timeout/crash scenarios.
- `app/Http/Controllers/TopicController.php` - Lists topics grouped by category.
- `app/Http/Controllers/QuizController.php` - Starts a quiz, selects questions (new + due-for-review).
- `app/Http/Controllers/AttemptController.php` - Handles answer submission, grading, and storing attempts.
- `app/Http/Controllers/ProgressController.php` - Per-topic accuracy, attempt counts, streaks.
- `app/Services/ReviewQueue.php` - Leitner-style due-date scheduling for missed questions.
- `tests/Unit/ReviewQueueTest.php` - Unit tests for review-queue scheduling.
- `tests/Feature/QuizFlowTest.php` - Feature test covering the full quiz → submit → grade → result flow.
- `routes/web.php` - Registers topic/quiz/attempt/progress routes.
- `routes/api.php` - Local-only JSON API the MCP server calls (list/add/update topics, questions, resources, attempts, progress).
- `tests/Feature/McpApiTest.php` - Feature tests for the local API the MCP server depends on.
- `resources/js/pages/topics/index.tsx` - Topic list page.
- `resources/js/pages/quiz/show.tsx` - Quiz-taking page; composes question/answer components only.
- `resources/js/pages/quiz/result.tsx` - Result page after submitting an answer.
- `resources/js/pages/progress/index.tsx` - Progress dashboard page.
- `resources/js/components/quiz/question-card.tsx` - Renders a single question.
- `resources/js/components/quiz/answer-form.tsx` - Answer input (text or code) + submit.
- `resources/js/components/quiz/result-feedback.tsx` - Pass/fail + Claude's explanation.
- `resources/js/components/progress/topic-stats.tsx` - Per-topic stat tile.
- `resources/js/hooks/use-quiz-session.ts` - Client-side quiz session state, extracted out of `quiz/show.tsx`.
- `mcp-server/package.json` - MCP server project (Node/TypeScript, `@modelcontextprotocol/sdk`).
- `mcp-server/src/index.ts` - MCP server entrypoint.
- `mcp-server/src/tools/topics.ts` - `list_topics` tool.
- `mcp-server/src/tools/questions.ts` - `add_question` / `update_question` tools.
- `mcp-server/src/tools/resources.ts` - `add_resource` tool.
- `mcp-server/src/tools/progress.ts` - `list_attempts` / `get_progress` tools.
- `mcp-server/README.md` - Registration instructions for Claude Code (`claude mcp add`) and Claude Desktop config.

### Notes

- Backend tests use Pest (already in `composer.json`) — run with `./vendor/bin/pest` or `php artisan test`, optionally with a path.
- No JS test runner is configured in this starter kit; skip frontend unit tests unless specifically requested (YAGNI) — rely on manual testing in the browser per this repo's UI-change guidance.
- The MCP server is a separate Node process (`mcp-server/`) — it talks to the Laravel app over `routes/api.php`, it does not touch the MySQL database directly, so validation and slug generation stay in one place.
- Follow `.claude/rules/code-separation.md` for all frontend work and `.claude/rules/slug-rule.md` for the `Topic` slug (auto-generated, never an editable field).

## Instructions for Completing Tasks

**IMPORTANT:** As you complete each task, you must check it off in this markdown file by changing `- [ ]` to `- [x]`. This helps track progress and ensures you don't skip any steps. Update the file after completing each sub-task, not just after completing an entire parent task.

## Tasks

- [x] 0.0 Create feature branch
  - [x] 0.1 Create and checkout a new branch for this feature (`git checkout -b feature/interview-prep-system`)
- [x] 1.0 Database schema & models
  - [x] 1.1 Migration: `topics` (name, auto-generated slug, category enum `stack`/`general`, description, timestamps)
  - [x] 1.2 Migration: `questions` (topic_id FK, type enum `mcq`/`short_answer`/`coding`, difficulty, prompt, reference_answer, test_cases JSON nullable, status enum `draft`/`approved`, generated_by, timestamps)
  - [x] 1.3 Migration: `attempts` (question_id FK, submitted_answer, is_correct, score, feedback, execution_result JSON nullable, timestamps)
  - [x] 1.4 Migration: `resources` (topic_id FK, title, content, url nullable, generated_by, timestamps)
  - [x] 1.5 Models `Topic`, `Question`, `Attempt`, `Resource` with relationships and slug auto-generation on `Topic` (model `booted()` hook, unique suffix on collision, per the slug rule)
  - [x] 1.6 Seeder: initial stack topics (PHP/Laravel, JS/React) and general topics (DSA, system design)
  - [x] 1.7 Run migrations + seeder locally and verify schema (`php artisan migrate`, `php artisan db:seed`, spot-check via `php artisan tinker`)
- [x] 2.0 AI provider integration service (Claude CLI + Gemini API)
  - [x] 2.1 `AiProvider` interface: `generateQuestions()`, `gradeAnswer()`, `explainCodeResult()`; bound to `ClaudeCli` or `GeminiApi` in a service provider based on `config('services.ai_provider')` (from `AI_PROVIDER` env, default `claude`)
  - [x] 2.2 `ClaudeCli` service wrapping `Process::run(['claude', '-p', ..., '--output-format', 'json'])`, parsing the JSON result and throwing on a non-zero exit code
  - [x] 2.3 `GeminiApi` service calling the Gemini API via the `Http` facade with `GEMINI_API_KEY`, parsing the response into the same shape as `ClaudeCli`
  - [x] 2.4 `generateQuestions(Topic $topic, string $difficulty, int $count): array` — prompts for structured question data, validates the returned shape before use (implemented by both providers)
  - [x] 2.5 `gradeAnswer(Question $question, string $answer): array` — for `mcq`/`short_answer` questions, returns `is_correct`/`score`/`feedback` (implemented by both providers)
  - [x] 2.6 `explainCodeResult(Question $question, string $code, array $executionResult): string` — feeds sandbox test results to the AI provider for a plain-language explanation of failures and fixes (implemented by both providers)
  - [x] 2.7 Artisan command `questions:generate {topic} {difficulty} {count=5}` to trigger generation from the CLI, using the configured `AiProvider`
  - [x] 2.8 Unit tests for `ClaudeCli` (mocking the `Process` facade: success, non-zero exit, malformed JSON) and `GeminiApi` (mocking the `Http` facade: success, error response, malformed JSON)
- [x] 3.0 Sandboxed code-execution service
  - [x] 3.1 `CodeExecutionService` — runs submitted code in a restricted subprocess (fresh temp dir, timeout, no network access) for JavaScript (`node`) and PHP
  - [x] 3.2 Test-case runner: injects a question's `test_cases` into the submitted code, captures stdout/exit code per case
  - [x] 3.3 Timeout and resource limits — kill runaway processes, cap max execution time per submission
  - [x] 3.4 Unit tests: pass, fail, timeout, and crash scenarios for both languages

  Note: added a `language` enum column (`javascript`/`php`, nullable) to `questions` — needed by the executor and the coding-answer editor but missing from the original schema. Execution contract: coding solutions read one JSON value from stdin and print one JSON value to stdout; `BuildsAiPrompts` instructs generated solutions accordingly.
- [x] 4.0 Web UI — topic browsing, quiz flow, results
  - [x] 4.1 `TopicController` + route: list topics grouped by category → `resources/js/pages/topics/index.tsx`
  - [x] 4.2 `QuizController` + route: start a quiz for a topic + difficulty, selecting a mix of new and due-for-review questions
  - [x] 4.3 `resources/js/pages/quiz/show.tsx` — composes `question-card.tsx` + `answer-form.tsx`; no inline question/answer markup in the page itself
  - [x] 4.4 `resources/js/components/quiz/question-card.tsx` and `answer-form.tsx` (text input for mcq/short-answer, code editor textarea for coding questions)
  - [x] 4.5 `AttemptController`: submit answer → route to grading (`AiProvider::gradeAnswer` for mcq/short-answer; `CodeExecutionService` + `AiProvider::explainCodeResult` for coding) → persist `Attempt`
  - [x] 4.6 `resources/js/pages/quiz/result.tsx` + `result-feedback.tsx` — pass/fail and Claude's explanation
  - [x] 4.7 `resources/js/hooks/use-quiz-session.ts` — client-side quiz session state (current question index, collected answers) extracted out of `quiz/show.tsx`

  Implementation notes:
  - No separate "quiz session" table: the exclude-list of already-seen question IDs round-trips through the URL/POST body across `QuizController`→`AttemptController`→`quiz/result`→next `QuizController` visit, so quiz progress survives reloads without any server-side session state.
  - Added `AutoLoginLocalUser` middleware (auto-logs in the one seeded user) scoped **only** to `topics.*`/`quiz.*`/`attempts.*` routes in `routes/web.php`, per FR14 ("no login"). It is intentionally NOT global — an earlier version applied it to the whole `web` middleware group and broke the starter kit's own auth tests (`assertGuest()` after a failed login started failing because the request got auto-authenticated anyway). Scoping it to just the new routes fixed that; `php artisan test` is back to 41/41 passing.
  - Verified via `npx tsc --noEmit`, `eslint`, `npm run build`, and a curl-driven Inertia smoke test (no headless browser was available in this environment — `chromium-cli` wasn't installed — so this wasn't visually confirmed in a real browser window). `/topics` and `/topics/{slug}/quiz` render correctly end-to-end, including the auto-login split (topics routes 200, `/dashboard` still 302s to login).
  - **Known issue found in testing, not fixed (upstream/environment, not app code):** submitting an answer that reaches `ClaudeCli` (mcq/short-answer grading, or the coding-explanation step) 500s when the app runs under `php artisan serve` on this Windows machine — the `claude` CLI subprocess crashes with a Windows `STATUS_STACK_BUFFER_OVERRUN`. Isolated by direct comparison: `ClaudeCli::gradeAnswer()` called straight from `php artisan tinker` works perfectly (proves the PHP-side prompt/parsing logic is correct), and `CodeExecutionService` spawning plain `node`/`php` scripts through the same `php artisan serve` process works fine too — only the `claude` CLI itself crashes when spawned this way. Looks like a `claude`-CLI-on-Windows console-handling quirk under PHP's built-in dev server, not something fixable from this codebase. Workaround: set `AI_PROVIDER=gemini` (pure HTTP, no subprocess) for local Windows dev, or serve behind a real web server instead of `php artisan serve`.
- [x] 5.0 Progress tracking & review queue
  - [x] 5.1 `ProgressController` + route: per-topic accuracy, attempt counts, current streak
  - [x] 5.2 `resources/js/pages/progress/index.tsx` + `topic-stats.tsx` component
  - [x] 5.3 `ReviewQueue` service: Leitner-style bucket per question, due-date calculation on wrong answers
  - [x] 5.4 Wire `ReviewQueue` into `QuizController`'s question-selection logic so due questions are prioritized
  - [x] 5.5 Unit tests for review-queue scheduling (bucket promotion/demotion, due-date math)

  Note: `QuizController`'s selection query (built in 4.2) already ordered by due-then-new-then-not-yet-due, so "wiring in" 5.4 was just making `ReviewQueue::recordResult()` actually populate `review_bucket`/`review_due_at` — done via one call in `AttemptController::store()`. Buckets 1–5, delays 1/2/4/7/14 days; wrong answers always drop to bucket 1.
- [x] 6.0 MCP server + Claude registration
  - [x] 6.1 Scaffold `mcp-server/` (package.json, tsconfig, `@modelcontextprotocol/sdk` dependency)
  - [x] 6.2 Local-only routes in `routes/api.php`: list topics, add/update question, add resource, list attempts, get progress summary — reusing the existing controllers/services rather than duplicating logic
  - [x] 6.3 `mcp-server/src/tools/*.ts` — implement `list_topics`, `add_question`, `update_question`, `add_resource`, `list_attempts`, `get_progress` tools, each calling the local Laravel API
  - [x] 6.4 `mcp-server/src/index.ts` — MCP server entrypoint wiring the tools together
  - [x] 6.5 `mcp-server/README.md` — registration steps for Claude Code (`claude mcp add`) and the Claude Desktop config JSON
  - [ ] 6.6 Manual end-to-end check: from a Claude chat session, add a question via MCP and confirm it appears in the web UI without restarting the app

  Implementation notes:
  - No build step: the MCP server runs `.ts` files directly (`node src/index.ts`) — Node 22+ strips TypeScript types natively, so `tsconfig.json` is type-checking-only (`npm run typecheck`), not a compile step. Extracted a shared `ProgressStats` service so `ProgressController` (web) and `Api\ProgressController` (MCP) don't duplicate the accuracy/streak calculation.
  - `POST /api/questions` defaults `status` to `approved` and `generated_by` to `claude` when omitted, so a question added via MCP shows up in a quiz immediately — matching the PRD's "no restart needed" success metric.
  - Covered by `tests/Feature/McpApiTest.php` (7 tests, all passing) — topic listing, add/update question, add resource, list/filter attempts, get progress.
  - **6.6 not cleanly completed:** ran `claude mcp add interview-prep -- node .../mcp-server/src/index.ts` and `claude mcp list` reported "Failed to connect" (alongside two unrelated pre-existing MCP servers in this environment that also failed their health check, so this may be a sandbox networking/stdio quirk rather than a bug in the server itself — not root-caused before time ran out). The server passes its own typecheck and the HTTP layer it calls is fully tested; what's unverified is the live stdio handshake with a real Claude client. Worth a fresh manual check: `claude mcp add interview-prep -- node <path>/mcp-server/src/index.ts`, then ask Claude to list topics or add a question, with `php artisan serve` running.
