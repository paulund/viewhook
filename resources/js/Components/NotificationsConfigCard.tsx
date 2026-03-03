import TerminalCard from '@/Components/TerminalCard';
import type { Url } from '@/types';
import { useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

interface Props {
    url: Url;
}

export default function NotificationsConfigCard({ url }: Props) {
    const [isEditing, setIsEditing] = useState(false);

    const { data, setData, patch, processing, errors, reset } = useForm({
        notify_email: url.notify_email,
        notify_slack: url.notify_slack,
        slack_webhook_url: url.slack_webhook_url ?? '',
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        patch(route('urls.update', { url: url.id }), {
            preserveScroll: true,
            onSuccess: () => setIsEditing(false),
        });
    };

    const hasAnyNotification = url.has_email_notification || url.has_slack_notification;

    return (
        <TerminalCard
            title="notifications"
            subtitle={hasAnyNotification ? 'active' : 'not configured'}
            variant={hasAnyNotification ? 'info' : 'default'}
            headerContent={
                hasAnyNotification && !isEditing ? (
                    <div className="flex items-center gap-2">
                        {url.has_email_notification && (
                            <span className="text-terminal-cyan font-mono text-xs">📧 email</span>
                        )}
                        {url.has_slack_notification && (
                            <span className="text-terminal-purple font-mono text-xs">💬 slack</span>
                        )}
                    </div>
                ) : null
            }
        >
            {isEditing ? (
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="space-y-3">
                        <label className="flex items-center gap-3">
                            <input
                                type="checkbox"
                                checked={data.notify_email}
                                onChange={(e) => setData('notify_email', e.target.checked)}
                                className="border-terminal-border bg-terminal-black text-terminal-cyan focus:ring-terminal-cyan/50 h-4 w-4 rounded"
                            />
                            <span className="text-terminal-text font-mono text-sm">
                                📧 Email notifications
                            </span>
                        </label>
                        <p className="text-terminal-text-muted ml-7 font-mono text-xs">
                            Receive email notifications when requests are captured
                        </p>
                    </div>

                    <div className="border-terminal-border space-y-3 border-t pt-4">
                        <label className="flex items-center gap-3">
                            <input
                                type="checkbox"
                                checked={data.notify_slack}
                                onChange={(e) => setData('notify_slack', e.target.checked)}
                                className="border-terminal-border bg-terminal-black text-terminal-purple focus:ring-terminal-purple/50 h-4 w-4 rounded"
                            />
                            <span className="text-terminal-text font-mono text-sm">
                                💬 Slack notifications
                            </span>
                        </label>

                        {data.notify_slack && (
                            <div className="ml-7 space-y-2">
                                <label className="text-terminal-text-muted flex items-center gap-2 font-mono text-xs">
                                    <span className="text-terminal-purple">#</span>
                                    slack webhook url
                                </label>
                                <input
                                    type="url"
                                    value={data.slack_webhook_url}
                                    onChange={(e) => setData('slack_webhook_url', e.target.value)}
                                    placeholder="https://hooks.slack.com/services/..."
                                    className="border-terminal-border bg-terminal-black text-terminal-text placeholder:text-terminal-text-subtle focus:border-terminal-purple focus:ring-terminal-purple/50 w-full rounded border px-4 py-2 font-mono text-sm focus:ring-1 focus:outline-none"
                                />
                                {errors.slack_webhook_url && (
                                    <p className="text-terminal-red font-mono text-xs">
                                        {errors.slack_webhook_url}
                                    </p>
                                )}
                            </div>
                        )}
                    </div>

                    <div className="flex items-center gap-2 pt-2">
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
            ) : hasAnyNotification ? (
                <div className="space-y-4">
                    <div className="space-y-3">
                        {url.has_email_notification && (
                            <div className="flex items-center gap-3">
                                <span className="bg-terminal-cyan/20 text-terminal-cyan rounded px-2 py-1 font-mono text-xs">
                                    📧 Email
                                </span>
                                <span className="text-terminal-text-muted font-mono text-xs">
                                    Notifications sent to your account email
                                </span>
                            </div>
                        )}
                        {url.has_slack_notification && (
                            <div className="flex items-center gap-3">
                                <span className="bg-terminal-purple/20 text-terminal-purple rounded px-2 py-1 font-mono text-xs">
                                    💬 Slack
                                </span>
                                <span className="text-terminal-text-muted truncate font-mono text-xs">
                                    {url.slack_webhook_url}
                                </span>
                            </div>
                        )}
                    </div>

                    <button
                        type="button"
                        onClick={() => setIsEditing(true)}
                        className="border-terminal-border text-terminal-text-muted hover:border-terminal-cyan hover:text-terminal-cyan rounded border px-4 py-2 font-mono text-sm transition-all"
                    >
                        edit
                    </button>
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
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                            />
                        </svg>
                    </div>
                    <p className="text-terminal-text-muted mt-3 font-mono text-sm">
                        Get notified when requests are captured
                    </p>
                    <button
                        type="button"
                        onClick={() => setIsEditing(true)}
                        className="border-terminal-cyan/50 bg-terminal-cyan/10 text-terminal-cyan hover:bg-terminal-cyan/20 mt-4 inline-flex items-center gap-2 rounded border px-4 py-2 font-mono text-sm transition-all"
                    >
                        configure notifications
                    </button>
                </div>
            )}
        </TerminalCard>
    );
}
