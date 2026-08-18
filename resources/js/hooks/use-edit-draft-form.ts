import { parseTestCases } from '@/lib/parse-test-cases';
import { type DraftQuestion, type QuestionType } from '@/types/interview-prep';
import { useForm } from '@inertiajs/react';
import { type FormEventHandler } from 'react';

export function useEditDraftForm(question: DraftQuestion, onSaved: () => void) {
    const isMcq = question.type === 'mcq';
    const initialOptions = isMcq && question.options?.length ? question.options : ['', ''];
    const initialCorrectOption = isMcq ? Math.max(0, initialOptions.indexOf(question.reference_answer)) : 0;

    const form = useForm({
        type: question.type as QuestionType,
        difficulty: question.difficulty,
        prompt: question.prompt,
        reference_answer: question.reference_answer,
        options: initialOptions,
        correctOption: initialCorrectOption,
        language: (question.language ?? '') as '' | 'javascript' | 'php',
        test_cases: question.test_cases ? JSON.stringify(question.test_cases, null, 2) : '',
    });
    const { data, setData, patch, processing, errors, setError, clearErrors, transform } = form;

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

        patch(route('questions.update', question.id), {
            preserveScroll: true,
            onSuccess: onSaved,
        });
    };

    return { data, setData, setOption, addOption, removeOption, submit, processing, errors };
}
