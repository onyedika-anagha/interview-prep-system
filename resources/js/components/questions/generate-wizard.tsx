import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useGenerateWizard } from '@/hooks/use-generate-wizard';
import { type Topic } from '@/types/interview-prep';

export function GenerateWizard({ topics }: { topics: Topic[] }) {
    const { DIFFICULTIES, step, selectedTopic, data, setData, chooseTopic, back, submit, processing, errors } = useGenerateWizard(topics);

    return (
        <Card>
            <CardHeader className="flex-row items-center justify-between">
                <CardTitle className="text-base">Generate with AI</CardTitle>
                <Badge variant="outline">Step {step} of 2</Badge>
            </CardHeader>
            <CardContent className="flex flex-col gap-4">
                {step === 1 ? (
                    <>
                        <p className="text-muted-foreground text-sm">Choose a topic to generate questions for.</p>
                        <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            {topics.map((topic) => (
                                <Button
                                    key={topic.id}
                                    type="button"
                                    variant="outline"
                                    className="justify-start"
                                    onClick={() => chooseTopic(topic.id)}
                                >
                                    {topic.name}
                                </Button>
                            ))}
                        </div>
                    </>
                ) : (
                    <>
                        <p className="text-muted-foreground text-sm">
                            Generating for <span className="text-foreground font-medium">{selectedTopic?.name}</span>
                        </p>
                        <div className="flex flex-wrap gap-4">
                            <div className="grid gap-2">
                                <Label>Difficulty</Label>
                                <Select value={data.difficulty} onValueChange={(v) => setData('difficulty', v)}>
                                    <SelectTrigger className="w-40">
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
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="count">Count</Label>
                                <Input
                                    id="count"
                                    type="number"
                                    min={1}
                                    max={20}
                                    className="w-24"
                                    value={data.count}
                                    onChange={(e) => setData('count', Number(e.target.value))}
                                />
                            </div>
                        </div>
                        {errors.count && <p className="text-destructive text-sm">{errors.count}</p>}
                        {errors.difficulty && <p className="text-destructive text-sm">{errors.difficulty}</p>}
                    </>
                )}
            </CardContent>
            {step === 2 && (
                <CardFooter className="flex gap-2">
                    <Button type="button" variant="outline" onClick={back}>
                        Back
                    </Button>
                    <Button type="button" onClick={submit} disabled={processing}>
                        {processing ? 'Generating…' : 'Generate questions'}
                    </Button>
                </CardFooter>
            )}
        </Card>
    );
}
