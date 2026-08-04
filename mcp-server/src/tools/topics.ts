import { McpServer } from '@modelcontextprotocol/sdk/server/mcp.js';
import { apiFetch } from '../config.js';

export function registerTopicTools(server: McpServer) {
    server.tool(
        'list_topics',
        'List all interview-prep topics (stack and general categories), with their slugs.',
        {},
        async () => {
            const data = await apiFetch('/topics');
            return { content: [{ type: 'text' as const, text: JSON.stringify(data, null, 2) }] };
        },
    );
}
