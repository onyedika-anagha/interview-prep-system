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
                    <CardContent className="flex flex-col gap-2">
                        {attempt.execution_result.map((result, index) => (
                            <div key={index} className="flex items-start gap-2 rounded-md border p-2 text-sm">
                                {result.passed ? (
                                    <CheckCircle2 className="mt-0.5 size-4 shrink-0 text-green-600" />
                                ) : (
                                    <XCircle className="mt-0.5 size-4 shrink-0 text-destructive" />
                                )}
                                <div className="flex flex-col gap-1">
                                    <span>Input: {JSON.stringify(result.input)}</span>
                                    <span>Expected: {JSON.stringify(result.expected_output)}</span>
                                    <span>Got: {JSON.stringify(result.actual_output)}</span>
                                    {result.error && <span className="text-destructive">{result.error}</span>}
                                </div>
                            </div>
                        ))}
                    </CardContent>
                </Card>
            )}

            <Card>
                <CardHeader>
                    <CardTitle className="text-base">Reference answer</CardTitle>
                </CardHeader>
                <CardContent className="whitespace-pre-wrap font-mono text-sm text-muted-foreground">{question.reference_answer}</CardContent>
            </Card>
        </div>
    );
}
