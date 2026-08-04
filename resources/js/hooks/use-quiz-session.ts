import { router } from '@inertiajs/react';
import { useState } from 'react';

export function buildQuizUrl(topicSlug: string, difficulty: string, excludeIds: string[]): string {
    const params = new URLSearchParams({ difficulty });

    if (excludeIds.length > 0) {
        params.set('exclude', excludeIds.join(','));
    }

    return `/topics/${topicSlug}/quiz?${params.toString()}`;
}

interface UseQuizSessionArgs {
    questionId: number | null;
    difficulty: string;
    excludeIds: string[];
}

/**
 * Holds the in-progress answer for the current question and derives its
 * position in the quiz from the exclude list the server round-trips on
 * every navigation (no separate client-side session store needed).
 */
export function useQuizSession({ questionId, difficulty, excludeIds }: UseQuizSessionArgs) {
    const [answer, setAnswer] = useState('');
    const [submitting, setSubmitting] = useState(false);

    const questionNumber = excludeIds.length + 1;

    const submitAnswer = () => {
        if (!questionId || answer.trim() === '') {
            return;
        }

        setSubmitting(true);

        router.post(
            `/questions/${questionId}/attempts`,
            { answer, difficulty, exclude: excludeIds.join(',') },
            { onFinish: () => setSubmitting(false) },
        );
    };

    return { answer, setAnswer, submitting, submitAnswer, questionNumber };
}
