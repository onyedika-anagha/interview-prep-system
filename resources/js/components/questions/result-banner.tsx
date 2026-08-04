import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { type QuestionActionResult } from '@/types/interview-prep';

const TITLES: Record<QuestionActionResult['type'], string> = {
    generated: 'Generated',
    added: 'Added',
    imported: 'Imported',
};

export function ResultBanner({ result }: { result: QuestionActionResult }) {
    return (
        <Alert variant={result.errors.length > 0 ? 'destructive' : 'default'}>
            <AlertTitle>{TITLES[result.type]}</AlertTitle>
            <AlertDescription>
                <p>
                    {result.created} question{result.created === 1 ? '' : 's'} added as drafts.
                </p>
                {result.errors.length > 0 && (
                    <ul className="mt-2 list-disc pl-5">
                        {result.errors.map((error, index) => (
                            <li key={index}>{error}</li>
                        ))}
                    </ul>
                )}
            </AlertDescription>
        </Alert>
    );
}
