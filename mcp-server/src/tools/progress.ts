import { McpServer } from '@modelcontextprotocol/sdk/server/mcp.js';
import { z } from 'zod';
import { apiFetch } from '../config.js';

export function registerProgressTools(server: McpServer) {
    server.tool(
        'list_attempts',
        'List recent quiz attempts, optionally filtered by topic slug.',
        {
            topic_slug: z.string().optional(),
            limit: z.number().min(1).max(100).optional(),
        },
        async ({ topic_slug, limit }) => {
            const params = new URLSearchParams();
            if (topic_slug) params.set('topic_slug', topic_slug);
            if (limit) params.set('limit', String(limit));
            const query = params.toString();
            const data = await apiFetch(`/attempts${query ? `?${query}` : ''}`);
            return { content: [{ type: 'text' as const, text: JSON.stringify(data, null, 2) }] };
        },
    );

    server.tool(
        'get_progress',
        'Get per-topic accuracy, attempt counts, and current streak. Use this to spot weak topics (low accuracy) worth focusing study on.',
        {},
        async () => {
            const data = await apiFetch('/progress');
            return { content: [{ type: 'text' as const, text: JSON.stringify(data, null, 2) }] };
        },
    );
}
