import { useForm } from '@inertiajs/react';
import { type FormEventHandler } from 'react';

export function useImportQuestionsForm(defaultTopicId: number | '') {
    const { data, setData, post, processing, errors, reset } = useForm<{ topic_id: number | ''; file: File | null }>({
        topic_id: defaultTopicId,
        file: null,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('questions.import'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => reset('file'),
        });
    };

    return { data, setData, submit, processing, errors };
}
