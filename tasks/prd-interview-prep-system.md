# PRD: Interview Prep System

## 1. Introduction/Overview

A local, single-user web application (built on the existing Laravel + Inertia/React starter kit) for practicing programming interview questions and coding challenges. Claude generates questions, grades submissions (including running code against test cases), explains corrections in depth, and tracks progress over time. A companion MCP server lets Claude — running in any Claude chat client (Claude Desktop, Claude Code, etc.), not just this app — add or update questions and resources, and read attempt/progress history to suggest what to study next.

**Goal:** get better at programming through repeated, AI-reviewed practice, with Claude able to both power the app and be directed from outside it.

## 2. Goals

1. Provide a working web UI to browse topics and take quizzes (MCQ, short-answer, and coding questions).
2. Auto-generate questions, reference answers, and study resources via an AI provider (Claude CLI headless mode, or the Gemini API as a configurable alternative), seeded across both the user's current stack (PHP/Laravel, JS/React) and general CS/DSA fundamentals.
3. Grade coding submissions by actually running them against test cases in a sandbox, then have Claude explain failures/corrections in detail.
4. Track attempts and progress per topic, with a review queue that resurfaces previously-missed questions.
5. Expose an MCP server so Claude, in any chat session, can add/update questions and resources and read attempt/progress data — without the user opening the app.
6. No login — this is a local, single-user tool.

## 3. User Stories

- As a learner, I want to pick a topic and difficulty and get a quiz of AI-generated questions, so I can practice on demand.
- As a learner, I want to submit a coding solution and see it actually run against test cases, plus Claude's explanation of what's wrong and how to fix it.
- As a learner, I want questions I got wrong to resurface later, so I actually close knowledge gaps instead of only seeing new material.
- As a learner chatting with Claude Desktop, I want to say "add 5 more questions about React hooks" and have them show up in my app without opening it.
- As a learner, I want Claude (via MCP) to see my attempt history so it can tell me what to focus on and generate questions targeted at my weak spots.

## 4. Functional Requirements

1. The system must have a `Topic` model (name, category: `stack` or `general`, description). Slugs are auto-generated server-side per the repo's slug rule — never user-editable.
2. The system must have a `Question` model: `topic_id`, `type` (`mcq` / `short_answer` / `coding`), `difficulty`, `prompt`, reference answer/solution, test cases (for coding questions), `status` (`draft`/`approved`), `generated_by` (`claude` or `manual`).
3. The system must have an `Attempt` model: `question_id`, submitted answer/code, `is_correct`, `score`, Claude's feedback/explanation, execution result (for coding), timestamp.
4. The system must have a `Resource` model: `topic_id`, title, content or URL, `generated_by`.
5. The web UI must let the user pick a topic (and optionally difficulty) and start a quiz.
6. The quiz flow must present one question at a time and accept a submitted answer (text or code).
7. An `AiProvider` abstraction must handle all AI calls (generating new questions for a topic, grading non-coding answers, and explaining/correcting any answer), with two implementations: `ClaudeCli` (wraps headless `claude -p ... --output-format json`) and `GeminiApi` (calls the Gemini API with an API key). The active provider is selected via a single global `.env` setting (`AI_PROVIDER=claude|gemini`) — not a per-request choice.
8. A sandboxed code-execution service must run submitted coding answers against the question's test cases and report pass/fail per test case. Start with JavaScript (Node) and PHP, run in a restricted subprocess with a timeout and no network access.
9. After execution, the code, test results, and question are sent to Claude to produce a human-readable explanation of failures and how to fix them.
10. The system must track a review queue: questions answered incorrectly resurface after a delay (simple spaced-repetition — e.g. Leitner-style buckets), prioritized in future quiz sessions.
11. A progress view must show per-topic accuracy, attempt counts, and current streak.
12. An MCP server must expose tools to: list topics, add/update a question, add a resource, list attempts, and get progress/weak-spot summaries.
13. The MCP server must operate through the same validation/slug-generation logic the web app uses (i.e., call into the Laravel app's own local API rather than writing to the database directly), so there is one source of truth for data integrity.
14. The web app requires no authentication — it's local and single-user.
15. Frontend code must follow the repo's existing code-separation rule: reusable UI in `/components`, business logic in `/hooks` or `/services`, pages only compose components.

## 5. Non-Goals (Out of Scope)

- Multi-user accounts, roles, or permissions.
- Public hosting or deployment — this is a local-only tool for v1. (If hosting is wanted later, the Claude CLI calls would need to move to the Anthropic API with an API key — noted as a future consideration, not built now.)
- Leaderboards or any social/competitive features.
- Video or audio content generation.
- Support for sandboxed execution of languages beyond JS and PHP in v1.

## 6. Design Considerations

- Reuse the shadcn/Radix UI components already installed in the starter kit rather than adding a new component library.
- Quiz-taking UI: one question per screen, clear pass/fail + explanation on submission.
- Progress view: simple stat tiles/lists — no need for a charting library unless a specific visualization is requested later.

## 7. Technical Considerations

- Requires the `claude` CLI installed and authenticated (via the user's existing subscription) on the machine running the app; the `ClaudeCli` service shells out via PHP's `Process` facade.
- Gemini option: requires a `GEMINI_API_KEY` in `.env` when `AI_PROVIDER=gemini`; the `GeminiApi` service calls the Gemini API over HTTP (Laravel's `Http` facade — no new dependency).
- Sandboxed execution: subprocess-based (e.g. `node` / `php` invoked with a timeout, restricted temp working directory, no network access) — not a full container system for v1.
- MCP server: a small Node/TypeScript process using `@modelcontextprotocol/sdk`, registered with `claude mcp add` (Claude Code) and/or the Claude Desktop config. It calls the Laravel app's existing local API endpoints (not the database directly) so slug generation and validation stay in one place.
- Database: Mysql, already set up in this starter kit.

## 8. Success Metrics

- A full quiz (question → submit → grade → explanation) works end-to-end for at least one `stack` topic and one `general` topic.
- A coding question can be submitted, executed against test cases, and produce a pass/fail result plus Claude's explanation, for both JS and PHP.
- Adding a question via the MCP server (from a Claude chat session) makes it appear in the web UI without restarting the app.
- A previously-wrong question correctly resurfaces in a later quiz session.

## 9. Open Questions

- Exact list of starter topics within "stack" and "general" categories (can be seeded incrementally — not a blocker to starting).
- Whether the review-queue scheduling algorithm should be a simple fixed-delay bucket system or a proper spaced-repetition formula (SM-2 style) — start simple, revisit if it doesn't feel effective.
