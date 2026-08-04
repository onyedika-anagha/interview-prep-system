import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { type TopicProgress } from '@/types/interview-prep';
import { Flame } from 'lucide-react';

export function TopicStats({ stat }: { stat: TopicProgress }) {
    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-base">{stat.name}</CardTitle>
            </CardHeader>
            <CardContent className="flex items-center justify-between text-sm">
                <div className="flex flex-col">
                    <span className="text-2xl font-semibold">{stat.accuracy}%</span>
                    <span className="text-muted-foreground">accuracy · {stat.attempt_count} attempts</span>
                </div>
                {stat.current_streak > 0 && (
                    <div className="flex items-center gap-1 text-orange-500">
                        <Flame className="size-4" />
                        <span className="font-medium">{stat.current_streak}</span>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
