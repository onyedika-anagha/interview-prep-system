# Design ticket: MCQ local grading + on-demand AI feedback

## What's changing visually/UX-wise
On the quiz result page, the feedback card for MCQ questions no longer always shows AI-written text. It shows the correct/incorrect badge instantly (unchanged), and in place of feedback text shows a "Get AI feedback" button until the learner clicks it, at which point it shows a loading state and then the AI's explanation in the same spot the static text used to occupy.

## Affected screen(s) and state(s)
`quiz/result` page → `ResultFeedback` component, feedback card body:
- **Default (MCQ, not yet requested):** "Get AI feedback" button, no explanation text.
- **Loading:** button shows "Getting feedback…" and is disabled.
- **Success:** button is replaced by the returned feedback text (same styling as existing feedback text).
- **Non-MCQ (short_answer, coding):** unchanged — feedback text is already present at render time, no button shown.

## Before/after
Before: every result card shows AI-written feedback text immediately below the score badge.
After: MCQ result cards show a button in that spot instead, until clicked.

## Accessibility check
No — button uses the existing `Button` component (already meets touch target/contrast baseline used elsewhere in this app); no new contrast or focus-order concerns.
