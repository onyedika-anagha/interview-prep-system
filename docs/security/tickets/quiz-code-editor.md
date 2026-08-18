# Security ticket: Real code editor for coding questions

## Confirmation of no sensitive surface
Client-side input control swap only — no new endpoint, no change to what data is submitted (`answer` is still a plain string posted to the existing `questions/{question}/attempts` route), no secrets, no external input parsing beyond what CodeMirror does in-browser for syntax highlighting (no network calls).

## Dependency change check
Four packages added: `@uiw/react-codemirror`, `@codemirror/lang-javascript`, `@codemirror/lang-php`, `@uiw/codemirror-theme-github`. `npm audit` was run after install and traced — all 14 reported vulnerabilities belong to pre-existing transitive dependencies of `vite`, `concurrently`, `laravel-vite-plugin`, and `@inertiajs/react` (confirmed via `npm ls` on each flagged package); none trace to the newly added CodeMirror packages. The pre-existing `vite`-chain vulnerabilities are a separate, unrelated cleanup item (see overall gap list) — not introduced or touched by this change.
