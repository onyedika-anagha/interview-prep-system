# Ticket: Rebrand to PrepPilot + drop the stock marketing landing page

## Change
The app was still running as unmodified Laravel-starter-kit boilerplate: `APP_NAME=Laravel`, the sidebar/logo read "Laravel Starter Kit," and `/` rendered the stock "Let's get started / Read the docs / Laracasts / Deploy now" marketing page. Renamed the product to PrepPilot (name, logo mark, `APP_NAME`) and replaced the marketing page with a redirect from `/` straight into `/topics`.

## Why
This is a local, single-user tool (PRD FR14) — there's no visitor to market to, so the boilerplate marketing page was dead weight that also actively misrepresented the product (linked to laravel.com/laracasts/cloud.laravel.com).

## Acceptance Criteria
- `AppLogo`/sidebar/browser tab title show "PrepPilot," not "Laravel Starter Kit" / "Laravel."
- Visiting `/` redirects to `/topics` (verified by `tests/Feature/ExampleTest.php`).
- Login/register/reset-password pages (which link "back home" via `route('home')`) still resolve correctly.

## Risk flag
No — branding/routing only, no schema change, no new user-facing capability.
