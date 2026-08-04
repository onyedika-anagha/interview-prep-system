import { McpServer } from '@modelcontextprotocol/sdk/server/mcp.js';
import { z } from 'zod';
import { apiFetch } from '../config.js';

const testCaseSchema = z.object({ input: z.unknown(), expected_output: z.unknown() });

export function registerQuestionTools(server: McpServer) {
    server.tool(
        'add_question',
        "Add a new question to a topic. Coding questions must include language and test_cases; a coding reference_answer must read one JSON value from stdin and print one JSON value to stdout — that's how submissions get executed and graded.",
        {
            topic_slug: z.string().describe('Slug of the topic to add this question to (see list_topics)'),
            type: z.enum(['mcq', 'short_answer', 'coding']),
            difficulty: z.string().describe('e.g. easy, medium, hard'),
            prompt: z.string(),
            reference_answer: z.string(),
            language: z.enum(['javascript', 'php']).optional(),
            test_cases: z.array(testCaseSchema).optional(),
            status: z.enum(['draft', 'approved']).optional(),
        },
        async (input) => {
            const data = await apiFetch('/questions', { method: 'POST', body: JSON.stringify(input) });
            return { content: [{ type: 'text' as const, text: JSON.stringify(data, null, 2) }] };
        },
    );

    server.tool(
        'update_question',
        'Update fields on an existing question by id.',
        {
            question_id: z.number(),
            type: z.enum(['mcq', 'short_answer', 'coding']).optional(),
            difficulty: z.string().optional(),
            prompt: z.string().optional(),
            reference_answer: z.string().optional(),
            language: z.enum(['javascript', 'php']).nullable().optional(),
            test_cases: z.array(testCaseSchema).nullable().optional(),
            status: z.enum(['draft', 'approved']).optional(),
        },
        async ({ question_id, ...fields }) => {
            const data = await apiFetch(`/questions/${question_id}`, { method: 'PATCH', body: JSON.stringify(fields) });
            return { content: [{ type: 'text' as const, text: JSON.stringify(data, null, 2) }] };
        },
    );
}
