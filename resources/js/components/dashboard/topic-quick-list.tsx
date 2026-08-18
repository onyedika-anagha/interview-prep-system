import { Button } from '@/components/ui/button';
import { type TopicProgress } from '@/types/interview-prep';
import { Link } from '@inertiajs/react';

export function TopicQuickList({ topics }: { topics: TopicProgress[] }) {
    if (topics.length === 0) {
        return (
            <div className="flex h-full items-center justify-center p-8">
                <p className="text-muted-foreground text-sm">Add a topic to start practicing.</p>
            </div>
        );
    }

    return (
        <div className="flex flex-col divide-y">
            {topics.map((topic) => (
                <div key={topic.id} className="flex items-center justify-between gap-4 p-4">
                    <div className="flex flex-col">
                        <span className="font-medium">{topic.name}</span>
                        <span className="text-muted-foreground text-sm">
                            {topic.attempt_count > 0 ? `${topic.accuracy}% accuracy · ${topic.attempt_count} attempts` : 'Not attempted yet'}
                        </span>
                    </div>
                    <Button asChild size="sm" variant="outline">
                        <Link href={`/topics/${topic.slug}/quiz?difficulty=easy`}>Practice</Link>
                    </Button>
                </div>
            ))}
        </div>
    );
}
