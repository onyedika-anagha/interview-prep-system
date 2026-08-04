import { McpServer } from '@modelcontextprotocol/sdk/server/mcp.js';
import { z } from 'zod';
import { apiFetch } from '../config.js';

export function registerResourceTools(server: McpServer) {
    server.tool(
        'add_resource',
        'Add a study resource (note or link) to a topic.',
        {
            topic_slug: z.string(),
            title: z.string(),
            content: z.string().optional(),
            url: z.string().url().optional(),
        },
        async (input) => {
            const data = await apiFetch('/resources', { method: 'POST', body: JSON.stringify(input) });
            return { content: [{ type: 'text' as const, text: JSON.stringify(data, null, 2) }] };
        },
    );
}
