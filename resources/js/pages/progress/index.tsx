import { TopicStats } from '@/components/progress/topic-stats';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type TopicProgress } from '@/types/interview-prep';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Progress', href: '/progress' }];

export default function ProgressIndex({ stats }: { stats: TopicProgress[] }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Progress" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {stats.map((stat) => (
                        <TopicStats key={stat.id} stat={stat} />
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
