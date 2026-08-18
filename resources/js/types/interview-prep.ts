export type QuestionType = 'mcq' | 'short_answer' | 'coding';

export type TopicCategory = 'stack' | 'general';

export interface Topic {
    id: number;
    name: string;
    slug: string;
    category?: TopicCategory;
    description?: string | null;
}

export type ReviewStatus = 'new' | 'due' | 'scheduled';

export interface SampleTestCase {
    input: unknown;
    expected_output: unknown;
}

export interface QuizQuestion {
    id: number;
    type: QuestionType;
    difficulty: string;
    prompt: string;
    options?: string[] | null;
    language?: 'javascript' | 'php' | null;
    test_cases?: SampleTestCase[] | null;
    review_status?: ReviewStatus;
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

export interface DraftQuestion {
    id: number;
    topic_id: number;
    type: QuestionType;
    difficulty: string;
    prompt: string;
    reference_answer: string;
    options?: string[] | null;
    language?: 'javascript' | 'php' | null;
    test_cases?: SampleTestCase[] | null;
    generated_by: string;
    created_at: string;
    topic: { id: number; name: string };
}

export interface QuestionActionResult {
    type: 'generated' | 'added' | 'imported';
    created: number;
    errors: string[];
}

export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
}

export interface DraftQuestionFilters {
    topic_id?: string;
    type?: string;
    difficulty?: string;
}
