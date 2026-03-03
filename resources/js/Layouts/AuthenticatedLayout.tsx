import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import type { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode, useState } from 'react';

export default function Authenticated({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const { user } = usePage<PageProps>().props.auth;

    const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);

    return (
        <div className="bg-terminal-black min-h-screen">
            {/* Subtle scanline effect overlay */}
            <div className="terminal-scanline pointer-events-none fixed inset-0 z-50 opacity-50" />

            <nav className="border-terminal-border bg-terminal-bg/95 border-b backdrop-blur-sm">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex h-16 justify-between">
                        <div className="flex">
                            <div className="flex shrink-0 items-center">
                                <Link href="/" className="transition-opacity hover:opacity-80">
                                    <ApplicationLogo showText />
                                </Link>
                            </div>

                            <div className="hidden space-x-1 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink
                                    href={route('dashboard')}
                                    active={route().current('dashboard')}
                                    className="font-mono text-sm"
                                >
                                    <span className="text-terminal-green">~</span>/dashboard
                                </NavLink>
                                <NavLink
                                    href={route('urls.index')}
                                    active={route().current('urls.*')}
                                    className="font-mono text-sm"
                                >
                                    <span className="text-terminal-green">~</span>/urls
                                </NavLink>
                            </div>
                        </div>

                        <div className="hidden sm:ms-6 sm:flex sm:items-center">
                            {/* Status indicator */}
                            <div className="border-terminal-border bg-terminal-surface mr-4 flex items-center gap-2 rounded-md border px-3 py-1.5">
                                <span className="relative flex h-2 w-2">
                                    <span className="bg-terminal-green absolute inline-flex h-full w-full animate-ping rounded-full opacity-75"></span>
                                    <span className="bg-terminal-green relative inline-flex h-2 w-2 rounded-full"></span>
                                </span>
                                <span className="text-terminal-text-muted font-mono text-xs">
                                    connected
                                </span>
                            </div>

                            <div className="relative ms-3">
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <span className="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                className="border-terminal-border bg-terminal-surface text-terminal-text hover:border-terminal-green/50 hover:bg-terminal-hover focus:ring-terminal-green/50 inline-flex items-center gap-2 rounded-md border px-3 py-2 font-mono text-sm transition duration-150 ease-in-out focus:ring-1 focus:outline-none"
                                            >
                                                {user.avatar ? (
                                                    <img
                                                        src={user.avatar}
                                                        alt={user.name}
                                                        className="ring-terminal-green/30 h-5 w-5 rounded-full ring-1"
                                                    />
                                                ) : (
                                                    <span className="bg-terminal-green/20 text-terminal-green flex h-5 w-5 items-center justify-center rounded-full text-xs">
                                                        {user.name.charAt(0).toUpperCase()}
                                                    </span>
                                                )}
                                                <span className="max-w-[120px] truncate">
                                                    {user.name}
                                                </span>
                                                <svg
                                                    className="text-terminal-text-muted h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fillRule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clipRule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </Dropdown.Trigger>

                                    <Dropdown.Content>
                                        <Dropdown.Link href={route('profile.edit')}>
                                            Profile
                                        </Dropdown.Link>
                                        <Dropdown.Link
                                            href={route('logout')}
                                            method="post"
                                            as="button"
                                        >
                                            Log Out
                                        </Dropdown.Link>
                                    </Dropdown.Content>
                                </Dropdown>
                            </div>
                        </div>

                        <div className="-me-2 flex items-center sm:hidden">
                            <button
                                onClick={() =>
                                    setShowingNavigationDropdown((previousState) => !previousState)
                                }
                                className="border-terminal-border text-terminal-text-muted hover:border-terminal-green/50 hover:bg-terminal-hover hover:text-terminal-text inline-flex items-center justify-center rounded-md border p-2 transition duration-150 ease-in-out focus:outline-none"
                            >
                                <svg
                                    className="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        className={
                                            !showingNavigationDropdown ? 'inline-flex' : 'hidden'
                                        }
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        className={
                                            showingNavigationDropdown ? 'inline-flex' : 'hidden'
                                        }
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div className={(showingNavigationDropdown ? 'block' : 'hidden') + ' sm:hidden'}>
                    <div className="space-y-1 pt-2 pb-3">
                        <ResponsiveNavLink
                            href={route('dashboard')}
                            active={route().current('dashboard')}
                        >
                            <span className="font-mono">~/dashboard</span>
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            href={route('urls.index')}
                            active={route().current('urls.*')}
                        >
                            <span className="font-mono">~/urls</span>
                        </ResponsiveNavLink>
                    </div>

                    <div className="border-terminal-border border-t pt-4 pb-1">
                        <div className="px-4">
                            <div className="text-terminal-text font-mono text-base font-medium">
                                {user.name}
                            </div>
                            <div className="text-terminal-text-muted font-mono text-sm">
                                {user.email}
                            </div>
                        </div>

                        <div className="mt-3 space-y-1">
                            <ResponsiveNavLink href={route('profile.edit')}>
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink method="post" href={route('logout')} as="button">
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            {header && (
                <header className="border-terminal-border bg-terminal-bg/50 border-b">
                    <div className="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">{header}</div>
                </header>
            )}

            <main className="relative">{children}</main>
        </div>
    );
}
