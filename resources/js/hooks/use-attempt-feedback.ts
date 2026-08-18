import { router } from '@inertiajs/react';
import { useState } from 'react';

interface UseAttemptFeedbackArgs {
    questionId: number;
    attemptId: number;
    difficulty: string;
    excludeIds: string[];
    type: string | null;
}

/**
 * Fetches an AI explanation for an already-graded MCQ attempt on demand, re-rendering the
 * result page in place once the feedback comes back.
 */
export function useAttemptFeedback({ questionId, attemptId, difficulty, excludeIds, type }: UseAttemptFeedbackArgs) {
    const [loadingFeedback, setLoadingFeedback] = useState(false);

    const requestFeedback = () => {
        setLoadingFeedback(true);

        router.post(
            `/questions/${questionId}/attempts/${attemptId}/feedback`,
            { difficulty, exclude: excludeIds.join(','), type },
            { preserveScroll: true, onFinish: () => setLoadingFeedback(false) },
        );
    };

    return { loadingFeedback, requestFeedback };
}
