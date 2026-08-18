# Engineering ticket: Full-width, grid-based layout for quiz/manage pages + MCQ option styling

## What changed
- `resources/js/pages/quiz/show.tsx`: dropped `mx-auto max-w-2xl` wrapper. Layout now branches on `question.type === 'coding'`: coding questions render a `grid lg:grid-cols-2 items-start` split (question card | answer form + nav buttons); MCQ/short-answer render a single `mx-auto max-w-2xl` column. Extracted `navButtons` (Previous/Skip) and `answerForm` to local variables shared by both branches instead of duplicating the JSX.
- `resources/js/pages/questions/manage.tsx`: dropped `mx-auto max-w-4xl`; the three creation-form components now sit in `grid items-start gap-6 xl:grid-cols-3` instead of a stacked flex column.
- `resources/js/components/quiz/answer-form.tsx`: MCQ options are now wrapped in a bordered row div using Tailwind's `has-[[data-state=checked]]` variant (matches Radix's `data-state` attribute on the underlying radio button) for the selected-state border/background, instead of a bare `RadioGroupItem` + `Label` pair. Option ids are now scoped with `useId()` (`${answerId}-option-${index}`) instead of a bare `option-${index}`, fixing a latent id-collision risk if `AnswerForm` were ever rendered twice on one page.

## Test added or updated
No new automated test — this is layout/CSS only, verified with a real Playwright screenshot against the running dev server (light mode, dark mode, and the selected-option state), not just a build check. `npx tsc --noEmit`, `npx eslint`, and the full `php artisan test` suite (74 passed) confirm no regressions in the surrounding logic.

## Changelog entry
Redesigned the quiz and manage-questions page layouts (grid-based, full-width) and restyled MCQ answer options as selectable rows with a clear selected state.
