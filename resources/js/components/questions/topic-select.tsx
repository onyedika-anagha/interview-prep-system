import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { type Topic } from '@/types/interview-prep';

interface TopicSelectProps {
    topics: Topic[];
    value: number | '';
    onChange: (topicId: number) => void;
}

export function TopicSelect({ topics, value, onChange }: TopicSelectProps) {
    return (
        <Select value={value ? String(value) : undefined} onValueChange={(v) => onChange(Number(v))}>
            <SelectTrigger>
                <SelectValue placeholder="Choose a topic" />
            </SelectTrigger>
            <SelectContent>
                {topics.map((topic) => (
                    <SelectItem key={topic.id} value={String(topic.id)}>
                        {topic.name}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}
