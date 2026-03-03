import EmptyState from '@/Components/EmptyState';
import RequestListItem from '@/Components/RequestListItem';
import TerminalCard from '@/Components/TerminalCard';
import { useUrlChannel } from '@/hooks/use-url-channel';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { PaginatedData, Request, Url } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { useCallback, useState } from 'react';

interface Props {
    url: Url;
    requests: PaginatedData<Request>;
}

export default function RequestsIndex({ url, requests }: Props) {
    const [exporting, setExporting] = useState(false);

    const handleRequestCaptured = useCallback(() => {
        router.reload({ only: ['requests'] });
    }, []);

    const handleExport = useCallback(
        (format: 'csv' | 'json') => {
            setExporting(true);
            // Trigger file download
            window.location.href = route('urls.export', { url: url.id, format });
            // Reset exporting state after a delay
            setTimeout(() => setExporting(false), 2000);
        },
        [url.id],
    );

    useUrlChannel({
        urlId: url.id,
        onRequestCaptured: handleRequestCaptured,
    });

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col gap-3">
                    <Link
                        href={route('urls.show', { url: url.id })}
                        className="text-terminal-text-muted hover:text-terminal-green inline-flex w-fit items-center gap-2 font-mono text-sm transition-colors"
                    >
                        <span className="text-terminal-green">←</span>
                        cd ../
                    </Link>
                    <div className="flex items-center gap-3">
                        <span className="text-terminal-text font-mono text-xl font-semibold">
                            <span className="text-terminal-cyan">$</span> tail -f requests.log
                        </span>
                        <span className="bg-terminal-green inline-flex h-2 w-2 animate-pulse rounded-full"></span>
                    </div>
                    <div className="text-terminal-text-muted flex items-center gap-2 font-mono text-sm">
                        <span className="text-terminal-green">#</span>
                        {url.name} • {requests.meta.total} request
                        {requests.meta.total !== 1 ? 's' : ''} captured
                    </div>
                </div>
            }
        >
            <Head title={`Requests - ${url.name}`} />

            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                    {requests.data.length === 0 ? (
                        <TerminalCard title="incoming" subtitle="waiting for requests..." glow>
                            <EmptyState
                                className="py-12"
                                icon={
                                    <svg
                                        className="text-terminal-cyan h-8 w-8"
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
                                }
                                title="LOG_EMPTY"
                                description="No requests captured yet. Send a webhook to start monitoring."
                                action={
                                    <div className="border-terminal-border bg-terminal-black rounded-lg border p-4">
                                        <div className="text-terminal-text-subtle mb-2 font-mono text-xs">
                                            $ example request
                                        </div>
                                        <code className="text-terminal-cyan block font-mono text-sm">
                                            curl -X POST {url.endpoint_url}
                                        </code>
                                    </div>
                                }
                            />
                        </TerminalCard>
                    ) : (
                        <TerminalCard
                            title="requests.log"
                            subtitle={`${requests.meta.total} entries`}
                            variant="info"
                            headerContent={
                                <div className="flex items-center gap-3">
                                    <div className="flex items-center gap-2">
                                        <span className="bg-terminal-green inline-flex h-2 w-2 animate-pulse rounded-full"></span>
                                        <span className="text-terminal-text-muted font-mono text-xs">
                                            live
                                        </span>
                                    </div>
                                    <div className="flex items-center gap-1">
                                        <button
                                            onClick={() => handleExport('csv')}
                                            disabled={exporting}
                                            className="border-terminal-border hover:border-terminal-cyan hover:text-terminal-cyan text-terminal-text-muted rounded border px-2 py-1 font-mono text-xs transition-colors disabled:opacity-50"
                                            title="Export as CSV"
                                        >
                                            {exporting ? '...' : 'CSV'}
                                        </button>
                                        <button
                                            onClick={() => handleExport('json')}
                                            disabled={exporting}
                                            className="border-terminal-border hover:border-terminal-cyan hover:text-terminal-cyan text-terminal-text-muted rounded border px-2 py-1 font-mono text-xs transition-colors disabled:opacity-50"
                                            title="Export as JSON"
                                        >
                                            {exporting ? '...' : 'JSON'}
                                        </button>
                                    </div>
                                </div>
                            }
                        >
                            <div className="space-y-2">
                                {requests.data.map((request, index) => (
                                    <RequestListItem
                                        key={request.id}
                                        request={request}
                                        url={url}
                                        index={
                                            requests.meta.total -
                                            ((requests.meta.from ?? 1) + index - 1)
                                        }
                                    />
                                ))}
                            </div>
                        </TerminalCard>
                    )}

                    {requests.meta.last_page > 1 && (
                        <div className="flex items-center justify-center gap-3">
                            {requests.links.prev && (
                                <Link
                                    href={requests.links.prev}
                                    className="border-terminal-border bg-terminal-surface text-terminal-text hover:border-terminal-green/50 hover:text-terminal-green flex items-center gap-2 rounded-md border px-4 py-2 font-mono text-sm font-medium transition-all"
                                >
                                    <span>←</span> prev
                                </Link>
                            )}
                            <span className="border-terminal-border bg-terminal-bg text-terminal-text-muted flex items-center gap-2 rounded-md border px-4 py-2 font-mono text-sm">
                                <span className="text-terminal-green">
                                    {requests.meta.current_page}
                                </span>
                                <span>/</span>
                                <span>{requests.meta.last_page}</span>
                            </span>
                            {requests.links.next && (
                                <Link
                                    href={requests.links.next}
                                    className="border-terminal-border bg-terminal-surface text-terminal-text hover:border-terminal-green/50 hover:text-terminal-green flex items-center gap-2 rounded-md border px-4 py-2 font-mono text-sm font-medium transition-all"
                                >
                                    next <span>→</span>
                                </Link>
                            )}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
