import { cn } from '@/lib/utils';
import { PropsWithChildren, ReactNode } from 'react';

interface TerminalCardProps {
    title?: string;
    subtitle?: string;
    headerContent?: ReactNode;
    className?: string;
    bodyClassName?: string;
    variant?: 'default' | 'success' | 'warning' | 'danger' | 'info';
    glow?: boolean;
}

const variantStyles = {
    default: 'border-terminal-border',
    success: 'border-terminal-green/30',
    warning: 'border-terminal-yellow/30',
    danger: 'border-terminal-red/30',
    info: 'border-terminal-cyan/30',
};

const variantGlowStyles = {
    default: 'terminal-glow',
    success: 'shadow-[0_0_20px_rgba(57,211,83,0.1)]',
    warning: 'shadow-[0_0_20px_rgba(210,153,34,0.1)]',
    danger: 'shadow-[0_0_20px_rgba(248,81,73,0.1)]',
    info: 'shadow-[0_0_20px_rgba(88,166,255,0.1)]',
};

const variantDotStyles = {
    default: 'bg-terminal-green',
    success: 'bg-terminal-green',
    warning: 'bg-terminal-yellow',
    danger: 'bg-terminal-red',
    info: 'bg-terminal-cyan',
};

export default function TerminalCard({
    title,
    subtitle,
    headerContent,
    className,
    bodyClassName,
    variant = 'default',
    glow = false,
    children,
}: PropsWithChildren<TerminalCardProps>) {
    return (
        <div
            className={cn(
                'bg-terminal-surface overflow-hidden rounded-lg border',
                variantStyles[variant],
                glow && variantGlowStyles[variant],
                className,
            )}
        >
            {/* Terminal window header */}
            <div className="border-terminal-border bg-terminal-bg flex items-center justify-between border-b px-4 py-2.5">
                <div className="flex items-center gap-3">
                    {/* Window controls */}
                    <div className="flex items-center gap-1.5">
                        <span className="bg-terminal-red/80 hover:bg-terminal-red h-3 w-3 rounded-full transition-colors" />
                        <span className="bg-terminal-yellow/80 hover:bg-terminal-yellow h-3 w-3 rounded-full transition-colors" />
                        <span
                            className={cn(
                                'h-3 w-3 rounded-full transition-colors',
                                variantDotStyles[variant],
                            )}
                        />
                    </div>

                    {/* Title */}
                    {title && (
                        <div className="flex flex-col">
                            <span className="text-terminal-text font-mono text-sm font-medium">
                                {title}
                            </span>
                            {subtitle && (
                                <span className="text-terminal-text-muted font-mono text-xs">
                                    {subtitle}
                                </span>
                            )}
                        </div>
                    )}
                </div>

                {headerContent && <div className="flex items-center gap-2">{headerContent}</div>}
            </div>

            {/* Card body */}
            <div className={cn('p-4', bodyClassName)}>{children}</div>
        </div>
    );
}

// Sub-component for terminal-style stat display
interface TerminalStatProps {
    label: string;
    value: string | number;
    icon?: ReactNode;
    trend?: 'up' | 'down' | 'neutral';
    className?: string;
}

export function TerminalStat({ label, value, icon, trend, className }: TerminalStatProps) {
    return (
        <div className={cn('flex items-center justify-between', className)}>
            <div className="flex items-center gap-2">
                {icon && <span className="text-terminal-text-muted">{icon}</span>}
                <span className="text-terminal-text-muted font-mono text-sm">{label}</span>
            </div>
            <div className="flex items-center gap-2">
                <span className="text-terminal-text font-mono text-lg font-semibold">{value}</span>
                {trend && (
                    <span
                        className={cn(
                            'text-xs',
                            trend === 'up' && 'text-terminal-green',
                            trend === 'down' && 'text-terminal-red',
                            trend === 'neutral' && 'text-terminal-text-muted',
                        )}
                    >
                        {trend === 'up' && '↑'}
                        {trend === 'down' && '↓'}
                        {trend === 'neutral' && '→'}
                    </span>
                )}
            </div>
        </div>
    );
}

// Sub-component for command-style output lines
interface TerminalLineProps {
    prompt?: string;
    command?: string;
    output?: string;
    className?: string;
}

export function TerminalLine({ prompt = '$', command, output, className }: TerminalLineProps) {
    return (
        <div className={cn('font-mono text-sm', className)}>
            {command && (
                <div className="flex items-center gap-2">
                    <span className="text-terminal-green">{prompt}</span>
                    <span className="text-terminal-text">{command}</span>
                </div>
            )}
            {output && <div className="text-terminal-text-muted ml-4">{output}</div>}
        </div>
    );
}
