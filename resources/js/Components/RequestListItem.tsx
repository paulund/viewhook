import { formatBytes, methodColors } from '@/lib/request-utils';
import { cn } from '@/lib/utils';
import type { Request, Url } from '@/types';
import { Link } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';

interface RequestListItemProps {
    request: Request;
    url: Url;
    index?: number;
}

export default function RequestListItem({ request, url, index }: RequestListItemProps) {
    const timeAgo = formatDistanceToNow(new Date(request.created_at), {
        addSuffix: true,
    });

    return (
        <Link
            href={route('urls.requests.show', { url: url.id, request: request.id })}
            className="group border-terminal-border bg-terminal-bg hover:border-terminal-green/30 hover:terminal-glow block rounded-lg border p-4 transition-all"
        >
            <div className="flex items-start justify-between gap-4">
                <div className="flex min-w-0 flex-1 items-start gap-3">
                    {/* Log index */}
                    {index !== undefined && (
                        <span className="text-terminal-text-subtle font-mono text-xs">
                            [{index.toString().padStart(4, '0')}]
                        </span>
                    )}

                    {/* Method badge */}
                    <span
                        className={cn(
                            'inline-flex shrink-0 items-center rounded px-2 py-1 font-mono text-xs font-medium',
                            methodColors[request.method],
                        )}
                    >
                        {request.method}
                    </span>

                    {/* Path */}
                    <span className="text-terminal-text group-hover:text-terminal-green min-w-0 flex-1 font-mono text-sm">
                        {request.path || '/'}
                    </span>
                </div>

                {/* Metadata */}
                <div className="text-terminal-text-muted flex shrink-0 items-center gap-4 font-mono text-xs">
                    {request.content_type && (
                        <span className="hidden md:inline">
                            {request.content_type.split(';')[0]}
                        </span>
                    )}
                    <span className="text-terminal-cyan">
                        {formatBytes(request.content_length)}
                    </span>
                    <span className="text-terminal-text-subtle hidden sm:inline">{timeAgo}</span>
                </div>
            </div>

            {/* IP Address */}
            {request.ip_address && (
                <div className="text-terminal-text-subtle mt-2 ml-[52px] flex items-center gap-2 font-mono text-xs">
                    <span className="text-terminal-purple">from:</span>
                    <span>{request.ip_address}</span>
                    <span className="text-terminal-text-subtle">•</span>
                    <span className="sm:hidden">{timeAgo}</span>
                </div>
            )}
        </Link>
    );
}
