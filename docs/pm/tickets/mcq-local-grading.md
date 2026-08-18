# Ticket: MCQ local grading + on-demand AI feedback

## Change
MCQ attempts are currently graded by sending the question and answer to the AI provider on every submission, even though the correct option (`reference_answer`) is already stored and the submitted answer is always an exact match to one of `options`. Switch MCQ grading to an instant local string comparison (no AI call on submit), and add an on-demand "Get AI feedback" action the learner can trigger afterward if they want an explanation.

## Why
Wasted AI usage/cost and unnecessary latency on every MCQ submission for a result that's already deterministic from stored data.

## Acceptance Criteria
- Submitting an MCQ answer returns `is_correct`/`score` computed by comparing the answer to `reference_answer`, with no AI provider call made during submission.
- The result page shows correct/incorrect immediately; if the learner requests AI feedback, it is fetched on demand and then displayed.
- Short-answer and coding grading behavior is unchanged (short-answer still auto-grades via AI; coding still executes against test cases and auto-explains via AI).

## Risk flag
No — reuses the existing `AiProvider` integration and the existing auto-authenticated local-user route group; no schema change, no new external service.
