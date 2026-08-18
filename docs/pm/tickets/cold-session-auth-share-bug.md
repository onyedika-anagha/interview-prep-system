# Ticket: Fix blank-page crash on a user's very first request

## Change
Any Inertia page inside the `AutoLoginLocalUser` route group crashed with a blank screen on a request with no existing session cookie (a genuinely first-ever visit, a cleared session, or a fresh browser profile). `HandleInertiaRequests` (global middleware) was capturing `$request->user()` eagerly, before route-specific `AutoLoginLocalUser` middleware had a chance to run and auto-authenticate. The frontend's `<UserInfo>` component then read `.avatar` off a null user and threw. Made the `auth`/`draftQuestionCount` shared props lazy (closures), which Inertia resolves after all middleware — including `AutoLoginLocalUser` — has run.

## Why
Found while verifying an unrelated UI fix in a fresh headless-browser session (no cookies) — the quiz page rendered a completely blank body with a React error in the console. Confirmed by temporarily reverting the fix: a request with no prior session gets `auth.user: null` and crashes client-side.

## Acceptance Criteria
- A request to any `AutoLoginLocalUser`-gated route (e.g. `/topics/{slug}/quiz`) with no existing session cookie returns a page where `auth.user` is populated (not null).
- Existing behavior for already-authenticated requests (`/dashboard`, settings pages) is unchanged.

## Risk flag
No — this only affects when an already-true fact (the auto-logged-in user) is read, not what a user is authorized to do; no new access is granted by this change.
