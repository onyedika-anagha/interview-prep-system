# interview-prep MCP server

Exposes the interview-prep app's local API as MCP tools, so Claude — in any
chat client, not just this app — can add/update questions and resources and
read attempt/progress history.

It calls the Laravel app's `routes/api.php` endpoints over HTTP; it never
touches the database directly, so slug generation and validation stay in one
place (the Laravel app).

## Prerequisites

- The Laravel app must be running (e.g. `php artisan serve`) and reachable —
  by default at `http://127.0.0.1:8000`.
- Node.js 22+ (uses Node's native TypeScript support — no build step).

```bash
cd mcp-server
npm install
```

## Registration

### Claude Code

```bash
claude mcp add interview-prep -- node "/absolute/path/to/mcp-server/src/index.ts"
```

If your Laravel app runs on a different host/port, pass the env var:

```bash
claude mcp add interview-prep --env INTERVIEW_PREP_API_URL=http://127.0.0.1:8000/api -- node "/absolute/path/to/mcp-server/src/index.ts"
```

### Claude Desktop

Add to your Claude Desktop config (`claude_desktop_config.json`):

```json
{
    "mcpServers": {
        "interview-prep": {
            "command": "node",
            "args": ["/absolute/path/to/mcp-server/src/index.ts"],
            "env": {
                "INTERVIEW_PREP_API_URL": "http://127.0.0.1:8000/api"
            }
        }
    }
}
```

Restart Claude Desktop after editing the config.

## Tools

| Tool | Purpose |
|---|---|
| `list_topics` | List topics with their slugs |
| `add_question` | Add a question to a topic (mcq / short_answer / coding) |
| `update_question` | Update fields on an existing question by id |
| `add_resource` | Add a study resource (note or link) to a topic |
| `list_attempts` | List recent attempts, optionally filtered by topic |
| `get_progress` | Per-topic accuracy, attempt counts, current streak |

## Environment

| Variable | Default | Notes |
|---|---|---|
| `INTERVIEW_PREP_API_URL` | `http://127.0.0.1:8000/api` | Base URL of the Laravel app's local API |
