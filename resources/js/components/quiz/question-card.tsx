import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { type QuizQuestion } from '@/types/interview-prep';

interface QuestionCardProps {
    question: QuizQuestion;
    questionNumber: number;
}

const TYPE_LABELS: Record<QuizQuestion['type'], string> = {
    mcq: 'Multiple choice',
    short_answer: 'Short answer',
    coding: 'Coding',
};

export function QuestionCard({ question, questionNumber }: QuestionCardProps) {
    return (
        <Card>
            <CardHeader>
                <div className="flex items-center gap-2">
                    <Badge variant="secondary">Question {questionNumber}</Badge>
                    <Badge variant="outline">{TYPE_LABELS[question.type]}</Badge>
                    <Badge variant="outline">{question.difficulty}</Badge>
                    {question.language && <Badge variant="outline">{question.language}</Badge>}
                </div>
                <CardTitle className="whitespace-pre-wrap text-lg font-medium">{question.prompt}</CardTitle>
            </CardHeader>
            <CardContent />
        </Card>
    );
}
