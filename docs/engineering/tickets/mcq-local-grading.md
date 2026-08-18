# Engineering ticket: MCQ local grading + on-demand AI feedback

## What changed
- `app/Http/Controllers/AttemptController.php`: `store()` now grades `mcq` questions with a local string comparison against `reference_answer` (score 100/0, `feedback` null) instead of calling `AiProvider::gradeAnswer`. `short_answer` and `coding` branches are unchanged. Extracted the shared `Inertia::render('quiz/result', ...)` call into a private `render()` helper, and added `id` to the attempt payload. Added `feedback()` — a new action that fetches AI feedback for an existing MCQ attempt on demand (idempotent: skips the AI call if `feedback` is already set), guarded to MCQ-only (422 otherwise) and to the attempt actually belonging to the given question (404 otherwise).
- `routes/web.php`: added `POST questions/{question}/attempts/{attempt}/feedback` inside the existing `AutoLoginLocalUser` group.
- `resources/js/types/interview-prep.ts`: `Attempt.feedback` is now `string | null`; added `Attempt.id`.
- `resources/js/hooks/use-attempt-feedback.ts` (new): holds the on-demand feedback request's loading state and posts to the new route via Inertia's `router.post`, mirroring `use-quiz-session.ts`'s `runCode`.
- `resources/js/components/quiz/result-feedback.tsx`: shows a "Get AI feedback" button in place of the feedback text when the question is MCQ and `attempt.feedback` is still null; unchanged for short_answer/coding, where feedback is always already present.
- `resources/js/pages/quiz/result.tsx`: wires `useAttemptFeedback` into `ResultFeedback`.

## Test added or updated
`tests/Feature/AttemptTest.php` (new): MCQ correct/incorrect answers are graded locally with the AI provider bound to a throwing stub (proves no AI call happens); short-answer grading via AI is unchanged; the on-demand feedback endpoint fetches and persists feedback once and is idempotent on a second request; the endpoint 422s for non-MCQ attempts.

## Changelog entry
Grade MCQ answers locally instead of via AI, and add an on-demand "Get AI feedback" action for MCQ results.
