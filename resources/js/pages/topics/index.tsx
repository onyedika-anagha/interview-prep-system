import { TopicCard } from '@/components/topics/topic-card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type Topic } from '@/types/interview-prep';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Topics', href: '/topics' }];

interface TopicsIndexProps {
    topics: {
        stack: Topic[];
        general: Topic[];
    };
}

export default function TopicsIndex({ topics }: TopicsIndexProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Topics" />
            <div className="flex flex-1 flex-col gap-8 p-4">
                <section className="flex flex-col gap-3">
                    <h2 className="text-lg font-semibold">Your stack</h2>
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {topics.stack.map((topic) => (
                            <TopicCard key={topic.id} topic={topic} />
                        ))}
                    </div>
                </section>
                <section className="flex flex-col gap-3">
                    <h2 className="text-lg font-semibold">General CS</h2>
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {topics.general.map((topic) => (
                            <TopicCard key={topic.id} topic={topic} />
                        ))}
                    </div>
                </section>
            </div>
        </AppLayout>
    );
}
