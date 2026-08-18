# Security ticket: Rebrand to PrepPilot + drop the stock marketing landing page

## Confirmation of no sensitive surface
Branding text/icon and a route-level redirect — no auth, no secrets, no external input parsing, no third-party integration, no infra/DNS/CDN change. The `/` route goes from rendering a page to an unconditional redirect; it carries no request data and grants no new access (everything behind it was already reachable directly at `/topics` under the existing `AutoLoginLocalUser` middleware group).

## Dependency change check
None — no dependencies added or bumped (the `Rocket` icon comes from `lucide-react`, already installed).
