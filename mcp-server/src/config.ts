export const API_BASE_URL = process.env.INTERVIEW_PREP_API_URL ?? 'http://127.0.0.1:8000/api';

export async function apiFetch(path: string, init?: RequestInit): Promise<unknown> {
    const res = await fetch(`${API_BASE_URL}${path}`, {
        ...init,
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            ...init?.headers,
        },
    });

    const body = await res.json().catch(() => null);

    if (!res.ok) {
        throw new Error(`Request to ${path} failed (${res.status}): ${JSON.stringify(body)}`);
    }

    return body;
}
