import { useState } from 'react';

export function useCopyToClipboard(resetMs = 2000) {
    const [copiedText, setCopiedText] = useState<string | null>(null);

    const copy = async (text: string) => {
        try {
            await navigator.clipboard.writeText(text);
            setCopiedText(text);
            setTimeout(() => setCopiedText(null), resetMs);
        } catch {
            // clipboard not available
        }
    };

    return { copied: copiedText !== null, copiedText, copy };
}
