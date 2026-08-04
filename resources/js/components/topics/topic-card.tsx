import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { type Topic } from '@/types/interview-prep';
import { Link } from '@inertiajs/react';
import { useState } from 'react';

const DIFFICULTIES = ['easy', 'medium', 'hard'];

export function TopicCard({ topic }: { topic: Topic }) {
    const [difficulty, setDifficulty] = useState('easy');

    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-base">{topic.name}</CardTitle>
            </CardHeader>
            <CardContent className="text-sm text-muted-foreground">{topic.description}</CardContent>
            <CardFooter className="flex items-center gap-2">
                <Select value={difficulty} onValueChange={setDifficulty}>
                    <SelectTrigger className="w-32">
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
                <Button asChild>
                    <Link href={`/topics/${topic.slug}/quiz?difficulty=${difficulty}`}>Start quiz</Link>
                </Button>
            </CardFooter>
        </Card>
    );
}
