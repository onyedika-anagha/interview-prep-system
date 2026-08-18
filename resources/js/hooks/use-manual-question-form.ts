import { parseTestCases } from '@/lib/parse-test-cases';
import { DIFFICULTIES } from '@/lib/question-labels';
import { type QuestionType } from '@/types/interview-prep';
import { router, useForm } from '@inertiajs/react';
import { type FormEventHandler, useState } from 'react';

export function useManualQuestionForm(defaultTopicId: number | '') {
    const form = useForm({
        topic_id: defaultTopicId,
        type: 'short_answer' as QuestionType,
        difficulty: 'easy',
        prompt: '',
        reference_answer: '',
        options: ['', ''] as string[],
        correctOption: 0,
        language: '' as '' | 'javascript' | 'php',
        test_cases: '',
    });
    const { data, setData, post, processing, errors, reset, setError, clearErrors, transform } = form;
    const [verifying, setVerifying] = useState(false);

    transform((formData) => ({
        ...formData,
        reference_answer: formData.type === 'mcq' ? (formData.options[formData.correctOption] ?? '') : formData.reference_answer,
        options: formData.type === 'mcq' ? formData.options.filter((option) => option.trim() !== '') : null,
        language: formData.type === 'coding' && formData.language ? formData.language : null,
        test_cases: formData.type === 'coding' && formData.test_cases.trim() !== '' ? JSON.parse(formData.test_cases) : null,
    }));

    const setOption = (index: number, value: string) => {
        const options = [...data.options];
        options[index] = value;
        setData('options', options);
    };

    const addOption = () => setData('options', [...data.options, '']);

    const removeOption = (index: number) => {
        const options = data.options.filter((_, i) => i !== index);
        setData('options', options);

        if (data.correctOption >= options.length) {
            setData('correctOption', 0);
        }
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        clearErrors('test_cases', 'options');

        if (data.type === 'mcq' && data.options.filter((option) => option.trim() !== '').length < 2) {
            setError('options', 'Add at least 2 options.');

            return;
        }

        if (data.type === 'coding' && data.test_cases.trim() !== '' && parseTestCases(data.test_cases) === null) {
            setError('test_cases', 'Test cases must be valid JSON.');

            return;
        }

        post(route('questions.store'), {
            preserveScroll: true,
            onSuccess: () => reset('prompt', 'reference_answer', 'test_cases', 'options', 'correctOption'),
        });
    };

    const verify = () => {
        clearErrors('test_cases', 'language');

        if (!data.language) {
            setError('language', 'Choose a language to verify against.');

            return;
        }

        if (data.test_cases.trim() === '') {
            setError('test_cases', 'Add test cases to verify against.');

            return;
        }

        const testCases = parseTestCases(data.test_cases);

        if (testCases === null) {
            setError('test_cases', 'Test cases must be valid JSON.');

            return;
        }

        setVerifying(true);
        router.post(
            route('questions.verify'),
            { language: data.language, reference_answer: data.reference_answer, test_cases: testCases },
            { preserveScroll: true, preserveState: true, onFinish: () => setVerifying(false) },
        );
    };

    return { DIFFICULTIES, data, setData, setOption, addOption, removeOption, submit, verify, verifying, processing, errors };
}
