# Design ticket: Full-width, grid-based layout for quiz/manage pages + MCQ option styling

## What's changing visually/UX-wise
- `quiz/show`: coding questions use a two-column grid (question/test cases | editor+actions) on `lg+`, full width, replacing the previous `mx-auto max-w-2xl` single column. MCQ/short-answer questions keep a single centered column (`max-w-2xl`) — full-bleed for a 4-option list left the page mostly empty and felt broken.
- MCQ answer options: each option is now a full-width bordered row (padding, rounded corners) instead of a bare radio circle + inline text. Hover state (`hover:bg-accent`) and a selected state (darker border + faint tinted background, driven by the radio's `data-state=checked`) make the current selection unambiguous at a glance.
- `questions/manage`: the three question-creation forms (Generate/Manual/Import) sit in a 3-column grid on `xl+` instead of stacking full-width; each keeps its natural height (`items-start`, no forced stretch).

## Affected screen(s) and state(s)
- `quiz/show` — coding vs. non-coding layout branch (both are "default" states, no new loading/empty/error states introduced).
- `AnswerForm`'s MCQ branch — unselected row, hover, and selected row are now visually distinct states.
- `questions/manage` — default state (topics exist) only; the "create a topic first" empty state is unchanged.

## Before/after
Before: MCQ options were plain radio circles with adjacent text, no row affordance, sitting in a layout split awkwardly across a wide grid. After: each option is a clickable card-like row with a clear selected state, in a single focused column sized to the content.

## Accessibility check
Yes — each option row is `<label>`-equivalent via Radix `Label`/`htmlFor` (click target now covers the full row, not just the radio dot and text — larger, easier touch target than before). Selected-state contrast (near-black border on both light and dark backgrounds, verified via screenshot) exceeds the existing `border-input` baseline, so it doesn't rely on color alone — the radio dot itself still fills in as the primary indicator.
