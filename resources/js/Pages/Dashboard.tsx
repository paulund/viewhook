import CreateUrlModal from '@/Components/CreateUrlModal';
import EmptyState from '@/Components/EmptyState';
import TerminalCard, { TerminalLine } from '@/Components/TerminalCard';
import { useCopyToClipboard } from '@/hooks/useCopyToClipboard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps, Url } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

interface DashboardProps extends PageProps {
    urls: Url[];
    stats: {
        total_urls: number;
        total_requests: number;
    };
}

export default function Dashboard() {
    const { auth, urls, stats } = usePage<DashboardProps>().props;
    const user = auth.user;
    const [showCreateModal, setShowCreateModal] = useState(false);
    const { copiedText, copy } = useCopyToClipboard();

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <span className="text-terminal-text font-mono text-xl font-semibold">
                            <span className="text-terminal-green">$</span> dashboard
                        </span>
                        <span className="cursor-blink text-terminal-green"></span>
                    </div>
                    <button
                        onClick={() => setShowCreateModal(true)}
                        className="group border-terminal-green bg-terminal-green/10 text-terminal-green hover:bg-terminal-green/20 flex items-center gap-2 rounded-md border px-4 py-2 font-mono text-sm font-medium transition-all"
                    >
                        <span className="transition-transform group-hover:scale-110">+</span>
                        new --webhook
                    </button>
                </div>
            }
        >
            <Head title="Dashboard" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                    {/* Welcome Terminal */}
                    <TerminalCard title="session" subtitle="~/.viewhook/welcome" glow>
                        <div className="space-y-2">
                            <TerminalLine command="whoami" output={user.name} />
                            <TerminalLine
                                command="echo $MISSION"
                                output="Capture, inspect, and debug webhook requests in real-time."
                            />
                            <div className="mt-4 flex items-center gap-2 font-mono text-sm">
                                <span className="text-terminal-green">$</span>
                                <span className="text-terminal-text">ready</span>
                                <span className="cursor-blink text-terminal-green"></span>
                            </div>
                        </div>
                    </TerminalCard>

                    {/* Stats Grid */}
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <TerminalCard title="urls.count" variant="info">
                            <div className="flex items-baseline justify-between">
                                <span className="text-terminal-cyan font-mono text-4xl font-bold">
                                    {stats.total_urls}
                                </span>
                                <span className="text-terminal-text-muted font-mono text-xs">
                                    endpoints
                                </span>
                            </div>
                            <div className="bg-terminal-border mt-2 h-1 rounded-full">
                                <div
                                    className="bg-terminal-cyan h-full rounded-full transition-all"
                                    style={{
                                        width: `${Math.min((stats.total_urls / 10) * 100, 100)}%`,
                                    }}
                                />
                            </div>
                        </TerminalCard>

                        <TerminalCard title="requests.total" variant="success">
                            <div className="flex items-baseline justify-between">
                                <span className="text-terminal-green font-mono text-4xl font-bold">
                                    {stats.total_requests}
                                </span>
                                <span className="text-terminal-text-muted font-mono text-xs">
                                    captured
                                </span>
                            </div>
                            <div className="text-terminal-text-muted mt-2 flex items-center gap-1 font-mono text-xs">
                                <span className="bg-terminal-green inline-flex h-2 w-2 animate-pulse rounded-full"></span>
                                listening for webhooks...
                            </div>
                        </TerminalCard>

                        <TerminalCard title="status" variant="success">
                            <div className="flex items-center gap-3">
                                <div className="bg-terminal-green/20 text-terminal-green flex h-10 w-10 items-center justify-center rounded-lg">
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
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <div className="text-terminal-green font-mono text-lg font-semibold">
                                        OPERATIONAL
                                    </div>
                                    <div className="text-terminal-text-muted font-mono text-xs">
                                        all systems nominal
                                    </div>
                                </div>
                            </div>
                        </TerminalCard>
                    </div>

                    {/* Quick Commands */}
                    <TerminalCard title="commands" subtitle="quick actions">
                        <div className="flex flex-wrap gap-3">
                            <button
                                onClick={() => setShowCreateModal(true)}
                                className="border-terminal-green/50 bg-terminal-green/10 text-terminal-green hover:bg-terminal-green/20 flex items-center gap-2 rounded border px-4 py-2 font-mono text-sm transition-all"
                            >
                                <span>$</span> create --url
                            </button>
                            <Link
                                href={route('urls.index')}
                                className="border-terminal-cyan/50 bg-terminal-cyan/10 text-terminal-cyan hover:bg-terminal-cyan/20 flex items-center gap-2 rounded border px-4 py-2 font-mono text-sm transition-all"
                            >
                                <span>$</span> ls --urls
                            </Link>
                        </div>
                    </TerminalCard>

                    {/* Recent URLs */}
                    {urls.length > 0 && (
                        <TerminalCard
                            title="recent"
                            subtitle="webhook endpoints"
                            headerContent={
                                <Link
                                    href={route('urls.index')}
                                    className="text-terminal-cyan font-mono text-xs hover:underline"
                                >
                                    view all →
                                </Link>
                            }
                        >
                            <div className="space-y-3">
                                {urls.map((url) => (
                                    <div
                                        key={url.id}
                                        className="group border-terminal-border bg-terminal-bg hover:border-terminal-green/30 hover:terminal-glow rounded-lg border p-4 transition-all"
                                    >
                                        <div className="flex items-start justify-between gap-4">
                                            <div className="min-w-0 flex-1">
                                                <div className="flex items-center gap-2">
                                                    <Link
                                                        href={route('urls.show', url.id)}
                                                        className="text-terminal-text hover:text-terminal-green font-mono text-sm font-medium"
                                                    >
                                                        {url.name}
                                                    </Link>
                                                </div>
                                                {url.description && (
                                                    <p className="text-terminal-text-muted mt-1 font-mono text-xs">
                                                        # {url.description}
                                                    </p>
                                                )}
                                                <div className="mt-3 flex items-center gap-2">
                                                    <code className="border-terminal-border bg-terminal-black text-terminal-cyan flex-1 rounded border px-3 py-2 font-mono text-xs">
                                                        {url.endpoint_url}
                                                    </code>
                                                    <button
                                                        onClick={() => copy(url.endpoint_url)}
                                                        className={`rounded border px-3 py-2 font-mono text-xs transition-all ${
                                                            copiedText === url.endpoint_url
                                                                ? 'border-terminal-green bg-terminal-green/20 text-terminal-green'
                                                                : 'border-terminal-border text-terminal-text-muted hover:border-terminal-green/50 hover:text-terminal-green'
                                                        }`}
                                                    >
                                                        {copiedText === url.endpoint_url
                                                            ? '✓ copied'
                                                            : 'copy'}
                                                    </button>
                                                </div>
                                                <div className="text-terminal-text-subtle mt-3 flex items-center gap-4 font-mono text-xs">
                                                    <span className="flex items-center gap-1">
                                                        <span className="text-terminal-purple">
                                                            →
                                                        </span>
                                                        {url.requests_count ?? 0} requests
                                                    </span>
                                                    {url.last_request_at && (
                                                        <span className="flex items-center gap-1">
                                                            <span className="text-terminal-yellow">
                                                                ⏱
                                                            </span>
                                                            {new Date(
                                                                url.last_request_at,
                                                            ).toLocaleString()}
                                                        </span>
                                                    )}
                                                </div>
                                            </div>
                                            <Link
                                                href={route('urls.show', url.id)}
                                                className="border-terminal-border text-terminal-text-muted hover:border-terminal-green/50 hover:text-terminal-green rounded border px-3 py-1.5 font-mono text-xs transition-all"
                                            >
                                                inspect →
                                            </Link>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </TerminalCard>
                    )}

                    {/* Empty State */}
                    {urls.length === 0 && (
                        <TerminalCard title="init" glow>
                            <EmptyState
                                className="py-8"
                                icon={
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
                                            d="M13 10V3L4 14h7v7l9-11h-7z"
                                        />
                                    </svg>
                                }
                                title="no endpoints found"
                                description="Create your first webhook URL to start capturing and inspecting incoming requests."
                                action={
                                    <button
                                        onClick={() => setShowCreateModal(true)}
                                        className="border-terminal-green bg-terminal-green/10 text-terminal-green hover:bg-terminal-green/20 inline-flex items-center gap-2 rounded border px-6 py-3 font-mono text-sm font-medium transition-all"
                                    >
                                        <span>$</span> init --first-webhook
                                    </button>
                                }
                            />
                        </TerminalCard>
                    )}
                </div>
            </div>

            <CreateUrlModal show={showCreateModal} onClose={() => setShowCreateModal(false)} />
        </AuthenticatedLayout>
    );
}
