import { SVGAttributes } from 'react';

interface ApplicationLogoProps extends SVGAttributes<SVGElement> {
    showText?: boolean;
}

export default function ApplicationLogo({
    showText = false,
    className,
    ...props
}: ApplicationLogoProps) {
    return (
        <div className={`flex items-center gap-3 ${className || ''}`}>
            {/* Terminal icon with webhook hook */}
            <svg
                {...props}
                viewBox="0 0 40 40"
                xmlns="http://www.w3.org/2000/svg"
                className="h-9 w-9"
            >
                {/* Terminal window frame */}
                <rect
                    x="2"
                    y="6"
                    width="36"
                    height="28"
                    rx="3"
                    fill="currentColor"
                    className="text-terminal-surface"
                />
                <rect
                    x="2"
                    y="6"
                    width="36"
                    height="28"
                    rx="3"
                    stroke="currentColor"
                    strokeWidth="2"
                    fill="none"
                    className="text-terminal-green"
                />

                {/* Window dots */}
                <circle cx="8" cy="11" r="1.5" className="fill-terminal-red" />
                <circle cx="13" cy="11" r="1.5" className="fill-terminal-yellow" />
                <circle cx="18" cy="11" r="1.5" className="fill-terminal-green" />

                {/* Terminal prompt */}
                <text
                    x="7"
                    y="25"
                    fontFamily="monospace"
                    fontSize="10"
                    fontWeight="bold"
                    className="fill-terminal-green"
                >
                    &gt;_
                </text>

                {/* Webhook arrow/hook */}
                <path
                    d="M25 18 L30 23 L25 28"
                    stroke="currentColor"
                    strokeWidth="2.5"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    fill="none"
                    className="text-terminal-cyan"
                />
                <circle cx="33" cy="23" r="2" className="fill-terminal-cyan" />
            </svg>

            {showText && (
                <span className="text-terminal-text font-mono text-lg font-bold tracking-tight">
                    <span className="text-terminal-green">viewhook</span>
                    <span className="text-terminal-text-muted">.</span>
                    <span className="text-terminal-cyan">dev</span>
                </span>
            )}
        </div>
    );
}
