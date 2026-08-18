import { TestCaseResults } from '@/components/test-case-results';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Textarea } from '@/components/ui/textarea';
import { type QuizQuestion, type TestCaseResult } from '@/types/interview-prep';
import { type KeyboardEvent, useId } from 'react';

interface AnswerFormProps {
    question: QuizQuestion;
    answer: string;
    onAnswerChange: (value: string) => void;
    onSubmit: () => void;
    submitting: boolean;
    onRun: () => void;
    running: boolean;
    runResult: TestCaseResult[] | null;
    executionTimeoutSeconds: number;
}

export function AnswerForm({
    question,
    answer,
    onAnswerChange,
    onSubmit,
    submitting,
    onRun,
    running,
    runResult,
    executionTimeoutSeconds,
}: AnswerFormProps) {
    const isCoding = question.type === 'coding';
    const isMcq = question.type === 'mcq' && !!question.options?.length;
    const answerId = useId();

    // Native textareas move focus to the next element on Tab, which makes indenting code impossible.
    const handleTabIndent = (e: KeyboardEvent<HTMLTextAreaElement>) => {
        if (e.key !== 'Tab') return;
        e.preventDefault();

        const target = e.currentTarget;
        const { selectionStart, selectionEnd } = target;
        onAnswerChange(answer.slice(0, selectionStart) + '  ' + answer.slice(selectionEnd));

        requestAnimationFrame(() => {
            target.selectionStart = target.selectionEnd = selectionStart + 2;
        });
    };

    return (
        <form
            className="flex flex-col gap-3"
            onSubmit={(e) => {
                e.preventDefault();
                onSubmit();
            }}
        >
            {isMcq ? (
                <RadioGroup value={answer} onValueChange={onAnswerChange}>
                    {question.options?.map((option, index) => (
                        <div key={index} className="flex items-center gap-2">
                            <RadioGroupItem value={option} id={`option-${index}`} />
                            <Label htmlFor={`option-${index}`} className="font-normal">
                                {option}
                            </Label>
                        </div>
                    ))}
                </RadioGroup>
            ) : (
                <>
                    <Label htmlFor={answerId} className="sr-only">
                        {isCoding ? 'Your solution' : 'Your answer'}
                    </Label>
                    <Textarea
                        id={answerId}
                        value={answer}
                        onChange={(e) => onAnswerChange(e.target.value)}
                        onKeyDown={isCoding ? handleTabIndent : undefined}
                        placeholder={isCoding ? 'Write your solution here...' : 'Type your answer...'}
                        className={isCoding ? 'min-h-64 font-mono text-sm' : undefined}
                        spellCheck={!isCoding}
                        autoFocus
                    />
                </>
            )}
            {isCoding && (
                <p className="text-muted-foreground text-xs">
                    Each test case has {executionTimeoutSeconds} second{executionTimeoutSeconds === 1 ? '' : 's'} to run.
                </p>
            )}
            <div className="flex gap-2">
                {isCoding && (
                    <Button type="button" variant="outline" onClick={onRun} disabled={running || answer.trim() === ''}>
                        {running ? 'Running…' : 'Run against sample test cases'}
                    </Button>
                )}
                <Button type="submit" disabled={submitting || answer.trim() === ''}>
                    {submitting ? 'Grading...' : 'Submit answer'}
                </Button>
            </div>
            {runResult && <TestCaseResults results={runResult} />}
        </form>
    );
}
