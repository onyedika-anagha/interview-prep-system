# Security ticket: Fix blank-page crash on a user's very first request

## Confirmation of no sensitive surface
This change touches auth-adjacent code (`HandleInertiaRequests::share()`), so it was evaluated for Track A escalation per the security-engineer veto rule. Conclusion: no escalation needed — the fix changes only *when* an already-decided authentication fact is serialized to the frontend (after `AutoLoginLocalUser` runs, instead of before), not *what* a request is authorized to do. `AutoLoginLocalUser`'s own logic (auto-login as `User::first()` for this local, single-user app) is unchanged. No new endpoint, no new data exposed, no session/credential handling changed.

## Dependency change check
None — no dependencies added or bumped.
