import { SampleTestCases } from '@/components/quiz/sample-test-cases';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { difficultyLabel, languageLabel, reviewStatusLabel, TYPE_LABELS } from '@/lib/question-labels';
import { type QuizQuestion } from '@/types/interview-prep';

interface QuestionCardProps {
    question: QuizQuestion;
    questionNumber: number;
    totalQuestions: number;
}

export function QuestionCard({ question, questionNumber, totalQuestions }: QuestionCardProps) {
    return (
        <Card>
            <CardHeader>
                <div className="flex flex-wrap items-center gap-2">
                    <Badge variant="secondary">
                        Question {questionNumber} · {totalQuestions} available
                    </Badge>
                    <Badge variant="outline">{TYPE_LABELS[question.type]}</Badge>
                    <Badge variant="outline">{difficultyLabel(question.difficulty)}</Badge>
                    {question.language && <Badge variant="outline">{languageLabel(question.language)}</Badge>}
                    {question.review_status && question.review_status !== 'scheduled' && (
                        <Badge variant={question.review_status === 'due' ? 'default' : 'outline'}>
                            {reviewStatusLabel(question.review_status)}
                        </Badge>
                    )}
                </div>
                <CardTitle className="whitespace-pre-wrap text-lg font-medium">{question.prompt}</CardTitle>
            </CardHeader>
            {question.type === 'coding' && question.test_cases && question.test_cases.length > 0 && (
                <CardContent>
                    <SampleTestCases testCases={question.test_cases} />
                </CardContent>
            )}
        </Card>
    );
}
