import { router } from '@inertiajs/react';
import { useState } from 'react';

export function buildQuizUrl(topicSlug: string, difficulty: string, excludeIds: string[], type?: string | null): string {
    const params = new URLSearchParams({ difficulty });

    if (excludeIds.length > 0) {
        params.set('exclude', excludeIds.join(','));
    }

    if (type) {
        params.set('type', type);
    }

    return `/topics/${topicSlug}/quiz?${params.toString()}`;
}

export function buildBackUrl(questionId: number, difficulty: string, excludeIds: string[], type?: string | null): string {
    const params = new URLSearchParams({ difficulty });

    if (excludeIds.length > 0) {
        params.set('exclude', excludeIds.join(','));
    }

    if (type) {
        params.set('type', type);
    }

    return `/questions/${questionId}/back?${params.toString()}`;
}

interface UseQuizSessionArgs {
    questionId: number | null;
    difficulty: string;
    excludeIds: string[];
    type: string | null;
}

/**
 * Holds the in-progress answer for the current question and derives its
 * position in the quiz from the exclude list the server round-trips on
 * every navigation (no separate client-side session store needed).
 */
export function useQuizSession({ questionId, difficulty, excludeIds, type }: UseQuizSessionArgs) {
    const [answer, setAnswer] = useState('');
    const [submitting, setSubmitting] = useState(false);
    const [running, setRunning] = useState(false);

    const questionNumber = excludeIds.length + 1;

    const submitAnswer = () => {
        if (!questionId || answer.trim() === '') {
            return;
        }

        setSubmitting(true);

        router.post(
            `/questions/${questionId}/attempts`,
            { answer, difficulty, exclude: excludeIds.join(','), type },
            { onFinish: () => setSubmitting(false) },
        );
    };

    const runCode = () => {
        if (!questionId || answer.trim() === '') {
            return;
        }

        setRunning(true);

        router.post(
            `/questions/${questionId}/run`,
            { answer, difficulty, exclude: excludeIds.join(','), type },
            { preserveScroll: true, preserveState: true, onFinish: () => setRunning(false) },
        );
    };

    return { answer, setAnswer, submitting, submitAnswer, running, runCode, questionNumber };
}
