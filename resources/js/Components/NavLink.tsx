import { cn } from '@/lib/utils';
import { InertiaLinkProps, Link } from '@inertiajs/react';

export default function NavLink({
    active = false,
    className = '',
    children,
    ...props
}: InertiaLinkProps & { active: boolean }) {
    return (
        <Link
            {...props}
            className={cn(
                'inline-flex items-center border-b-2 px-3 py-4 text-sm font-medium transition duration-150 ease-in-out focus:outline-none',
                active
                    ? 'border-terminal-green text-terminal-text'
                    : 'text-terminal-text-muted hover:border-terminal-green/50 hover:text-terminal-text border-transparent',
                className,
            )}
        >
            {children}
        </Link>
    );
}
