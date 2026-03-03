import TerminalCard from '@/Components/TerminalCard';
import type { Url, WebhookForward } from '@/types';
import { router, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

interface Props {
    url: Url;
    recentForwards: WebhookForward[];
}

export default function ForwardingConfigCard({ url, recentForwards }: Props) {
    const [isEditing, setIsEditing] = useState(false);

    const { data, setData, patch, processing, errors, reset } = useForm({
        forward_to_url: url.forward_to_url ?? '',
        forward_method: url.forward_method ?? 'POST',
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        patch(route('urls.update', { url: url.id }), {
            preserveScroll: true,
            onSuccess: () => setIsEditing(false),
        });
    };

    const handleDisable = () => {
        router.patch(
            route('urls.update', { url: url.id }),
            { forward_to_url: '' },
            { preserveScroll: true },
        );
    };

    return (
        <TerminalCard
            title="forwarding"
            subtitle={url.has_forwarding ? 'active' : 'not configured'}
            variant={url.has_forwarding ? 'success' : 'default'}
            headerContent={
                url.has_forwarding && !isEditing ? (
                    <div className="flex items-center gap-2">
                        <span className="bg-terminal-green inline-flex h-2 w-2 animate-pulse rounded-full"></span>
                        <span className="text-terminal-green font-mono text-xs">enabled</span>
                    </div>
                ) : null
            }
        >
            {isEditing ? (
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="space-y-2">
                        <label className="text-terminal-text-muted flex items-center gap-2 font-mono text-sm">
                            <span className="text-terminal-cyan">→</span>
                            target url
                        </label>
                        <input
                            type="url"
                            value={data.forward_to_url}
                            onChange={(e) => setData('forward_to_url', e.target.value)}
                            placeholder="https://your-api.com/webhook"
                            className="border-terminal-border bg-terminal-black text-terminal-text placeholder:text-terminal-text-subtle focus:border-terminal-cyan focus:ring-terminal-cyan/50 w-full rounded border px-4 py-2 font-mono text-sm focus:ring-1 focus:outline-none"
                        />
                        {errors.forward_to_url && (
                            <p className="text-terminal-red font-mono text-xs">
                                {errors.forward_to_url}
                            </p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <label className="text-terminal-text-muted flex items-center gap-2 font-mono text-sm">
                            <span className="text-terminal-purple">#</span>
                            http method
                        </label>
                        <select
                            value={data.forward_method}
                            onChange={(e) => setData('forward_method', e.target.value)}
                            className="border-terminal-border bg-terminal-black text-terminal-text focus:border-terminal-cyan focus:ring-terminal-cyan/50 w-full rounded border px-4 py-2 font-mono text-sm focus:ring-1 focus:outline-none"
                        >
                            <option value="POST">POST</option>
                            <option value="PUT">PUT</option>
                            <option value="PATCH">PATCH</option>
                            <option value="GET">GET</option>
                            <option value="DELETE">DELETE</option>
                        </select>
                    </div>

                    <div className="flex items-center gap-2">
                        <button
                            type="submit"
                            disabled={processing}
                            className="border-terminal-green/50 bg-terminal-green/10 text-terminal-green hover:bg-terminal-green/20 flex items-center gap-2 rounded border px-4 py-2 font-mono text-sm transition-all disabled:opacity-50"
                        >
                            {processing ? 'saving...' : 'save'}
                        </button>
                        <button
                            type="button"
                            onClick={() => {
                                reset();
                                setIsEditing(false);
                            }}
                            className="border-terminal-border text-terminal-text-muted hover:border-terminal-text-muted rounded border px-4 py-2 font-mono text-sm transition-all"
                        >
                            cancel
                        </button>
                    </div>
                </form>
            ) : url.has_forwarding ? (
                <div className="space-y-4">
                    <div className="space-y-3">
                        <div className="space-y-1">
                            <div className="text-terminal-text-muted flex items-center gap-2 font-mono text-xs">
                                <span className="text-terminal-cyan">→</span>
                                forwarding to
                            </div>
                            <code className="text-terminal-cyan block font-mono text-sm break-all">
                                {url.forward_to_url}
                            </code>
                        </div>
                        <div className="space-y-1">
                            <div className="text-terminal-text-muted flex items-center gap-2 font-mono text-xs">
                                <span className="text-terminal-purple">#</span>
                                method
                            </div>
                            <span className="text-terminal-text font-mono text-sm">
                                {url.forward_method}
                            </span>
                        </div>
                    </div>

                    {recentForwards.length > 0 && (
                        <div className="border-terminal-border space-y-2 border-t pt-4">
                            <div className="text-terminal-text-muted font-mono text-xs">
                                recent forwards
                            </div>
                            <div className="space-y-1">
                                {recentForwards.map((forward) => (
                                    <div
                                        key={forward.id}
                                        className="border-terminal-border flex items-center justify-between rounded border px-3 py-2"
                                    >
                                        <div className="flex items-center gap-2">
                                            <span
                                                className={`inline-flex h-2 w-2 rounded-full ${
                                                    forward.is_successful
                                                        ? 'bg-terminal-green'
                                                        : 'bg-terminal-red'
                                                }`}
                                            />
                                            <span className="text-terminal-text font-mono text-xs">
                                                {forward.status_code ?? 'ERR'}
                                            </span>
                                            {forward.response_time_ms !== null && (
                                                <span className="text-terminal-text-muted font-mono text-xs">
                                                    {forward.response_time_ms}ms
                                                </span>
                                            )}
                                        </div>
                                        <span className="text-terminal-text-subtle font-mono text-xs">
                                            {new Date(forward.created_at).toLocaleTimeString()}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    <div className="flex items-center gap-2">
                        <button
                            type="button"
                            onClick={() => setIsEditing(true)}
                            className="border-terminal-border text-terminal-text-muted hover:border-terminal-cyan hover:text-terminal-cyan rounded border px-4 py-2 font-mono text-sm transition-all"
                        >
                            edit
                        </button>
                        <button
                            type="button"
                            onClick={handleDisable}
                            className="border-terminal-red/50 text-terminal-red hover:bg-terminal-red/10 rounded border px-4 py-2 font-mono text-sm transition-all"
                        >
                            disable
                        </button>
                    </div>
                </div>
            ) : (
                <div className="py-6 text-center">
                    <div className="border-terminal-border bg-terminal-bg mx-auto flex h-12 w-12 items-center justify-center rounded-lg border">
                        <svg
                            className="text-terminal-text-muted h-6 w-6"
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
                    </div>
                    <p className="text-terminal-text-muted mt-3 font-mono text-sm">
                        Forward captured webhooks to another endpoint
                    </p>
                    <button
                        type="button"
                        onClick={() => setIsEditing(true)}
                        className="border-terminal-cyan/50 bg-terminal-cyan/10 text-terminal-cyan hover:bg-terminal-cyan/20 mt-4 inline-flex items-center gap-2 rounded border px-4 py-2 font-mono text-sm transition-all"
                    >
                        configure forwarding
                    </button>
                </div>
            )}
        </TerminalCard>
    );
}
