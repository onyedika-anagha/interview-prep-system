import { TestCaseResults } from '@/components/test-case-results';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { type Attempt, type RevealedQuestion } from '@/types/interview-prep';
import { CheckCircle2, XCircle } from 'lucide-react';

interface ResultFeedbackProps {
    question: RevealedQuestion;
    attempt: Attempt;
}

export function ResultFeedback({ question, attempt }: ResultFeedbackProps) {
    return (
        <div className="flex flex-col gap-4">
            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2 text-lg">
                        {attempt.is_correct ? (
                            <CheckCircle2 className="size-5 text-green-600" />
                        ) : (
                            <XCircle className="size-5 text-destructive" />
                        )}
                        {attempt.is_correct ? 'Correct' : 'Not quite'}
                        <Badge variant="secondary">{attempt.score}/100</Badge>
                    </CardTitle>
                </CardHeader>
                <CardContent className="whitespace-pre-wrap text-sm text-muted-foreground">{attempt.feedback}</CardContent>
            </Card>

            {attempt.execution_result && (
                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Test cases</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <TestCaseResults results={attempt.execution_result} />
                    </CardContent>
                </Card>
            )}

            <Card>
                <CardHeader>
                    <CardTitle className="text-base">Reference answer</CardTitle>
                    {question.type === 'coding' && (
                        <p className="text-muted-foreground text-sm">
                            Includes the stdin/stdout wrapper used for automated grading — the core logic is what matters.
                        </p>
                    )}
                </CardHeader>
                <CardContent className="whitespace-pre-wrap font-mono text-sm text-muted-foreground">{question.reference_answer}</CardContent>
            </Card>
        </div>
    );
}
