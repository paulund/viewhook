import DeleteUrlModal from '@/Components/DeleteUrlModal';
import ForwardingConfigCard from '@/Components/ForwardingConfigCard';
import NotificationsConfigCard from '@/Components/NotificationsConfigCard';
import { RateLimitIndicator } from '@/Components/RateLimitIndicator';
import RequestListItem from '@/Components/RequestListItem';
import TerminalCard, { TerminalLine } from '@/Components/TerminalCard';
import { useUrlChannel } from '@/hooks/use-url-channel';
import { useCopyToClipboard } from '@/hooks/useCopyToClipboard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { UrlShowProps } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { useCallback, useState } from 'react';

export default function Show({ url, recentForwards }: UrlShowProps) {
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const { copied, copy: copyToClipboard } = useCopyToClipboard();
    const { copied: curlCopied, copy: copyCurlToClipboard } = useCopyToClipboard();

    const handleRequestCaptured = useCallback(() => {
        router.reload({ only: ['url'] });
    }, []);

    useUrlChannel({
        urlId: url.id,
        onRequestCaptured: handleRequestCaptured,
    });

    const copyCurlCommand = async () => {
        const curlCommand = `curl -X POST ${url.endpoint_url} \\
  -H "Content-Type: application/json" \\
  -H "User-Agent: MyApp/1.0" \\
  -d '{"event": "test", "data": {"message": "Hello, viewhook!"}}'`;
        await copyCurlToClipboard(curlCommand);
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <Link
                            href={route('urls.index')}
                            className="text-terminal-text-muted hover:text-terminal-green transition-colors"
                        >
                            <svg
                                className="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M15 19l-7-7 7-7"
                                />
                            </svg>
                        </Link>
                        <span className="text-terminal-text font-mono text-xl font-semibold">
                            <span className="text-terminal-green">$</span> inspect --url
                        </span>
                        <span className="text-terminal-cyan font-mono text-lg">{url.name}</span>
                    </div>
                    <button
                        onClick={() => setShowDeleteModal(true)}
                        className="border-terminal-red/50 bg-terminal-red/10 text-terminal-red hover:bg-terminal-red/20 flex items-center gap-2 rounded border px-4 py-2 font-mono text-sm transition-all"
                    >
                        <svg
                            className="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                            />
                        </svg>
                        delete
                    </button>
                </div>
            }
        >
            <Head title={url.name} />

            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                    {/* Endpoint URL Card */}
                    <TerminalCard
                        title="endpoint"
                        subtitle="webhook capture url"
                        variant="success"
                        glow
                    >
                        <div className="space-y-4">
                            <div className="space-y-2">
                                <TerminalLine
                                    command="echo $WEBHOOK_URL"
                                    output={url.endpoint_url}
                                />
                            </div>
                            <div className="flex items-center gap-2">
                                <code className="border-terminal-border bg-terminal-black text-terminal-cyan flex-1 rounded border px-4 py-3 font-mono text-sm">
                                    {url.endpoint_url}
                                </code>
                                <button
                                    type="button"
                                    onClick={() => copyToClipboard(url.endpoint_url)}
                                    className={`rounded border px-4 py-3 font-mono text-sm transition-all ${
                                        copied
                                            ? 'border-terminal-green bg-terminal-green/20 text-terminal-green'
                                            : 'border-terminal-border text-terminal-text-muted hover:border-terminal-green/50 hover:text-terminal-green'
                                    }`}
                                >
                                    {copied ? '✓ copied' : 'copy'}
                                </button>
                            </div>
                        </div>
                    </TerminalCard>

                    <div className="grid gap-6 md:grid-cols-2">
                        {/* Rate Limit Indicator */}
                        <RateLimitIndicator rateLimit={url.rate_limit} className="h-full" />

                        {/* Details Card */}
                        <TerminalCard
                            title="metadata"
                            subtitle="endpoint information"
                            className="h-full"
                        >
                            <div className="space-y-6">
                                {url.description && (
                                    <div className="space-y-2">
                                        <div className="text-terminal-text-muted flex items-center gap-2 font-mono text-sm">
                                            <span className="text-terminal-purple">#</span>
                                            description
                                        </div>
                                        <div className="text-terminal-text ml-4 font-mono text-sm">
                                            {url.description}
                                        </div>
                                    </div>
                                )}

                                <div className="grid gap-6 sm:grid-cols-4">
                                    <div className="space-y-2">
                                        <div className="text-terminal-text-muted flex items-center gap-2 font-mono text-xs">
                                            <span className="text-terminal-cyan">→</span>
                                            total requests
                                        </div>
                                        <div className="text-terminal-cyan font-mono text-2xl font-bold">
                                            {url.requests_count ?? 0}
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <div className="text-terminal-text-muted flex items-center gap-2 font-mono text-xs">
                                            <span className="text-terminal-yellow">⏱</span>
                                            last request
                                        </div>
                                        <div className="text-terminal-text font-mono text-sm">
                                            {url.last_request_at
                                                ? new Date(url.last_request_at).toLocaleString()
                                                : 'Never'}
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <div className="text-terminal-text-muted flex items-center gap-2 font-mono text-xs">
                                            <span className="text-terminal-green">✦</span>
                                            created
                                        </div>
                                        <div className="text-terminal-text font-mono text-sm">
                                            {new Date(url.created_at).toLocaleString()}
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <div className="text-terminal-text-muted flex items-center gap-2 font-mono text-xs">
                                            <span className="text-terminal-yellow">~</span>
                                            data expires
                                        </div>
                                        <div className="text-terminal-yellow font-mono text-sm">
                                            {url.requests_expire_after_hours >= 24
                                                ? `after ${url.requests_expire_after_hours / 24} days`
                                                : `after ${url.requests_expire_after_hours}h`}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </TerminalCard>
                    </div>

                    {/* Requests Section */}
                    <TerminalCard
                        title="requests"
                        subtitle="captured webhooks"
                        headerContent={
                            url.requests && url.requests.length > 0 ? (
                                <Link
                                    href={route('urls.requests.index', { url: url.id })}
                                    className="text-terminal-cyan font-mono text-xs hover:underline"
                                >
                                    view all →
                                </Link>
                            ) : null
                        }
                    >
                        {url.requests && url.requests.length > 0 ? (
                            <div className="space-y-2">
                                {url.requests.map((request, index) => (
                                    <RequestListItem
                                        key={request.id}
                                        request={request}
                                        url={url}
                                        index={index + 1}
                                    />
                                ))}
                            </div>
                        ) : (
                            <div className="py-8 text-center">
                                <div className="border-terminal-border bg-terminal-bg mx-auto flex h-16 w-16 items-center justify-center rounded-lg border">
                                    <svg
                                        className="text-terminal-green h-8 w-8"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={1.5}
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                                        />
                                    </svg>
                                </div>
                                <h3 className="text-terminal-text mt-4 font-mono text-lg font-semibold">
                                    no requests captured
                                </h3>
                                <p className="text-terminal-text-muted mx-auto mt-2 max-w-sm font-mono text-sm">
                                    Send a webhook to this endpoint to start capturing requests.
                                </p>
                                <div className="mt-6 space-y-3">
                                    <div className="text-terminal-text-muted flex items-center justify-center gap-2 font-mono text-xs">
                                        <span className="text-terminal-green">$</span>
                                        <span>example curl command</span>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={copyCurlCommand}
                                        className={`inline-block cursor-pointer rounded border px-4 py-2 font-mono text-xs transition-all ${
                                            curlCopied
                                                ? 'border-terminal-green bg-terminal-green/20 text-terminal-green'
                                                : 'border-terminal-border bg-terminal-black text-terminal-cyan hover:border-terminal-green/50 hover:bg-terminal-black/50'
                                        }`}
                                    >
                                        {curlCopied ? (
                                            <span className="flex items-center gap-2">
                                                ✓ copied to clipboard
                                            </span>
                                        ) : (
                                            <span className="text-left whitespace-pre-wrap">
                                                {`curl -X POST ${url.endpoint_url} \\\n  -H "Content-Type: application/json" \\\n  -H "User-Agent: MyApp/1.0" \\\n  -d '{"event": "test", "data": {"message": "Hello, viewhook!"}}'`}
                                            </span>
                                        )}
                                    </button>
                                </div>
                            </div>
                        )}
                    </TerminalCard>

                    {/* Forwarding Configuration */}
                    <ForwardingConfigCard url={url} recentForwards={recentForwards} />

                    {/* Notifications Configuration */}
                    <NotificationsConfigCard url={url} />
                </div>
            </div>

            <DeleteUrlModal
                url={url}
                show={showDeleteModal}
                onClose={() => setShowDeleteModal(false)}
            />
        </AuthenticatedLayout>
    );
}
