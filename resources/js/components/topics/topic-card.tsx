import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { difficultyLabel, DIFFICULTIES, TYPE_LABELS } from '@/lib/question-labels';
import { type QuestionType, type Topic } from '@/types/interview-prep';
import { Link } from '@inertiajs/react';
import { useId, useState } from 'react';

const TYPES: QuestionType[] = ['mcq', 'short_answer', 'coding'];
const ANY_TYPE = '__any__';

export function TopicCard({ topic }: { topic: Topic }) {
    const [difficulty, setDifficulty] = useState('easy');
    const [type, setType] = useState<QuestionType | ''>('');
    const difficultyId = useId();
    const typeId = useId();

    const quizUrl = `/topics/${topic.slug}/quiz?difficulty=${difficulty}${type ? `&type=${type}` : ''}`;

    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-base">{topic.name}</CardTitle>
            </CardHeader>
            <CardContent className="text-sm text-muted-foreground">{topic.description}</CardContent>
            <CardFooter className="flex flex-wrap items-center gap-2">
                <Label htmlFor={difficultyId} className="sr-only">
                    Difficulty
                </Label>
                <Select value={difficulty} onValueChange={setDifficulty}>
                    <SelectTrigger id={difficultyId} className="w-32">
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
                <Label htmlFor={typeId} className="sr-only">
                    Question type
                </Label>
                <Select value={type || ANY_TYPE} onValueChange={(v) => setType(v === ANY_TYPE ? '' : (v as QuestionType))}>
                    <SelectTrigger id={typeId} className="w-36">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value={ANY_TYPE}>All types</SelectItem>
                        {TYPES.map((t) => (
                            <SelectItem key={t} value={t}>
                                {TYPE_LABELS[t]}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Button asChild>
                    <Link href={quizUrl}>Start quiz</Link>
                </Button>
            </CardFooter>
        </Card>
    );
}
