import { cn } from '@/lib/utils';
import { useState } from 'react';

interface CodeBlockProps {
    content: string;
    language?: 'json' | 'xml' | 'text';
    className?: string;
    title?: string;
}

function formatJson(content: string): string {
    try {
        const parsed = JSON.parse(content);
        return JSON.stringify(parsed, null, 2);
    } catch {
        return content;
    }
}

// Simple syntax highlighting for JSON
function highlightJson(content: string): React.ReactNode {
    const lines = content.split('\n');

    return lines.map((line, lineIndex) => {
        const elements: React.ReactNode[] = [];
        let lastIndex = 0;
        let partIndex = 0;

        // Define highlighting patterns with precedence (keys first, then strings, then primitives)
        const keyRegex = /"([^"]+)"(\s*):/g;
        const stringRegex = /"([^"]+)"/g;
        const boolRegex = /\b(true|false)\b/g;
        const nullRegex = /\b(null)\b/g;
        const numRegex = /\b(\d+\.?\d*)\b/g;

        // First pass: find all keys (property names)
        const keyMatches: Array<{ index: number; length: number; value: string }> = [];
        let keyMatch;
        while ((keyMatch = keyRegex.exec(line)) !== null) {
            keyMatches.push({
                index: keyMatch.index,
                length: keyMatch[0].length,
                value: keyMatch[0],
            });
        }

        // Second pass: find all other tokens, excluding areas covered by keys
        const otherMatches: Array<{ index: number; length: number; value: string; type: string }> =
            [];

        const addMatchIfNotInKey = (match: RegExpExecArray, type: string): void => {
            const isInKey = keyMatches.some(
                (km) => match.index >= km.index && match.index < km.index + km.length,
            );
            if (!isInKey) {
                otherMatches.push({
                    index: match.index,
                    length: match[0].length,
                    value: match[0],
                    type,
                });
            }
        };

        let match;
        while ((match = stringRegex.exec(line)) !== null) {
            addMatchIfNotInKey(match, 'string');
        }
        while ((match = boolRegex.exec(line)) !== null) {
            addMatchIfNotInKey(match, 'boolean');
        }
        while ((match = nullRegex.exec(line)) !== null) {
            addMatchIfNotInKey(match, 'null');
        }
        while ((match = numRegex.exec(line)) !== null) {
            addMatchIfNotInKey(match, 'number');
        }

        // Combine and sort all matches by index
        const allMatches = [
            ...keyMatches.map((m) => ({ ...m, type: 'key' })),
            ...otherMatches,
        ].sort((a, b) => a.index - b.index);

        // Build elements
        allMatches.forEach((m) => {
            // Add text before this match
            if (m.index > lastIndex) {
                elements.push(line.slice(lastIndex, m.index));
            }

            // Add the highlighted match
            const colorClass = {
                key: 'text-terminal-cyan',
                string: 'text-terminal-green',
                boolean: 'text-terminal-yellow',
                null: 'text-terminal-purple',
                number: 'text-terminal-orange',
            }[m.type];

            elements.push(
                <span key={`${lineIndex}-${partIndex++}`} className={colorClass}>
                    {m.value}
                </span>,
            );

            lastIndex = m.index + m.length;
        });

        // Add remaining text
        if (lastIndex < line.length) {
            elements.push(line.slice(lastIndex));
        }

        return (
            <div key={lineIndex} className="leading-relaxed">
                {elements.length > 0 ? elements : line}
            </div>
        );
    });
}

export default function CodeBlock({
    content,
    language = 'text',
    className = '',
    title,
}: CodeBlockProps) {
    const [copied, setCopied] = useState(false);

    const handleCopy = async () => {
        await navigator.clipboard.writeText(content);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    const formattedContent = language === 'json' ? formatJson(content) : content;

    return (
        <div
            className={cn(
                'border-terminal-border bg-terminal-surface overflow-hidden rounded-lg border',
                className,
            )}
        >
            {/* Terminal header */}
            <div className="border-terminal-border bg-terminal-bg flex items-center justify-between border-b px-4 py-2">
                <div className="flex items-center gap-3">
                    <div className="flex items-center gap-1.5">
                        <span className="bg-terminal-red/80 h-2.5 w-2.5 rounded-full" />
                        <span className="bg-terminal-yellow/80 h-2.5 w-2.5 rounded-full" />
                        <span className="bg-terminal-green/80 h-2.5 w-2.5 rounded-full" />
                    </div>
                    {title && (
                        <span className="text-terminal-text-muted font-mono text-xs">{title}</span>
                    )}
                    {language !== 'text' && (
                        <span className="border-terminal-border bg-terminal-surface text-terminal-text-subtle rounded border px-1.5 py-0.5 font-mono text-[10px] uppercase">
                            {language}
                        </span>
                    )}
                </div>
                <button
                    onClick={handleCopy}
                    className={cn(
                        'flex items-center gap-1.5 rounded border px-2 py-1 font-mono text-xs transition-all',
                        copied
                            ? 'border-terminal-green/50 bg-terminal-green/10 text-terminal-green'
                            : 'border-terminal-border text-terminal-text-muted hover:border-terminal-green/50 hover:text-terminal-green',
                    )}
                    title="Copy to clipboard"
                >
                    {copied ? (
                        <>
                            <svg
                                className="h-3.5 w-3.5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                            copied
                        </>
                    ) : (
                        <>
                            <svg
                                className="h-3.5 w-3.5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                />
                            </svg>
                            copy
                        </>
                    )}
                </button>
            </div>

            {/* Code content */}
            <div className="bg-terminal-black overflow-x-auto p-4">
                <pre className="font-mono text-sm">
                    <code className="text-terminal-text">
                        {language === 'json' ? highlightJson(formattedContent) : formattedContent}
                    </code>
                </pre>
            </div>
        </div>
    );
}
