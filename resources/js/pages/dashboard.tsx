import { TopicQuickList } from '@/components/dashboard/topic-quick-list';
import { StatTile } from '@/components/stat-tile';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type TopicProgress } from '@/types/interview-prep';
import { Head, Link } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }];

interface DashboardProps {
    overall: { total_attempts: number; accuracy: number; review_due_count: number; draft_count: number };
    topics: TopicProgress[];
}

export default function Dashboard({ overall, topics }: DashboardProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="border-sidebar-border/70 dark:border-sidebar-border flex flex-col items-start justify-center gap-2 overflow-hidden rounded-xl border p-4">
                        <p className="text-sm font-medium">Need more questions?</p>
                        <p className="text-muted-foreground text-sm">Generate with AI, add one manually, or upload a JSON file.</p>
                        <Button asChild size="sm">
                            <Link href="/questions/manage">Manage questions</Link>
                        </Button>
                    </div>
                    <StatTile
                        label="Review queue"
                        value={overall.review_due_count}
                        description="questions due for review"
                        href="/topics"
                        cta="Start practicing"
                    />
                    <StatTile
                        label="Draft questions"
                        value={overall.draft_count}
                        description="pending your review"
                        href="/questions/manage"
                        cta="Review drafts"
                    />
                </div>
                <Card className="flex-1">
                    <CardHeader className="flex-row items-center justify-between">
                        <CardTitle className="text-base">Your topics</CardTitle>
                        <span className="text-muted-foreground text-sm">
                            {overall.total_attempts > 0 ? `${overall.accuracy}% overall accuracy` : 'No attempts yet'}
                        </span>
                    </CardHeader>
                    <CardContent className="p-0">
                        <TopicQuickList topics={topics} />
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
