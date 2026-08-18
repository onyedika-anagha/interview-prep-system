# Design ticket: Rebrand to PrepPilot + drop the stock marketing landing page

## What's changing visually/UX-wise
Sidebar logo mark changes from the Laravel diamond glyph to a `Rocket` icon (lucide-react, already a project dependency) in the existing rounded primary-color badge; the label next to it changes from "Laravel Starter Kit" to "PrepPilot." The standalone `/` marketing page is removed; visiting `/` now redirects straight into `/topics`, so there is no longer a distinct landing screen to design for.

## Affected screen(s) and state(s)
- Sidebar header (`AppLogo`) — single state, no loading/empty/error variants apply (static branding).
- Auth pages (login/register/forgot-password) — logo mark only, same treatment.
- `/` — no longer a screen; redirects (default/only state).

## Before/after
Before: Laravel diamond mark + "Laravel Starter Kit" text; `/` showed Laravel's own marketing copy and links.
After: Rocket mark + "PrepPilot" text; `/` has no screen of its own, it forwards to the topic list.

## Accessibility check
No — icon is decorative next to visible text (not the only label), contrast and touch target sizing are unchanged from the existing sidebar treatment.
