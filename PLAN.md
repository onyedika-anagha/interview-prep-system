# Interview Prep System — Plan

## 1. What this is

A personal, local tool (on top of the Laravel + Inertia/React starter already
scaffolded in this repo) for practicing programming interview questions and
coding challenges across topics/courses. Claude generates questions, grades
your answers, corrects mistakes, explains in depth, and (optionally) plays
coding games with you. Goal: get better at programming through repeated,
AI-reviewed practice.

## 2. Claude CLI vs MCP — recommendation

**Use the Claude CLI in headless mode (`claude -p "..." --output-format
json`), shelled out to from the Laravel backend. Skip building a custom MCP
server for v1.**

Why:

- **CLI fits the job.** Every place Claude touches this system —
  "generate 10 questions for topic X", "grade this answer", "explain why this
  is wrong" — is a one-shot request/response call. That's exactly what
  `claude -p` headless mode is for: a synchronous function call from PHP via
  `Symfony\Process`, no server to run or maintain.
- **MCP solves a different problem.** MCP exists so an external client (Claude
  Desktop, Claude Code, another agent) can call *into* your app as a set of
  tools. Nothing external needs to call into this app for v1 — the web UI
  *is* the interface, and the backend calls Claude, not the other way round.
  Building an MCP server here means writing a server process, tool schemas,
  and auth for a feature you can get from Bash + a PHP wrapper class.
- **Cost.** Headless CLI calls ride your existing Claude subscription/session
  instead of metered per-token API billing — matters for something you'll
  call a lot while studying.
- **MCP becomes worth it later, not now** — specifically if you want to add
  or edit questions/resources by chatting with Claude Desktop (or this CLI)
  *outside* the web app, with changes landing straight in the app's database.
  That's a clean Phase 4 addition once the core loop works, not a
  prerequisite for it.

## 3. High-level architecture

Builds on what's already scaffolded (Laravel 12, Inertia, React 19, SQLite).

- **Models:** `Topic` (course/subject), `Question` (mcq / short-answer /
  coding), `Attempt` (a submitted answer + score + feedback), `Resource`
  (study material tied to a topic).
- **`ClaudeCli` service** (`app/Services/ClaudeCli.php`): wraps
  `Process::run(['claude', '-p', $prompt, '--output-format', 'json'])`,
  parses the JSON result. One place all Claude calls go through.
- **Generation flow:** pick a topic + difficulty → queued job asks Claude for
  N new questions → stored as `Question` rows (status `draft`/`approved`).
- **Grading flow:** you submit an answer in the React UI → Laravel sends
  question + your answer to Claude via `ClaudeCli` → response stored as an
  `Attempt` with score, corrections, and explanation.
- **Games:** timed/daily-challenge modes reuse the same Question/Attempt
  models with different selection and scoring rules — not a separate system.
- **Progress:** attempt history per topic, simple streaks, and a review queue
  that resurfaces questions you got wrong (spaced repetition).

## 4. Phased roadmap

- **Phase 1 (MVP):** Topics + Questions CRUD, Claude-generated questions per
  topic, take a quiz, submit an answer, Claude grades + explains, view
  results.
- **Phase 2:** Progress dashboard, review queue for missed questions,
  Claude-generated study resources per topic.
- **Phase 3:** Coding games — timed rounds, "spot the bug", streaks/badges
  (single-user, so no leaderboard needed).
- **Phase 4 (optional):** Thin MCP server so Claude Desktop/other clients can
  add or update questions/resources directly, without going through the UI.

## 5. Open questions (will shape the PRD)

1. Which topics/courses to seed first (e.g. JS, DSA, system design)?
2. For coding questions: does submitted code actually *execute* somewhere
   (needs a sandbox), or is grading purely Claude reasoning over the code as
   text? Sandboxed execution adds real security/setup cost.
3. Single-user, no login — confirm no auth is needed for v1?
4. Any topic/course content you already have (e.g. course outlines, existing
   question lists) to seed from, or should everything be Claude-generated
   from scratch?

## 6. Next step

Once you approve this plan, I'll follow the repo's existing rules
(`.claude/rules/prd-rule.md` then `task-list.md`): ask a few clarifying
questions, write `tasks/prd-interview-prep-system.md`, then — after you say
"Go" — break it into `tasks/tasks-interview-prep-system.md`.
