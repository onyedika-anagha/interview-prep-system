# Engineering ticket: Rebrand to PrepPilot + drop the stock marketing landing page

## What changed
- `.env`, `.env.example`: `APP_NAME=Laravel` → `APP_NAME=PrepPilot` (drives the `<title>` tag in `resources/views/app.blade.php`, which already reads `config('app.name')`).
- `resources/js/components/app-logo-icon.tsx`: replaced the hand-drawn Laravel diamond SVG with lucide-react's `Rocket` icon (already an installed dependency used elsewhere in the sidebar).
- `resources/js/components/app-logo.tsx`: label text → "PrepPilot".
- `routes/web.php`: `Route::get('/', ...)` rendering the `welcome` Inertia page replaced with `Route::redirect('/', '/topics')->name('home')`; the `home` route name is preserved so `route('home')` links in the auth layouts keep working.
- `resources/js/pages/welcome.tsx`: deleted (no longer referenced by any route).
- `tests/Feature/ExampleTest.php`: updated from asserting `/` returns 200 to asserting it redirects to `/topics`.

## Test added or updated
`tests/Feature/ExampleTest.php` — asserts `GET /` redirects to `/topics`. Full suite (`php artisan test`) passes: 73 tests, 0 failures.

## Changelog entry
Rebrand the app as PrepPilot (logo, name, page title) and remove the unused Laravel-starter-kit marketing page — `/` now goes straight into the app.
