import { DraftQuestionsList } from '@/components/questions/draft-questions-list';
import { GenerateWizard } from '@/components/questions/generate-wizard';
import { ImportQuestionsForm } from '@/components/questions/import-questions-form';
import { ManualQuestionForm } from '@/components/questions/manual-question-form';
import { ResultBanner } from '@/components/questions/result-banner';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import {
    type DraftQuestion,
    type DraftQuestionFilters,
    type Paginated,
    type QuestionActionResult,
    type TestCaseResult,
    type Topic,
} from '@/types/interview-prep';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Manage questions', href: '/questions/manage' }];

interface ManageQuestionsProps {
    topics: Topic[];
    draftQuestions: Paginated<DraftQuestion>;
    filters: DraftQuestionFilters;
    result: QuestionActionResult | null;
    verification: TestCaseResult[] | null;
}

export default function ManageQuestions({ topics, draftQuestions, filters, result, verification }: ManageQuestionsProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Manage questions" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <h1 className="text-xl font-semibold">Manage questions</h1>

                {result && <ResultBanner result={result} />}

                {topics.length === 0 ? (
                    <p className="text-muted-foreground text-sm">Create a topic first before adding questions.</p>
                ) : (
                    <div className="grid items-start gap-6 xl:grid-cols-3">
                        <GenerateWizard topics={topics} />
                        <ManualQuestionForm topics={topics} verification={verification} />
                        <ImportQuestionsForm topics={topics} />
                    </div>
                )}

                <DraftQuestionsList questions={draftQuestions} topics={topics} filters={filters} />
            </div>
        </AppLayout>
    );
}
