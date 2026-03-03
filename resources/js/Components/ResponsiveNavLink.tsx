import { cn } from '@/lib/utils';
import { InertiaLinkProps, Link } from '@inertiajs/react';

export default function ResponsiveNavLink({
    active = false,
    className = '',
    children,
    ...props
}: InertiaLinkProps & { active?: boolean }) {
    return (
        <Link
            {...props}
            className={cn(
                'flex w-full items-start border-l-4 py-2 ps-3 pe-4 text-base font-medium transition duration-150 ease-in-out focus:outline-none',
                active
                    ? 'border-terminal-green bg-terminal-green/10 text-terminal-text'
                    : 'text-terminal-text-muted hover:border-terminal-green/50 hover:bg-terminal-hover hover:text-terminal-text border-transparent',
                className,
            )}
        >
            {children}
        </Link>
    );
}
