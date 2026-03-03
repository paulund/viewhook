import type { Url } from '@/types';
import { Link } from '@inertiajs/react';
import { useState } from 'react';

interface UrlCardProps {
    url: Url;
}

export default function UrlCard({ url }: UrlCardProps) {
    const [copied, setCopied] = useState(false);

    const copyToClipboard = async () => {
        try {
            await navigator.clipboard.writeText(url.endpoint_url);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        } catch (err) {
            console.error('Failed to copy URL:', err);
        }
    };

    const formatTimeAgo = (dateString: string | null): string => {
        if (!dateString) return 'Never';
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now.getTime() - date.getTime()) / 1000);

        if (seconds < 60) return 'Just now';
        if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
        if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
        return `${Math.floor(seconds / 86400)}d ago`;
    };

    return (
        <div className="group border-terminal-border bg-terminal-bg hover:border-terminal-green/30 hover:terminal-glow rounded-lg border p-6 transition-all">
            <div className="flex items-start justify-between">
                <div className="min-w-0 flex-1">
                    <Link
                        href={route('urls.show', url.id)}
                        className="text-terminal-text hover:text-terminal-green font-mono text-lg font-semibold"
                    >
                        {url.name}
                    </Link>
                    {url.description && (
                        <p className="text-terminal-text-muted mt-1 font-mono text-sm">
                            # {url.description}
                        </p>
                    )}
                </div>
            </div>

            <div className="mt-4">
                <div className="flex items-center gap-2">
                    <code className="border-terminal-border bg-terminal-black text-terminal-cyan flex-1 truncate rounded border px-3 py-2 font-mono text-xs">
                        {url.endpoint_url}
                    </code>
                    <button
                        type="button"
                        onClick={copyToClipboard}
                        className={`rounded border px-3 py-2 font-mono text-xs transition-all ${
                            copied
                                ? 'border-terminal-green bg-terminal-green/20 text-terminal-green'
                                : 'border-terminal-border text-terminal-text-muted hover:border-terminal-green/50 hover:text-terminal-green'
                        }`}
                        title="Copy URL"
                    >
                        {copied ? '✓ copied' : 'copy'}
                    </button>
                </div>
            </div>

            <div className="text-terminal-text-subtle mt-4 flex items-center justify-between font-mono text-xs">
                <span className="flex items-center gap-1">
                    {url.requests_count !== undefined && (
                        <>
                            <span className="text-terminal-purple">→</span>
                            {url.requests_count} request{url.requests_count !== 1 && 's'}
                        </>
                    )}
                </span>
                <span className="flex items-center gap-1">
                    {url.last_request_at ? (
                        <>
                            <span className="text-terminal-yellow">⏱</span>
                            {formatTimeAgo(url.last_request_at)}
                        </>
                    ) : (
                        'No requests yet'
                    )}
                </span>
            </div>
            <div className="text-terminal-text-muted mt-2 font-mono text-xs">
                <span className="text-terminal-yellow">~</span> data expires after{' '}
                {url.requests_expire_after_hours >= 24
                    ? `${url.requests_expire_after_hours / 24}d`
                    : `${url.requests_expire_after_hours}h`}
            </div>
        </div>
    );
}
