# Engineering ticket: Fix blank-page crash on a user's very first request

## What changed
- `app/Http/Middleware/HandleInertiaRequests.php`: `share()`'s `auth` and `draftQuestionCount` entries changed from eagerly-evaluated values to closures (`fn () => [...]`). Laravel Inertia resolves closure-valued shared props when the response is actually built — after the full middleware stack (including route-specific `AutoLoginLocalUser`, which calls `Auth::login()`) has run — instead of when this global `web`-group middleware executes, which is before route middleware in Laravel's pipeline.
- `tests/Feature/QuizTest.php`: added a regression test asserting `auth.user.id` is present on a cookie-less first request to a quiz route. Verified it fails without the fix (reverted the middleware change, confirmed the test catches it) and passes with it.

## Test added or updated
`tests/Feature/QuizTest.php` — `it shares the auto-logged-in user on the very first request of a session`. Full suite: 74 passed.

## Changelog entry
Fix a blank-page crash on a user's very first request (no session cookie yet) — the shared `auth.user` prop is now resolved after auto-login runs instead of before.

## design_stage
skipped — no UI impact (this is a data-timing fix; the UI it prevents from crashing is unchanged).
