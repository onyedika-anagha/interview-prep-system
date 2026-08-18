// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function parseTestCases(raw: string): any[] | null {
    try {
        return JSON.parse(raw);
    } catch {
        return null;
    }
}
