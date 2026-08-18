import { useIsDark } from '@/hooks/use-is-dark';
import { indentWithTab } from '@codemirror/commands';
import { javascript } from '@codemirror/lang-javascript';
import { php } from '@codemirror/lang-php';
import { EditorView, keymap } from '@codemirror/view';
import { githubDark, githubLight } from '@uiw/codemirror-theme-github';
import CodeMirror from '@uiw/react-codemirror';

interface CodeEditorProps {
    language: 'javascript' | 'php';
    value: string;
    onChange: (value: string) => void;
    placeholder?: string;
    ariaLabel?: string;
    autoFocus?: boolean;
}

const LANGUAGE_EXTENSIONS = {
    javascript: javascript(),
    php: php(),
};

export function CodeEditor({ language, value, onChange, placeholder, ariaLabel, autoFocus }: CodeEditorProps) {
    const isDark = useIsDark();

    return (
        <CodeMirror
            value={value}
            onChange={onChange}
            extensions={[
                LANGUAGE_EXTENSIONS[language],
                keymap.of([indentWithTab]),
                // Grammarly attaches to any contenteditable region; it doesn't belong on a code editor.
                EditorView.contentAttributes.of({ 'aria-label': ariaLabel ?? '', 'data-gramm': 'false' }),
            ]}
            theme={isDark ? githubDark : githubLight}
            placeholder={placeholder}
            autoFocus={autoFocus}
            minHeight="16rem"
            className="border-input overflow-hidden rounded-md border text-sm"
            basicSetup={{ autocompletion: false }}
        />
    );
}
