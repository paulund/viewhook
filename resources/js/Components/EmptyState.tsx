import { cn } from '@/lib/utils';
import type { ReactNode } from 'react';

interface EmptyStateProps {
    icon: ReactNode;
    title: string;
    description: string;
    action?: ReactNode;
    className?: string;
}

export default function EmptyState({
    icon,
    title,
    description,
    action,
    className,
}: EmptyStateProps) {
    return (
        <div className={cn('text-center', className)}>
            <div className="border-terminal-border bg-terminal-bg mx-auto flex h-16 w-16 items-center justify-center rounded-lg border">
                {icon}
            </div>
            <h3 className="text-terminal-text mt-4 font-mono text-lg font-semibold">{title}</h3>
            <p className="text-terminal-text-muted mx-auto mt-2 max-w-sm font-mono text-sm">
                {description}
            </p>
            {action && <div className="mt-6">{action}</div>}
        </div>
    );
}
