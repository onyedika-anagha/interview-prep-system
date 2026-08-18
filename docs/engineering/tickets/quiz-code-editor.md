# Engineering ticket: Real code editor for coding questions

## What changed
- `package.json`: added `@uiw/react-codemirror`, `@codemirror/lang-javascript`, `@codemirror/lang-php`, `@uiw/codemirror-theme-github`. `@codemirror/commands` and `@codemirror/view` were already present transitively and are imported directly.
- `resources/js/hooks/use-is-dark.ts` (new): tracks the `dark` class on `<html>` via `MutationObserver` — the same element `useAppearance()` toggles — so the editor theme follows the app's existing light/dark setting without duplicating its localStorage/media-query logic.
- `resources/js/components/quiz/code-editor.tsx` (new): wraps `CodeMirror` from `@uiw/react-codemirror` with the JS/PHP language extension selected by `question.language`, the light/dark theme from `useIsDark()`, an `indentWithTab` keymap (native basicSetup doesn't bind Tab, for accessibility reasons — opted back in here since this is a dedicated code field), and `data-gramm="false"` + `aria-label` set via `EditorView.contentAttributes`.
- `resources/js/components/quiz/answer-form.tsx`: coding questions now render `<CodeEditor>` instead of `<Textarea>`; removed the now-unused manual Tab-indent handler (`handleTabIndent`), which existed only to work around the textarea's lack of indent support.

## Test added or updated
Manually verified: `npm run build` succeeds, `npx tsc --noEmit` and `npx eslint` clean on all changed files. No existing automated test covered the textarea's rendering (it's a pure UI swap with the same `onAnswerChange`/`answer` contract `AnswerForm` already had test coverage around via `AttemptTest.php`'s use of the underlying submission flow, which is unaffected).

## Changelog entry
Coding questions now use a syntax-highlighted CodeMirror editor (JS/PHP-aware, theme-matched) instead of a plain textarea.

## Known follow-up (not blocking)
The `quiz/show` page's JS bundle grew to ~229KB gzipped (CodeMirror + language packs load eagerly on every quiz page, even non-coding questions). Fine for a local single-user tool; if this app ever needs to be lighter on that page, lazy-load `CodeEditor` via `React.lazy`/`Suspense` so it's only fetched when a coding question actually renders.
