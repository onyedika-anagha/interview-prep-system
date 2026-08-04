<?php

namespace Database\Seeders;

use App\Models\Topic;
use Illuminate\Database\Seeder;

class TopicSeeder extends Seeder
{
    public function run(): void
    {
        $topics = [
            ['name' => 'PHP & Laravel', 'category' => 'stack', 'description' => 'Core PHP, Laravel conventions, Eloquent, and the request lifecycle.'],
            ['name' => 'JavaScript & React', 'category' => 'stack', 'description' => 'Modern JS, React hooks, component patterns, and state management.'],
            ['name' => 'Data Structures & Algorithms', 'category' => 'general', 'description' => 'Arrays, trees, graphs, sorting, and complexity analysis.'],
            ['name' => 'System Design', 'category' => 'general', 'description' => 'Scalability, caching, databases, and distributed systems fundamentals.'],
        ];

        foreach ($topics as $topic) {
            Topic::firstOrCreate(['name' => $topic['name']], $topic);
        }
    }
}
