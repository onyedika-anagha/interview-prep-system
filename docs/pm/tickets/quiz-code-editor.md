# Ticket: Real code editor for coding questions

## Change
Coding questions were answered in a plain HTML `<textarea>` (monospace font only, manual Tab-to-indent handling, no syntax awareness). Replaced it with a CodeMirror 6 editor (via `@uiw/react-codemirror`) with JavaScript/PHP syntax highlighting, matched to the question's `language` field.

## Why
A textarea gives no visual feedback while writing code (no highlighting, no bracket matching), which is a weak experience for a product whose core interaction is writing code. CodeMirror was chosen over Monaco: ~150-300KB vs Monaco's multi-MB, enough for JS/PHP highlighting in a quiz form, doesn't need Monaco's IDE-grade IntelliSense.

## Acceptance Criteria
- Coding questions render a syntax-highlighted editor matching `question.language` (`javascript` or `php`); non-coding question types (`mcq`, `short_answer`) are unchanged.
- Editor follows the app's existing light/dark appearance setting.
- Tab inserts an indent inside the editor (matching the prior textarea behavior) rather than moving focus away.
- Grammarly's floating icon no longer attaches to the code editor.

## Non-Goals (Out of Scope)
- Autocomplete/IntelliSense, linting, or multi-file editing — not needed for single-function quiz answers.
- Editors for `mcq`/`short_answer` question types — unchanged (radio group / plain textarea respectively).

## Risk flag
No — new dependency (`@uiw/react-codemirror` + `@codemirror/lang-javascript` + `@codemirror/lang-php` + `@uiw/codemirror-theme-github`), but purely a client-side input control; no schema change, no new endpoint, no new data leaving the browser.
