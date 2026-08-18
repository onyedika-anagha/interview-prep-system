# Ticket: Full-width, grid-based layout for quiz/manage pages + MCQ option styling

## Change
`quiz/show` and `questions/manage` were the only two pages using `mx-auto max-w-*` centering, inconsistent with the rest of the app (dashboard/topics/progress use full-width layouts). Removed the centering and used CSS grid to make better use of the width: coding questions get a two-column split (prompt/test cases next to the editor); the three question-creation forms on the manage page sit in a grid instead of stacking. A first pass made *all* question types use the wide two-column split, which looked broken for MCQ (a 4-option radio list stranded next to a huge empty area) — corrected to keep MCQ/short-answer in a focused single centered column, since a wide layout only helps when there's a code editor to fill the second column. Also redesigned MCQ options from bare radio circles into full-width selectable rows with a visible selected state.

## Why
User feedback across two rounds: first that the app "already has a dashboard" pattern (full-width) that other pages should match, then that the resulting MCQ layout looked bad — confirmed by screenshot (a tiny radio list floating next to a mostly-empty page).

## Acceptance Criteria
- `quiz/show`, `questions/manage` no longer use `mx-auto`/fixed `max-w-*` centering as their outer wrapper.
- Coding questions render prompt+test cases and the editor side by side on `lg+` screens; MCQ/short-answer questions render as a single focused column.
- MCQ options are full-width rows with a visible border/background change when selected (verified via screenshot in light and dark mode).
- `questions/manage`'s three creation forms sit in a grid on `xl+` screens without stretching to match each other's height.

## Risk flag
No — layout/styling only, no schema or endpoint change.
