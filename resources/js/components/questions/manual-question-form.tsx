import InputError from '@/components/input-error';
import { TopicSelect } from '@/components/questions/topic-select';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useManualQuestionForm } from '@/hooks/use-manual-question-form';
import { type QuestionType, type Topic } from '@/types/interview-prep';

const TYPES: QuestionType[] = ['mcq', 'short_answer', 'coding'];

export function ManualQuestionForm({ topics }: { topics: Topic[] }) {
    const { DIFFICULTIES, data, setData, submit, processing, errors } = useManualQuestionForm(topics[0]?.id ?? '');

    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-base">Add a question manually</CardTitle>
            </CardHeader>
            <form onSubmit={submit}>
                <CardContent className="flex flex-col gap-4">
                    <div className="grid gap-2">
                        <Label>Topic</Label>
                        <TopicSelect topics={topics} value={data.topic_id} onChange={(id) => setData('topic_id', id)} />
                        <InputError message={errors.topic_id} />
                    </div>

                    <div className="grid gap-4 sm:grid-cols-3">
                        <div className="grid gap-2">
                            <Label>Type</Label>
                            <Select value={data.type} onValueChange={(v) => setData('type', v as QuestionType)}>
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {TYPES.map((type) => (
                                        <SelectItem key={type} value={type}>
                                            {type}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="grid gap-2">
                            <Label>Difficulty</Label>
                            <Select value={data.difficulty} onValueChange={(v) => setData('difficulty', v)}>
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {DIFFICULTIES.map((level) => (
                                        <SelectItem key={level} value={level}>
                                            {level}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        {data.type === 'coding' && (
                            <div className="grid gap-2">
                                <Label>Language</Label>
                                <Select value={data.language} onValueChange={(v) => setData('language', v as 'javascript' | 'php')}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Choose" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="javascript">javascript</SelectItem>
                                        <SelectItem value="php">php</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        )}
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="prompt">Prompt</Label>
                        <Textarea id="prompt" value={data.prompt} onChange={(e) => setData('prompt', e.target.value)} required />
                        <InputError message={errors.prompt} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="reference_answer">Reference answer</Label>
                        <Textarea
                            id="reference_answer"
                            value={data.reference_answer}
                            onChange={(e) => setData('reference_answer', e.target.value)}
                            required
                        />
                        <InputError message={errors.reference_answer} />
                    </div>

                    {data.type === 'coding' && (
                        <div className="grid gap-2">
                            <Label htmlFor="test_cases">Test cases (JSON array of {'{ input, expected_output }'})</Label>
                            <Textarea
                                id="test_cases"
                                className="min-h-24 font-mono text-sm"
                                value={data.test_cases}
                                onChange={(e) => setData('test_cases', e.target.value)}
                                placeholder='[{"input": 1, "expected_output": 2}]'
                            />
                            <InputError message={errors.test_cases} />
                        </div>
                    )}
                </CardContent>
                <CardFooter>
                    <Button type="submit" disabled={processing}>
                        {processing ? 'Adding…' : 'Add question'}
                    </Button>
                </CardFooter>
            </form>
        </Card>
    );
}
