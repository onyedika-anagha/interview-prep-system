#!/usr/bin/env node
import { McpServer } from '@modelcontextprotocol/sdk/server/mcp.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import { registerProgressTools } from './tools/progress.js';
import { registerQuestionTools } from './tools/questions.js';
import { registerResourceTools } from './tools/resources.js';
import { registerTopicTools } from './tools/topics.js';

const server = new McpServer({ name: 'interview-prep', version: '1.0.0' });

registerTopicTools(server);
registerQuestionTools(server);
registerResourceTools(server);
registerProgressTools(server);

const transport = new StdioServerTransport();
await server.connect(transport);
