import { AnswerForm } from '@/components/quiz/answer-form';
import { QuestionCard } from '@/components/quiz/question-card';
import { Button } from '@/components/ui/button';
import { buildBackUrl, buildQuizUrl, useQuizSession } from '@/hooks/use-quiz-session';
import AppLayout from '@/layouts/app-layout';
import { TYPE_LABELS } from '@/lib/question-labels';
import { type BreadcrumbItem } from '@/types';
import { type QuestionType, type QuizQuestion, type TestCaseResult, type Topic } from '@/types/interview-prep';
import { Head, Link } from '@inertiajs/react';

interface QuizShowProps {
    topic: Topic;
    difficulty: string;
    type: string | null;
    question: QuizQuestion | null;
    excludeIds: string[];
    totalQuestions: number;
    executionTimeoutSeconds: number;
    runResult: TestCaseResult[] | null;
}

export default function QuizShow({
    topic,
    difficulty,
    type,
    question,
    excludeIds,
    totalQuestions,
    executionTimeoutSeconds,
    runResult,
}: QuizShowProps) {
    const { answer, setAnswer, submitting, submitAnswer, running, runCode, questionNumber } = useQuizSession({
        questionId: question?.id ?? null,
        difficulty,
        excludeIds,
        type,
    });

    const previousQuestionId = excludeIds.length > 0 ? Number(excludeIds[excludeIds.length - 1]) : null;

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
                        <QuestionCard question={question} questionNumber={questionNumber} totalQuestions={totalQuestions} />
                        <AnswerForm
                            question={question}
                            answer={answer}
                            onAnswerChange={setAnswer}
                            onSubmit={submitAnswer}
                            submitting={submitting}
                            onRun={runCode}
                            running={running}
                            runResult={runResult}
                            executionTimeoutSeconds={executionTimeoutSeconds}
                        />
                        <div className="flex gap-2">
                            {previousQuestionId !== null && (
                                <Button asChild variant="ghost" size="sm">
                                    <Link href={buildBackUrl(previousQuestionId, difficulty, excludeIds.slice(0, -1), type)}>
                                        ← Previous question
                                    </Link>
                                </Button>
                            )}
                            <Button asChild variant="ghost" size="sm">
                                <Link href={buildQuizUrl(topic.slug, difficulty, [...excludeIds, String(question.id)], type)}>
                                    Skip this question
                                </Link>
                            </Button>
                        </div>
                    </>
                ) : (
                    <div className="flex flex-col items-center gap-4 rounded-xl border p-8 text-center text-muted-foreground">
                        <p>
                            No more {difficulty}
                            {type ? ` ${TYPE_LABELS[type as QuestionType].toLowerCase()}` : ''} questions for {topic.name} right now.
                        </p>
                        {type && (
                            <p className="text-sm">
                                Try{' '}
                                <Link href={buildQuizUrl(topic.slug, difficulty, excludeIds)} className="underline">
                                    clearing the question-type filter
                                </Link>
                                .
                            </p>
                        )}
                        <Button asChild variant="outline">
                            <Link href="/topics">Back to topics</Link>
                        </Button>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
