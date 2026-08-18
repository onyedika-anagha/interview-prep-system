import { CodeEditor } from '@/components/quiz/code-editor';
import { TestCaseResults } from '@/components/test-case-results';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Textarea } from '@/components/ui/textarea';
import { type QuizQuestion, type TestCaseResult } from '@/types/interview-prep';
import { useId } from 'react';

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

    return (
        <form
            className="flex flex-col gap-3"
            onSubmit={(e) => {
                e.preventDefault();
                onSubmit();
            }}
        >
            {isMcq ? (
                <RadioGroup value={answer} onValueChange={onAnswerChange} className="gap-2">
                    {question.options?.map((option, index) => {
                        const optionId = `${answerId}-option-${index}`;
                        return (
                            <div
                                key={index}
                                className="has-[[data-state=checked]]:border-primary has-[[data-state=checked]]:bg-primary/5 flex items-center gap-3 rounded-lg border border-input p-3 transition-colors hover:bg-accent"
                            >
                                <RadioGroupItem value={option} id={optionId} />
                                <Label htmlFor={optionId} className="flex-1 cursor-pointer font-mono text-sm font-normal">
                                    {option}
                                </Label>
                            </div>
                        );
                    })}
                </RadioGroup>
            ) : (
                <>
                    {isCoding ? (
                        <CodeEditor
                            language={question.language ?? 'javascript'}
                            value={answer}
                            onChange={onAnswerChange}
                            placeholder="Write your solution here..."
                            ariaLabel="Your solution"
                            autoFocus
                        />
                    ) : (
                        <>
                            <Label htmlFor={answerId} className="sr-only">
                                Your answer
                            </Label>
                            <Textarea
                                id={answerId}
                                value={answer}
                                onChange={(e) => onAnswerChange(e.target.value)}
                                placeholder="Type your answer..."
                                autoFocus
                            />
                        </>
                    )}
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
