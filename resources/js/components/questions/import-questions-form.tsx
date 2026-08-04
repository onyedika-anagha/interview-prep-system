import InputError from '@/components/input-error';
import { TopicSelect } from '@/components/questions/topic-select';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useImportQuestionsForm } from '@/hooks/use-import-questions-form';
import { type Topic } from '@/types/interview-prep';

export function ImportQuestionsForm({ topics }: { topics: Topic[] }) {
    const { data, setData, submit, processing, errors } = useImportQuestionsForm(topics[0]?.id ?? '');

    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-base">Upload questions (JSON)</CardTitle>
            </CardHeader>
            <form onSubmit={submit}>
                <CardContent className="flex flex-col gap-4">
                    <p className="text-muted-foreground text-sm">
                        A JSON array of questions for one topic:{' '}
                        <code>{'{ type, difficulty, prompt, reference_answer, language?, test_cases? }'}</code>
                    </p>
                    <div className="grid gap-2">
                        <Label>Topic</Label>
                        <TopicSelect topics={topics} value={data.topic_id} onChange={(id) => setData('topic_id', id)} />
                        <InputError message={errors.topic_id} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="file">File</Label>
                        <Input id="file" type="file" accept="application/json,.json" onChange={(e) => setData('file', e.target.files?.[0] ?? null)} />
                        <InputError message={errors.file} />
                    </div>
                </CardContent>
                <CardFooter>
                    <Button type="submit" disabled={processing || !data.file}>
                        {processing ? 'Uploading…' : 'Upload'}
                    </Button>
                </CardFooter>
            </form>
        </Card>
    );
}
