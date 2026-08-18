import { ConfirmButton } from '@/components/confirm-button';
import { DraftQuestionsFilters } from '@/components/questions/draft-questions-filters';
import { EditDraftDialog } from '@/components/questions/edit-draft-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { difficultyLabel, TYPE_LABELS } from '@/lib/question-labels';
import { type DraftQuestion, type DraftQuestionFilters, type Paginated, type Topic } from '@/types/interview-prep';
import { router } from '@inertiajs/react';
import { useState } from 'react';

interface DraftQuestionsListProps {
    questions: Paginated<DraftQuestion>;
    topics: Topic[];
    filters: DraftQuestionFilters;
}

export function DraftQuestionsList({ questions, topics, filters }: DraftQuestionsListProps) {
    const [selected, setSelected] = useState<number[]>([]);

    const approve = (id: number) => router.patch(route('questions.approve', id), {}, { preserveScroll: true });
    const reject = (id: number) => router.delete(route('questions.destroy', id), { preserveScroll: true });

    const toggleSelected = (id: number, checked: boolean) => {
        setSelected((prev) => (checked ? [...prev, id] : prev.filter((selectedId) => selectedId !== id)));
    };

    const bulkApprove = () => {
        router.patch(route('questions.bulk-approve'), { ids: selected }, { preserveScroll: true, onSuccess: () => setSelected([]) });
    };

    const bulkReject = () => {
        router.delete(route('questions.bulk-destroy'), {
            data: { ids: selected },
            preserveScroll: true,
            onSuccess: () => setSelected([]),
        });
    };

    const goToPage = (page: number) => {
        router.get(route('questions.manage'), { ...filters, page }, { preserveScroll: true, preserveState: true });
    };

    return (
        <Card>
            <CardHeader className="flex-row flex-wrap items-center justify-between gap-4">
                <CardTitle className="text-base">Pending review ({questions.total})</CardTitle>
                <DraftQuestionsFilters topics={topics} filters={filters} />
            </CardHeader>
            <CardContent className="flex flex-col gap-3">
                {questions.data.length === 0 && <p className="text-muted-foreground text-sm">No draft questions match.</p>}

                {questions.data.length > 0 && (
                    <div className="flex items-center gap-2 border-b pb-2">
                        <Checkbox
                            checked={selected.length === questions.data.length}
                            onCheckedChange={(checked) => setSelected(checked ? questions.data.map((question) => question.id) : [])}
                        />
                        <span className="text-muted-foreground text-sm">
                            {selected.length > 0 ? `${selected.length} selected` : 'Select all on this page'}
                        </span>
                        {selected.length > 0 && (
                            <div className="ml-auto flex gap-2">
                                <Button size="sm" onClick={bulkApprove}>
                                    Approve selected
                                </Button>
                                <ConfirmButton
                                    trigger={
                                        <Button size="sm" variant="outline">
                                            Reject selected
                                        </Button>
                                    }
                                    title={`Reject ${selected.length} question${selected.length === 1 ? '' : 's'}?`}
                                    description="This permanently deletes the selected drafts. This can't be undone."
                                    confirmLabel="Reject"
                                    onConfirm={bulkReject}
                                />
                            </div>
                        )}
                    </div>
                )}

                {questions.data.map((question) => (
                    <div key={question.id} className="flex items-start justify-between gap-4 rounded-lg border p-3">
                        <div className="flex items-start gap-3">
                            <Checkbox
                                checked={selected.includes(question.id)}
                                onCheckedChange={(checked) => toggleSelected(question.id, !!checked)}
                                className="mt-1"
                            />
                            <div className="flex flex-col gap-1">
                                <div className="flex flex-wrap items-center gap-2">
                                    <Badge variant="secondary">{question.topic.name}</Badge>
                                    <Badge variant="outline">{TYPE_LABELS[question.type]}</Badge>
                                    <Badge variant="outline">{difficultyLabel(question.difficulty)}</Badge>
                                    <span className="text-muted-foreground text-xs">via {question.generated_by}</span>
                                </div>
                                <p className="line-clamp-2 text-sm">{question.prompt}</p>
                            </div>
                        </div>
                        <div className="flex shrink-0 gap-2">
                            <EditDraftDialog question={question} />
                            <Button size="sm" onClick={() => approve(question.id)}>
                                Approve
                            </Button>
                            <ConfirmButton
                                trigger={
                                    <Button size="sm" variant="outline">
                                        Reject
                                    </Button>
                                }
                                title="Reject this question?"
                                description="This permanently deletes the draft. This can't be undone."
                                confirmLabel="Reject"
                                onConfirm={() => reject(question.id)}
                            />
                        </div>
                    </div>
                ))}

                {questions.last_page > 1 && (
                    <div className="flex items-center justify-between pt-2">
                        <Button
                            variant="outline"
                            size="sm"
                            disabled={questions.current_page <= 1}
                            onClick={() => goToPage(questions.current_page - 1)}
                        >
                            Previous
                        </Button>
                        <span className="text-muted-foreground text-sm">
                            Page {questions.current_page} of {questions.last_page}
                        </span>
                        <Button
                            variant="outline"
                            size="sm"
                            disabled={questions.current_page >= questions.last_page}
                            onClick={() => goToPage(questions.current_page + 1)}
                        >
                            Next
                        </Button>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
