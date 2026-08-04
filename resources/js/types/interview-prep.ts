export type QuestionType = 'mcq' | 'short_answer' | 'coding';

export type TopicCategory = 'stack' | 'general';

export interface Topic {
    id: number;
    name: string;
    slug: string;
    category?: TopicCategory;
    description?: string | null;
}

export interface QuizQuestion {
    id: number;
    type: QuestionType;
    difficulty: string;
    prompt: string;
    language?: 'javascript' | 'php' | null;
}

export interface RevealedQuestion {
    id: number;
    type: QuestionType;
    prompt: string;
    reference_answer: string;
}

export interface TestCaseResult {
    input: unknown;
    expected_output: unknown;
    actual_output: unknown;
    passed: boolean;
    error: string | null;
}

export interface Attempt {
    is_correct: boolean;
    score: number;
    feedback: string;
    execution_result: TestCaseResult[] | null;
}

export interface TopicProgress {
    id: number;
    name: string;
    slug: string;
    attempt_count: number;
    accuracy: number;
    current_streak: number;
}
