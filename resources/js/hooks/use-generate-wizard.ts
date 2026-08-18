import { DIFFICULTIES } from '@/lib/question-labels';
import { type Topic } from '@/types/interview-prep';
import { useForm } from '@inertiajs/react';
import { useState } from 'react';

/**
 * Two-step wizard: pick a topic, then difficulty + count. Kept as a hook so
 * the step state and the Inertia form travel together.
 */
export function useGenerateWizard(topics: Topic[]) {
    const [step, setStep] = useState<1 | 2>(1);
    const { data, setData, post, processing, errors, reset } = useForm({
        topic_id: '' as number | '',
        difficulty: 'easy',
        count: 5,
    });

    const selectedTopic = topics.find((topic) => topic.id === data.topic_id) ?? null;

    const chooseTopic = (topicId: number) => {
        setData('topic_id', topicId);
        setStep(2);
    };

    const back = () => setStep(1);

    const submit = () => {
        post(route('questions.generate'), {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                setStep(1);
            },
        });
    };

    return { DIFFICULTIES, step, selectedTopic, data, setData, chooseTopic, back, submit, processing, errors };
}
