import { TopicStats } from '@/components/progress/topic-stats';
import { StatTile } from '@/components/stat-tile';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type TopicProgress } from '@/types/interview-prep';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Progress', href: '/progress' }];

interface ProgressIndexProps {
    stats: TopicProgress[];
    overall: { total_attempts: number; accuracy: number; review_due_count: number; draft_count: number };
}

export default function ProgressIndex({ stats, overall }: ProgressIndexProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Progress" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="grid gap-4 sm:grid-cols-3">
                    <StatTile
                        label="Overall accuracy"
                        value={overall.total_attempts > 0 ? `${overall.accuracy}%` : '—'}
                        description={`${overall.total_attempts} attempt${overall.total_attempts === 1 ? '' : 's'}`}
                    />
                    <StatTile label="Due for review" value={overall.review_due_count} description="questions ready to retry" />
                    <StatTile label="Topics tracked" value={stats.length} description="with at least one question" />
                </div>
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {stats.map((stat) => (
                        <TopicStats key={stat.id} stat={stat} />
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
