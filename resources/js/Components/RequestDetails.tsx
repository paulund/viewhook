import CodeBlock from '@/Components/CodeBlock';
import TerminalCard from '@/Components/TerminalCard';
import { formatBytes, methodColors } from '@/lib/request-utils';
import { cn } from '@/lib/utils';
import type { Request } from '@/types';
import { format, formatDistanceToNow } from 'date-fns';

interface RequestDetailsProps {
    request: Request;
}

export default function RequestDetails({ request }: RequestDetailsProps) {
    const timeAgo = formatDistanceToNow(new Date(request.created_at), {
        addSuffix: true,
    });
    const exactTime = format(new Date(request.created_at), 'PPpp');

    return (
        <div className="space-y-6">
            {/* Overview Section */}
            <TerminalCard title="metadata" subtitle="request overview" variant="info">
                <dl className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt className="text-terminal-text-subtle font-mono text-xs font-medium">
                            method
                        </dt>
                        <dd className="mt-1">
                            <span
                                className={cn(
                                    'inline-flex items-center rounded px-2 py-1 font-mono text-xs font-medium',
                                    methodColors[request.method],
                                )}
                            >
                                {request.method}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt className="text-terminal-text-subtle font-mono text-xs font-medium">
                            path
                        </dt>
                        <dd className="text-terminal-text mt-1 font-mono text-sm">
                            {request.path}
                        </dd>
                    </div>
                    <div>
                        <dt className="text-terminal-text-subtle font-mono text-xs font-medium">
                            received
                        </dt>
                        <dd className="text-terminal-text mt-1 font-mono text-sm" title={exactTime}>
                            {timeAgo}
                        </dd>
                    </div>
                    <div>
                        <dt className="text-terminal-text-subtle font-mono text-xs font-medium">
                            content_type
                        </dt>
                        <dd className="text-terminal-text mt-1 font-mono text-sm">
                            {request.content_type ?? 'none'}
                        </dd>
                    </div>
                    <div>
                        <dt className="text-terminal-text-subtle font-mono text-xs font-medium">
                            size
                        </dt>
                        <dd className="text-terminal-cyan mt-1 font-mono text-sm">
                            {formatBytes(request.content_length)}
                        </dd>
                    </div>
                    <div>
                        <dt className="text-terminal-text-subtle font-mono text-xs font-medium">
                            ip_address
                        </dt>
                        <dd className="text-terminal-purple mt-1 font-mono text-sm">
                            {request.ip_address ?? 'unknown'}
                        </dd>
                    </div>
                </dl>
            </TerminalCard>

            {/* Headers Section */}
            <TerminalCard
                title="headers"
                subtitle={`${Object.keys(request.headers).length} entries`}
            >
                <div className="overflow-x-auto">
                    <table className="min-w-full">
                        <thead>
                            <tr className="border-terminal-border border-b">
                                <th className="text-terminal-text-subtle px-3 py-2 text-left font-mono text-xs font-medium">
                                    NAME
                                </th>
                                <th className="text-terminal-text-subtle px-3 py-2 text-left font-mono text-xs font-medium">
                                    VALUE
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-terminal-border divide-y">
                            {Object.entries(request.headers).map(([name, value]) => (
                                <tr
                                    key={name}
                                    className="hover:bg-terminal-hover transition-colors"
                                >
                                    <td className="text-terminal-green px-3 py-2 font-mono text-sm font-medium whitespace-nowrap">
                                        {name}
                                    </td>
                                    <td className="text-terminal-text-muted px-3 py-2 font-mono text-sm break-all">
                                        {value}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </TerminalCard>

            {/* Query Parameters Section */}
            {request.query_params && Object.keys(request.query_params).length > 0 && (
                <TerminalCard
                    title="query_params"
                    subtitle={`${Object.keys(request.query_params).length} parameters`}
                    variant="warning"
                >
                    <div className="overflow-x-auto">
                        <table className="min-w-full">
                            <thead>
                                <tr className="border-terminal-border border-b">
                                    <th className="text-terminal-text-subtle px-3 py-2 text-left font-mono text-xs font-medium">
                                        NAME
                                    </th>
                                    <th className="text-terminal-text-subtle px-3 py-2 text-left font-mono text-xs font-medium">
                                        VALUE
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-terminal-border divide-y">
                                {Object.entries(request.query_params).map(([name, value]) => (
                                    <tr
                                        key={name}
                                        className="hover:bg-terminal-hover transition-colors"
                                    >
                                        <td className="text-terminal-yellow px-3 py-2 font-mono text-sm font-medium whitespace-nowrap">
                                            {name}
                                        </td>
                                        <td className="text-terminal-text-muted px-3 py-2 font-mono text-sm break-all">
                                            {value}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </TerminalCard>
            )}

            {/* Body Section */}
            {request.body && (
                <TerminalCard
                    title="payload"
                    subtitle={request.is_json ? 'json' : request.is_xml ? 'xml' : 'raw'}
                    variant="success"
                >
                    <CodeBlock
                        content={request.body}
                        language={request.is_json ? 'json' : request.is_xml ? 'xml' : 'text'}
                    />
                </TerminalCard>
            )}

            {/* User Agent Section */}
            {request.user_agent && (
                <TerminalCard title="user_agent" subtitle="client information">
                    <p className="text-terminal-text-muted font-mono text-sm break-all">
                        {request.user_agent}
                    </p>
                </TerminalCard>
            )}
        </div>
    );
}
