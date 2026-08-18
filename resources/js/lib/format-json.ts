// Pretty-prints nested objects/arrays across multiple lines; scalars still render on one line.
export function formatJson(value: unknown) {
    return JSON.stringify(value, null, 2);
}
