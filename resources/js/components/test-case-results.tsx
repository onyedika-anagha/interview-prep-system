import { formatJson } from '@/lib/format-json';
import { type TestCaseResult } from '@/types/interview-prep';
import { CheckCircle2, XCircle } from 'lucide-react';

export function TestCaseResults({ results }: { results: TestCaseResult[] }) {
    return (
        <div className="flex flex-col gap-2">
            {results.map((result, index) => (
                <div key={index} className="flex items-start gap-2 rounded-md border p-2 text-sm">
                    {result.passed ? (
                        <CheckCircle2 className="mt-0.5 size-4 shrink-0 text-green-600" />
                    ) : (
                        <XCircle className="mt-0.5 size-4 shrink-0 text-destructive" />
                    )}
                    <div className="flex flex-col gap-1 overflow-x-auto">
                        <span>
                            Input: <pre className="inline whitespace-pre-wrap">{formatJson(result.input)}</pre>
                        </span>
                        <span>
                            Expected: <pre className="inline whitespace-pre-wrap">{formatJson(result.expected_output)}</pre>
                        </span>
                        <span>
                            Got: <pre className="inline whitespace-pre-wrap">{formatJson(result.actual_output)}</pre>
                        </span>
                        {result.error && <span className="text-destructive">{result.error}</span>}
                    </div>
                </div>
            ))}
        </div>
    );
}
