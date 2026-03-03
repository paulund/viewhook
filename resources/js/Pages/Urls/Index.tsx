import CreateUrlModal from '@/Components/CreateUrlModal';
import EmptyState from '@/Components/EmptyState';
import UrlCard from '@/Components/UrlCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { UrlIndexProps } from '@/types';
import { Head } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ urls }: UrlIndexProps) {
    const [showCreateModal, setShowCreateModal] = useState(false);

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <span className="text-terminal-text font-mono text-xl font-semibold">
                            <span className="text-terminal-green">$</span> ls --urls
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
            <Head title="Webhook URLs" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {urls.length === 0 ? (
                        <div className="border-terminal-border bg-terminal-bg rounded-lg border p-12">
                            <EmptyState
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
                        </div>
                    ) : (
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {urls.map((url) => (
                                <UrlCard key={url.id} url={url} />
                            ))}
                        </div>
                    )}
                </div>
            </div>

            <CreateUrlModal show={showCreateModal} onClose={() => setShowCreateModal(false)} />
        </AuthenticatedLayout>
    );
}
