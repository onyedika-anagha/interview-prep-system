import { formatJson } from '@/lib/format-json';
import { type SampleTestCase } from '@/types/interview-prep';

export function SampleTestCases({ testCases }: { testCases: SampleTestCase[] }) {
    return (
        <div className="flex flex-col gap-2">
            <p className="text-sm font-medium">Sample test cases</p>
            {testCases.map((testCase, index) => (
                <div key={index} className="flex flex-col gap-1 rounded-md border p-2 text-sm">
                    <span>
                        Input: <pre className="inline whitespace-pre-wrap">{formatJson(testCase.input)}</pre>
                    </span>
                    <span>
                        Expected: <pre className="inline whitespace-pre-wrap">{formatJson(testCase.expected_output)}</pre>
                    </span>
                </div>
            ))}
        </div>
    );
}
