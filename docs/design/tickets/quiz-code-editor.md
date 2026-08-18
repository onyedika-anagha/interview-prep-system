# Design ticket: Real code editor for coding questions

## What's changing visually/UX-wise
The coding-question answer box becomes a syntax-highlighted code editor (line-aware, bracket-aware) instead of a plain monospace textarea. Visual chrome (border, radius) matches the existing input styling (`border-input`, `rounded-md`) so it reads as part of the same form, not a foreign embed.

## Affected screen(s) and state(s)
`quiz/show` → `AnswerForm`, coding-question branch only:
- **Default:** empty editor with placeholder text "Write your solution here…", matching the prior textarea's placeholder.
- **Filled:** syntax-highlighted code as typed.
- **Light/dark:** follows the app's existing appearance setting (`githubLight` / `githubDark` themes), consistent with the rest of the UI's dark-mode support.
- MCQ and short-answer question types are unaffected (no editor).

## Before/after
Before: plain gray textarea, monospace font, no highlighting, Grammarly icon floating in the corner.
After: syntax-highlighted editor with line-aware indentation, themed to match light/dark mode, no Grammarly interference.

## Accessibility check
Yes — CodeMirror's content region is a `contenteditable` region rather than a native `<textarea>`, so the previous `<label htmlFor>` association no longer applies. Replaced with an explicit `aria-label="Your solution"` set directly on the editor's content element (via `EditorView.contentAttributes`), keeping screen-reader labeling equivalent to before.
