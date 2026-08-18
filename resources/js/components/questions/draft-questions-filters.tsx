import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { difficultyLabel, DIFFICULTIES, TYPE_LABELS } from '@/lib/question-labels';
import { type DraftQuestionFilters, type QuestionType, type Topic } from '@/types/interview-prep';
import { router } from '@inertiajs/react';

const TYPES: QuestionType[] = ['mcq', 'short_answer', 'coding'];
const ALL = '__all__';

interface DraftQuestionsFiltersProps {
    topics: Topic[];
    filters: DraftQuestionFilters;
}

export function DraftQuestionsFilters({ topics, filters }: DraftQuestionsFiltersProps) {
    const applyFilter = (key: keyof DraftQuestionFilters, value: string) => {
        router.get(
            route('questions.manage'),
            { ...filters, [key]: value === ALL ? undefined : value },
            { preserveScroll: true, preserveState: true, replace: true },
        );
    };

    const hasFilters = !!(filters.topic_id || filters.type || filters.difficulty);

    return (
        <div className="flex flex-wrap items-center gap-2">
            <Select value={filters.topic_id ?? ALL} onValueChange={(v) => applyFilter('topic_id', v)}>
                <SelectTrigger className="w-40">
                    <SelectValue placeholder="Topic" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value={ALL}>All topics</SelectItem>
                    {topics.map((topic) => (
                        <SelectItem key={topic.id} value={String(topic.id)}>
                            {topic.name}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
            <Select value={filters.type ?? ALL} onValueChange={(v) => applyFilter('type', v)}>
                <SelectTrigger className="w-40">
                    <SelectValue placeholder="Type" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value={ALL}>All types</SelectItem>
                    {TYPES.map((type) => (
                        <SelectItem key={type} value={type}>
                            {TYPE_LABELS[type]}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
            <Select value={filters.difficulty ?? ALL} onValueChange={(v) => applyFilter('difficulty', v)}>
                <SelectTrigger className="w-40">
                    <SelectValue placeholder="Difficulty" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value={ALL}>All difficulties</SelectItem>
                    {DIFFICULTIES.map((level) => (
                        <SelectItem key={level} value={level}>
                            {difficultyLabel(level)}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
            {hasFilters && (
                <Button variant="ghost" size="sm" onClick={() => router.get(route('questions.manage'), {}, { preserveScroll: true })}>
                    Clear filters
                </Button>
            )}
        </div>
    );
}
