import TerminalCard from '@/Components/TerminalCard';
import { useEffect, useState } from 'react';

interface RateLimitInfo {
    limit: number;
    remaining: number;
    used: number;
    percentage: number;
    reset_at: number;
}

interface RateLimitIndicatorProps {
    rateLimit: RateLimitInfo;
    className?: string;
}

export function RateLimitIndicator({ rateLimit, className = '' }: RateLimitIndicatorProps) {
    const [timeUntilReset, setTimeUntilReset] = useState<string>('');

    useEffect(() => {
        const updateCountdown = () => {
            const now = Math.floor(Date.now() / 1000);
            const secondsRemaining = Math.max(0, rateLimit.reset_at - now);
            const minutes = Math.floor(secondsRemaining / 60);
            const seconds = secondsRemaining % 60;
            setTimeUntilReset(`${minutes}:${seconds.toString().padStart(2, '0')}`);
        };

        updateCountdown();
        const interval = setInterval(updateCountdown, 1000);

        return () => clearInterval(interval);
    }, [rateLimit.reset_at]);

    const isWarning = rateLimit.percentage >= 80;
    const isExceeded = rateLimit.remaining === 0;

    // Determine variant and colors
    const variant = isExceeded ? 'danger' : isWarning ? 'warning' : 'success';

    const progressBarClass = isExceeded
        ? 'bg-terminal-red'
        : isWarning
          ? 'bg-terminal-yellow'
          : 'bg-terminal-green';

    const textColorClass = isExceeded
        ? 'text-terminal-red'
        : isWarning
          ? 'text-terminal-yellow'
          : 'text-terminal-green';

    // Terminal-style icon
    const getIcon = () => {
        if (isExceeded) {
            return '⚠';
        } else if (isWarning) {
            return '⚡';
        }
        return '✓';
    };

    return (
        <TerminalCard
            title="rate-limit"
            subtitle="request throttling status"
            variant={variant}
            className={className}
        >
            <div className="space-y-4">
                {/* Header with icon and countdown */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <span className={`font-mono text-lg ${textColorClass}`}>{getIcon()}</span>
                        <span className="text-terminal-text-muted font-mono text-sm">status</span>
                    </div>
                    <div className="text-terminal-text-muted flex items-center gap-2 font-mono text-sm">
                        <span className="text-terminal-cyan">⏱</span>
                        <span>reset: {timeUntilReset}</span>
                    </div>
                </div>

                {/* Progress Bar */}
                <div className="space-y-2">
                    <div className="border-terminal-border bg-terminal-black h-2 overflow-hidden rounded border">
                        <div
                            className={`h-full transition-all duration-300 ${progressBarClass}`}
                            style={{ width: `${Math.min(rateLimit.percentage, 100)}%` }}
                        />
                    </div>

                    {/* Stats */}
                    <div className="flex items-center justify-between font-mono text-sm">
                        <div className="flex items-center gap-2">
                            <span className="text-terminal-text-muted">used:</span>
                            <span className={`font-semibold ${textColorClass}`}>
                                {rateLimit.used}
                            </span>
                            <span className="text-terminal-text-muted">/</span>
                            <span className="text-terminal-text">{rateLimit.limit}</span>
                        </div>
                        <div className="flex items-center gap-2">
                            <span className="text-terminal-text-muted">remaining:</span>
                            <span className="text-terminal-cyan font-semibold">
                                {rateLimit.remaining}
                            </span>
                        </div>
                    </div>
                </div>

                {/* Warning/Error Messages */}
                {isExceeded && (
                    <div className="border-terminal-red/30 bg-terminal-red/10 rounded border p-3">
                        <div className="flex items-start gap-2">
                            <span className="text-terminal-red">!</span>
                            <p className="text-terminal-red font-mono text-xs">
                                Rate limit exceeded. New requests will be rejected until reset.
                            </p>
                        </div>
                    </div>
                )}

                {isWarning && !isExceeded && (
                    <div className="border-terminal-yellow/30 bg-terminal-yellow/10 rounded border p-3">
                        <div className="flex items-start gap-2">
                            <span className="text-terminal-yellow">⚡</span>
                            <p className="text-terminal-yellow font-mono text-xs">
                                Approaching rate limit threshold ({rateLimit.percentage}% used)
                            </p>
                        </div>
                    </div>
                )}
            </div>
        </TerminalCard>
    );
}
