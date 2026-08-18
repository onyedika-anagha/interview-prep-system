import { type QuestionType } from '@/types/interview-prep';

export const DIFFICULTIES = ['easy', 'medium', 'hard'] as const;

export const TYPE_LABELS: Record<QuestionType, string> = {
    mcq: 'Multiple choice',
    short_answer: 'Short answer',
    coding: 'Coding',
};

const DIFFICULTY_LABELS: Record<string, string> = {
    easy: 'Easy',
    medium: 'Medium',
    hard: 'Hard',
};

const LANGUAGE_LABELS: Record<string, string> = {
    javascript: 'JavaScript',
    php: 'PHP',
};

const REVIEW_STATUS_LABELS: Record<string, string> = {
    new: 'New',
    due: 'Due for review',
    scheduled: 'Reviewing',
};

export function difficultyLabel(difficulty: string) {
    return DIFFICULTY_LABELS[difficulty] ?? difficulty;
}

export function languageLabel(language: string) {
    return LANGUAGE_LABELS[language] ?? language;
}

export function reviewStatusLabel(status: string) {
    return REVIEW_STATUS_LABELS[status] ?? status;
}
