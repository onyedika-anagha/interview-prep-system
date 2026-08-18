import { ResultFeedback } from '@/components/quiz/result-feedback';
import { Button } from '@/components/ui/button';
import { buildQuizUrl } from '@/hooks/use-quiz-session';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type Attempt, type RevealedQuestion, type Topic } from '@/types/interview-prep';
import { Head, Link } from '@inertiajs/react';

interface QuizResultProps {
    topic: Topic;
    difficulty: string;
    type: string | null;
    question: RevealedQuestion;
    attempt: Attempt;
    excludeIds: string[];
}

export default function QuizResult({ topic, difficulty, type, question, attempt, excludeIds }: QuizResultProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Topics', href: '/topics' },
        { title: topic.name, href: `/topics/${topic.slug}/quiz?difficulty=${difficulty}` },
        { title: 'Result', href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Result" />
            <div className="mx-auto flex w-full max-w-2xl flex-1 flex-col gap-4 p-4">
                <ResultFeedback question={question} attempt={attempt} />
                <Button asChild className="self-start">
                    <Link href={buildQuizUrl(topic.slug, difficulty, excludeIds, type)}>Next question</Link>
                </Button>
            </div>
        </AppLayout>
    );
}
