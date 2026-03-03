import DangerButton from '@/Components/DangerButton';
import Modal from '@/Components/Modal';
import RequestDetails from '@/Components/RequestDetails';
import SecondaryButton from '@/Components/SecondaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { Request, Url } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

interface Props {
    url: Url;
    request: Request;
}

export default function RequestShow({ url, request }: Props) {
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [isDeleting, setIsDeleting] = useState(false);

    const handleDelete = () => {
        setIsDeleting(true);
        router.delete(route('urls.requests.destroy', { url: url.id, request: request.id }), {
            onFinish: () => {
                setIsDeleting(false);
                setShowDeleteModal(false);
            },
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col gap-3">
                    <Link
                        href={route('urls.requests.index', { url: url.id })}
                        className="text-terminal-text-muted hover:text-terminal-green inline-flex w-fit items-center gap-2 font-mono text-sm transition-colors"
                    >
                        <span className="text-terminal-green">←</span>
                        cd ../
                    </Link>
                    <div className="flex items-center justify-between">
                        <div className="flex flex-col gap-2">
                            <div className="flex items-center gap-3">
                                <span className="text-terminal-text font-mono text-xl font-semibold">
                                    <span className="text-terminal-cyan">$</span> cat request.log
                                </span>
                            </div>
                            <div className="text-terminal-text-muted flex items-center gap-2 font-mono text-sm">
                                <span className="text-terminal-green">#</span>
                                {url.name}
                            </div>
                        </div>
                        <button
                            onClick={() => setShowDeleteModal(true)}
                            className="border-terminal-red/50 bg-terminal-red/10 text-terminal-red hover:bg-terminal-red/20 inline-flex items-center gap-2 rounded-md border px-4 py-2 font-mono text-sm font-medium transition-all"
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
                            rm -rf
                        </button>
                    </div>
                </div>
            }
        >
            <Head title={`${request.method} ${request.path} - ${url.name}`} />

            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                    <RequestDetails request={request} />
                </div>
            </div>

            <Modal show={showDeleteModal} onClose={() => setShowDeleteModal(false)}>
                <div className="border-terminal-border bg-terminal-surface rounded-lg border p-6">
                    <h2 className="text-terminal-text font-mono text-lg font-medium">
                        <span className="text-terminal-red">ERROR:</span> Confirm deletion
                    </h2>
                    <p className="text-terminal-text-muted mt-2 font-mono text-sm">
                        This action cannot be undone. The captured request data will be permanently
                        deleted.
                    </p>
                    <div className="mt-6 flex justify-end gap-3">
                        <SecondaryButton onClick={() => setShowDeleteModal(false)}>
                            Cancel
                        </SecondaryButton>
                        <DangerButton onClick={handleDelete} disabled={isDeleting}>
                            {isDeleting ? 'Deleting...' : 'Delete'}
                        </DangerButton>
                    </div>
                </div>
            </Modal>
        </AuthenticatedLayout>
    );
}
