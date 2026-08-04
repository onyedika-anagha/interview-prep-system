import { type QuestionType } from '@/types/interview-prep';
import { useForm } from '@inertiajs/react';
import { type FormEventHandler } from 'react';

const DIFFICULTIES = ['easy', 'medium', 'hard'];

export function useManualQuestionForm(defaultTopicId: number | '') {
    const form = useForm({
        topic_id: defaultTopicId,
        type: 'short_answer' as QuestionType,
        difficulty: 'easy',
        prompt: '',
        reference_answer: '',
        language: '' as '' | 'javascript' | 'php',
        test_cases: '',
    });
    const { data, setData, post, processing, errors, reset, setError, clearErrors, transform } = form;

    transform((formData) => ({
        ...formData,
        language: formData.type === 'coding' && formData.language ? formData.language : null,
        test_cases: formData.type === 'coding' && formData.test_cases.trim() !== '' ? JSON.parse(formData.test_cases) : null,
    }));

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        clearErrors('test_cases');

        if (data.type === 'coding' && data.test_cases.trim() !== '') {
            try {
                JSON.parse(data.test_cases);
            } catch {
                setError('test_cases', 'Test cases must be valid JSON.');

                return;
            }
        }

        post(route('questions.store'), {
            preserveScroll: true,
            onSuccess: () => reset('prompt', 'reference_answer', 'test_cases'),
        });
    };

    return { DIFFICULTIES, data, setData, submit, processing, errors };
}
