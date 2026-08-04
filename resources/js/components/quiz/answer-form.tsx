import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { type QuizQuestion } from '@/types/interview-prep';

interface AnswerFormProps {
    question: QuizQuestion;
    answer: string;
    onAnswerChange: (value: string) => void;
    onSubmit: () => void;
    submitting: boolean;
}

export function AnswerForm({ question, answer, onAnswerChange, onSubmit, submitting }: AnswerFormProps) {
    const isCoding = question.type === 'coding';

    return (
        <form
            className="flex flex-col gap-3"
            onSubmit={(e) => {
                e.preventDefault();
                onSubmit();
            }}
        >
            <Textarea
                value={answer}
                onChange={(e) => onAnswerChange(e.target.value)}
                placeholder={isCoding ? 'Write your solution here...' : 'Type your answer...'}
                className={isCoding ? 'min-h-64 font-mono text-sm' : undefined}
                spellCheck={!isCoding}
                autoFocus
            />
            <Button type="submit" disabled={submitting || answer.trim() === ''} className="self-start">
                {submitting ? 'Grading...' : 'Submit answer'}
            </Button>
        </form>
    );
}
