# Security ticket: MCQ local grading + on-demand AI feedback

## Confirmation of no sensitive surface
This change does not touch secrets or credentials, and does not introduce a new third-party integration — it adds one more call site to the already-integrated `AiProvider` (Claude/Gemini, unchanged) and one new route inside the existing `AutoLoginLocalUser`-gated group used by every other quiz/attempt route in this app. The new `questions/{question}/attempts/{attempt}/feedback` endpoint validates that the attempt belongs to the given question before acting on it (404 otherwise) and is rejected for non-MCQ questions (422), matching the existing validation style used by `QuizController::run`.

## Dependency change check
None — no dependencies added or bumped.
