import { AnswerForm } from '@/components/quiz/answer-form';
import { QuestionCard } from '@/components/quiz/question-card';
import { Button } from '@/components/ui/button';
import { useQuizSession } from '@/hooks/use-quiz-session';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type QuizQuestion, type Topic } from '@/types/interview-prep';
import { Head, Link } from '@inertiajs/react';

interface QuizShowProps {
    topic: Topic;
    difficulty: string;
    question: QuizQuestion | null;
    excludeIds: string[];
}

export default function QuizShow({ topic, difficulty, question, excludeIds }: QuizShowProps) {
    const { answer, setAnswer, submitting, submitAnswer, questionNumber } = useQuizSession({
        questionId: question?.id ?? null,
        difficulty,
        excludeIds,
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Topics', href: '/topics' },
        { title: topic.name, href: `/topics/${topic.slug}/quiz?difficulty=${difficulty}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${topic.name} quiz`} />
            <div className="mx-auto flex w-full max-w-2xl flex-1 flex-col gap-4 p-4">
                {question ? (
                    <>
                        <QuestionCard question={question} questionNumber={questionNumber} />
                        <AnswerForm question={question} answer={answer} onAnswerChange={setAnswer} onSubmit={submitAnswer} submitting={submitting} />
                    </>
                ) : (
                    <div className="flex flex-col items-center gap-4 rounded-xl border p-8 text-center text-muted-foreground">
                        <p>No more {difficulty} questions for {topic.name} right now.</p>
                        <Button asChild variant="outline">
                            <Link href="/topics">Back to topics</Link>
                        </Button>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
