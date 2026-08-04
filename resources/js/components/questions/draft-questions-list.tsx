import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { type DraftQuestion } from '@/types/interview-prep';
import { router } from '@inertiajs/react';

export function DraftQuestionsList({ questions }: { questions: DraftQuestion[] }) {
    const approve = (id: number) => router.patch(route('questions.approve', id), {}, { preserveScroll: true });
    const reject = (id: number) => router.delete(route('questions.destroy', id), { preserveScroll: true });

    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-base">Pending review ({questions.length})</CardTitle>
            </CardHeader>
            <CardContent className="flex flex-col gap-3">
                {questions.length === 0 && <p className="text-muted-foreground text-sm">No draft questions right now.</p>}
                {questions.map((question) => (
                    <div key={question.id} className="flex items-start justify-between gap-4 rounded-lg border p-3">
                        <div className="flex flex-col gap-1">
                            <div className="flex flex-wrap items-center gap-2">
                                <Badge variant="secondary">{question.topic.name}</Badge>
                                <Badge variant="outline">{question.type}</Badge>
                                <Badge variant="outline">{question.difficulty}</Badge>
                                <span className="text-muted-foreground text-xs">via {question.generated_by}</span>
                            </div>
                            <p className="line-clamp-2 text-sm">{question.prompt}</p>
                        </div>
                        <div className="flex shrink-0 gap-2">
                            <Button size="sm" onClick={() => approve(question.id)}>
                                Approve
                            </Button>
                            <Button size="sm" variant="outline" onClick={() => reject(question.id)}>
                                Reject
                            </Button>
                        </div>
                    </div>
                ))}
            </CardContent>
        </Card>
    );
}
