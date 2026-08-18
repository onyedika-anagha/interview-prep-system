import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useEditDraftForm } from '@/hooks/use-edit-draft-form';
import { difficultyLabel, DIFFICULTIES, languageLabel, TYPE_LABELS } from '@/lib/question-labels';
import { type DraftQuestion, type QuestionType } from '@/types/interview-prep';
import { useState } from 'react';

const TYPES: QuestionType[] = ['mcq', 'short_answer', 'coding'];

export function EditDraftDialog({ question }: { question: DraftQuestion }) {
    const [open, setOpen] = useState(false);
    const { data, setData, setOption, addOption, removeOption, submit, processing, errors } = useEditDraftForm(question, () => setOpen(false));

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button size="sm" variant="outline">
                    Edit
                </Button>
            </DialogTrigger>
            <DialogContent className="max-h-[85vh] overflow-y-auto sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Edit draft question</DialogTitle>
                </DialogHeader>
                <form onSubmit={submit} className="flex flex-col gap-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div className="grid gap-2">
                            <Label>Type</Label>
                            <Select value={data.type} onValueChange={(v) => setData('type', v as QuestionType)}>
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {TYPES.map((type) => (
                                        <SelectItem key={type} value={type}>
                                            {TYPE_LABELS[type]}
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
                                            {difficultyLabel(level)}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="edit-prompt">Prompt</Label>
                        <Textarea id="edit-prompt" value={data.prompt} onChange={(e) => setData('prompt', e.target.value)} required />
                        <InputError message={errors.prompt} />
                    </div>

                    {data.type === 'mcq' ? (
                        <div className="grid gap-2">
                            <Label>Options (select the correct one)</Label>
                            <RadioGroup
                                value={String(data.correctOption)}
                                onValueChange={(v) => setData('correctOption', Number(v))}
                                className="gap-2"
                            >
                                {data.options.map((option, index) => (
                                    <div key={index} className="flex items-center gap-2">
                                        <RadioGroupItem value={String(index)} id={`edit-option-${index}`} />
                                        <Input
                                            value={option}
                                            onChange={(e) => setOption(index, e.target.value)}
                                            placeholder={`Option ${index + 1}`}
                                        />
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            onClick={() => removeOption(index)}
                                            disabled={data.options.length <= 2}
                                        >
                                            Remove
                                        </Button>
                                    </div>
                                ))}
                            </RadioGroup>
                            <Button type="button" variant="outline" size="sm" className="self-start" onClick={addOption}>
                                Add option
                            </Button>
                            <InputError message={errors.options} />
                        </div>
                    ) : (
                        <div className="grid gap-2">
                            <Label htmlFor="edit-reference-answer">Reference answer</Label>
                            <Textarea
                                id="edit-reference-answer"
                                value={data.reference_answer}
                                onChange={(e) => setData('reference_answer', e.target.value)}
                                required
                            />
                            <InputError message={errors.reference_answer} />
                        </div>
                    )}

                    {data.type === 'coding' && (
                        <>
                            <div className="grid gap-2">
                                <Label>Language</Label>
                                <Select value={data.language} onValueChange={(v) => setData('language', v as 'javascript' | 'php')}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Choose" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="javascript">{languageLabel('javascript')}</SelectItem>
                                        <SelectItem value="php">{languageLabel('php')}</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.language} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="edit-test-cases">Test cases (JSON array of {'{ input, expected_output }'})</Label>
                                <Textarea
                                    id="edit-test-cases"
                                    className="min-h-24 font-mono text-sm"
                                    value={data.test_cases}
                                    onChange={(e) => setData('test_cases', e.target.value)}
                                />
                                <InputError message={errors.test_cases} />
                            </div>
                        </>
                    )}

                    <DialogFooter>
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Saving…' : 'Save changes'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
