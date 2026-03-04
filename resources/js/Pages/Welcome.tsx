import ApplicationLogo from '@/Components/ApplicationLogo';
import { PageProps } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export default function Welcome({
    auth,
}: PageProps<{ laravelVersion: string; phpVersion: string }>) {
    const [typedText, setTypedText] = useState('');
    const [showCursor, setShowCursor] = useState(true);
    const fullText = 'Capture. Inspect. Debug.';

    useEffect(() => {
        let index = 0;
        const timer = setInterval(() => {
            if (index < fullText.length) {
                setTypedText(fullText.slice(0, index + 1));
                index++;
            } else {
                clearInterval(timer);
            }
        }, 80);

        return () => clearInterval(timer);
    }, []);

    useEffect(() => {
        const cursorTimer = setInterval(() => {
            setShowCursor((prev) => !prev);
        }, 530);

        return () => clearInterval(cursorTimer);
    }, []);

    return (
        <>
            <Head title="viewhook.dev - Webhook Testing Made Simple" />

            <div className="bg-terminal-black min-h-screen">
                {/* Scanline overlay */}
                <div className="terminal-scanline pointer-events-none fixed inset-0 z-50 opacity-30" />

                {/* Grid background */}
                <div
                    className="fixed inset-0 opacity-[0.03]"
                    style={{
                        backgroundImage: `
                            linear-gradient(rgba(57, 211, 83, 0.5) 1px, transparent 1px),
                            linear-gradient(90deg, rgba(57, 211, 83, 0.5) 1px, transparent 1px)
                        `,
                        backgroundSize: '50px 50px',
                    }}
                />

                {/* Glowing orb effect */}
                <div className="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
                    <div className="bg-terminal-green/5 h-[600px] w-[600px] rounded-full blur-[120px]" />
                </div>

                {/* Header */}
                <header className="border-terminal-border/50 bg-terminal-bg/80 relative z-10 border-b backdrop-blur-sm">
                    <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                        <ApplicationLogo showText />

                        <nav className="flex items-center gap-4">
                            {auth.user ? (
                                <Link
                                    href={route('dashboard')}
                                    className="group border-terminal-green bg-terminal-green/10 text-terminal-green hover:bg-terminal-green/20 flex items-center gap-2 rounded-md border px-4 py-2 font-mono text-sm transition-all"
                                >
                                    <span>$</span> dashboard
                                    <span className="transition-transform group-hover:translate-x-1">
                                        →
                                    </span>
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={route('login')}
                                        className="text-terminal-text-muted hover:text-terminal-text rounded-md px-4 py-2 font-mono text-sm transition-colors"
                                    >
                                        login
                                    </Link>
                                    <Link
                                        href={route('register')}
                                        className="group border-terminal-green bg-terminal-green/10 text-terminal-green hover:bg-terminal-green/20 flex items-center gap-2 rounded-md border px-4 py-2 font-mono text-sm transition-all"
                                    >
                                        get started
                                        <span className="transition-transform group-hover:translate-x-1">
                                            →
                                        </span>
                                    </Link>
                                </>
                            )}
                        </nav>
                    </div>
                </header>

                {/* Hero Section */}
                <main className="relative z-10">
                    <div className="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-32">
                        <div className="text-center">
                            {/* Terminal prompt animation */}
                            <div className="border-terminal-border bg-terminal-surface/50 inline-flex items-center gap-2 rounded-full border px-4 py-2 backdrop-blur-sm">
                                <span className="relative flex h-2 w-2">
                                    <span className="bg-terminal-green absolute inline-flex h-full w-full animate-ping rounded-full opacity-75"></span>
                                    <span className="bg-terminal-green relative inline-flex h-2 w-2 rounded-full"></span>
                                </span>
                                <span className="text-terminal-text-muted font-mono text-sm">
                                    Real-time webhook monitoring
                                </span>
                            </div>

                            {/* Main headline */}
                            <h1 className="text-terminal-text mt-8 font-mono text-4xl font-bold tracking-tight sm:text-6xl lg:text-7xl">
                                <span className="text-terminal-green">$</span> viewhook.dev
                            </h1>

                            {/* Typing animation */}
                            <p className="text-terminal-text-muted mt-6 font-mono text-xl sm:text-2xl">
                                {typedText}
                                <span
                                    className={`ml-1 ${showCursor ? 'opacity-100' : 'opacity-0'}`}
                                >
                                    ▊
                                </span>
                            </p>

                            {/* Description */}
                            <p className="text-terminal-text-muted mx-auto mt-8 max-w-2xl font-sans text-lg">
                                The developer-first webhook testing tool. Create unique URLs,
                                capture incoming requests, and inspect every detail in real-time.
                            </p>

                            {/* CTA Buttons */}
                            <div className="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                                <Link
                                    href={route('register')}
                                    className="group border-terminal-green bg-terminal-green/10 text-terminal-green hover:bg-terminal-green/20 terminal-glow-strong flex items-center gap-3 rounded-lg border px-8 py-4 font-mono text-lg transition-all"
                                >
                                    <span className="text-terminal-green">$</span>
                                    start
                                    <span className="transition-transform group-hover:translate-x-1">
                                        →
                                    </span>
                                </Link>
                                <a
                                    href="#features"
                                    className="border-terminal-border text-terminal-text-muted hover:border-terminal-cyan/50 hover:text-terminal-cyan flex items-center gap-2 rounded-lg border px-8 py-4 font-mono text-lg transition-all"
                                >
                                    learn more
                                </a>
                            </div>
                        </div>

                        {/* Terminal Demo */}
                        <div className="mx-auto mt-20 max-w-4xl">
                            <div className="border-terminal-border bg-terminal-surface terminal-glow overflow-hidden rounded-xl border">
                                {/* Terminal header */}
                                <div className="border-terminal-border bg-terminal-bg flex items-center gap-3 border-b px-4 py-3">
                                    <div className="flex items-center gap-2">
                                        <span className="bg-terminal-red h-3 w-3 rounded-full" />
                                        <span className="bg-terminal-yellow h-3 w-3 rounded-full" />
                                        <span className="bg-terminal-green h-3 w-3 rounded-full" />
                                    </div>
                                    <span className="text-terminal-text-muted font-mono text-sm">
                                        ~/webhooks — bash
                                    </span>
                                </div>

                                {/* Terminal content */}
                                <div className="p-6 font-mono text-sm">
                                    <div className="space-y-4">
                                        <div>
                                            <div className="flex items-center gap-2">
                                                <span className="text-terminal-green">❯</span>
                                                <span className="text-terminal-text">
                                                    curl -X POST https://viewhook.dev/abc123 \
                                                </span>
                                            </div>
                                            <div className="text-terminal-text ml-6">
                                                -H "Content-Type: application/json" \
                                            </div>
                                            <div className="text-terminal-text ml-6">
                                                -d '&#123;"event": "payment.completed"&#125;'
                                            </div>
                                        </div>

                                        <div className="border-terminal-green/30 bg-terminal-green/5 rounded border p-4">
                                            <div className="text-terminal-green flex items-center gap-2">
                                                <span>✓</span>
                                                <span>Request captured successfully</span>
                                            </div>
                                            <div className="text-terminal-text-muted mt-2">
                                                <span className="text-terminal-cyan">POST</span>{' '}
                                                /abc123 •
                                                <span className="text-terminal-purple">
                                                    {' '}
                                                    200 OK
                                                </span>{' '}
                                                •<span> 12ms</span>
                                            </div>
                                        </div>

                                        <div>
                                            <div className="flex items-center gap-2">
                                                <span className="text-terminal-green">❯</span>
                                                <span className="text-terminal-text-muted cursor-blink"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Features Section */}
                    <section
                        id="features"
                        className="border-terminal-border bg-terminal-bg/50 border-t py-20"
                    >
                        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                            <div className="text-center">
                                <h2 className="text-terminal-text font-mono text-3xl font-bold sm:text-4xl">
                                    <span className="text-terminal-cyan">const</span> features =
                                    &#123;
                                </h2>
                            </div>

                            <div className="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                                {/* Feature 1 */}
                                <div className="group border-terminal-border bg-terminal-surface hover:border-terminal-green/50 hover:terminal-glow rounded-xl border p-6 transition-all">
                                    <div className="border-terminal-green/30 bg-terminal-green/10 flex h-12 w-12 items-center justify-center rounded-lg border">
                                        <svg
                                            className="text-terminal-green h-6 w-6"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={1.5}
                                                d="M13 10V3L4 14h7v7l9-11h-7z"
                                            />
                                        </svg>
                                    </div>
                                    <h3 className="text-terminal-text mt-4 font-mono text-lg font-semibold">
                                        realtime: <span className="text-terminal-green">true</span>
                                    </h3>
                                    <p className="text-terminal-text-muted mt-2">
                                        WebSocket-powered live updates. See requests the moment they
                                        arrive.
                                    </p>
                                </div>

                                {/* Feature 2 */}
                                <div className="group border-terminal-border bg-terminal-surface hover:border-terminal-cyan/50 rounded-xl border p-6 transition-all hover:shadow-[0_0_20px_rgba(88,166,255,0.1)]">
                                    <div className="border-terminal-cyan/30 bg-terminal-cyan/10 flex h-12 w-12 items-center justify-center rounded-lg border">
                                        <svg
                                            className="text-terminal-cyan h-6 w-6"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={1.5}
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                            />
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={1.5}
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                            />
                                        </svg>
                                    </div>
                                    <h3 className="text-terminal-text mt-4 font-mono text-lg font-semibold">
                                        inspect: <span className="text-terminal-cyan">"deep"</span>
                                    </h3>
                                    <p className="text-terminal-text-muted mt-2">
                                        Full request details: headers, body, query params, and more.
                                    </p>
                                </div>

                                {/* Feature 3 */}
                                <div className="group border-terminal-border bg-terminal-surface hover:border-terminal-purple/50 rounded-xl border p-6 transition-all hover:shadow-[0_0_20px_rgba(163,113,247,0.1)]">
                                    <div className="border-terminal-purple/30 bg-terminal-purple/10 flex h-12 w-12 items-center justify-center rounded-lg border">
                                        <svg
                                            className="text-terminal-purple h-6 w-6"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={1.5}
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                            />
                                        </svg>
                                    </div>
                                    <h3 className="text-terminal-text mt-4 font-mono text-lg font-semibold">
                                        secure: <span className="text-terminal-purple">true</span>
                                    </h3>
                                    <p className="text-terminal-text-muted mt-2">
                                        Private URLs, user authentication, and HTTPS encryption.
                                    </p>
                                </div>

                                {/* Feature 4 */}
                                <div className="group border-terminal-border bg-terminal-surface hover:border-terminal-yellow/50 rounded-xl border p-6 transition-all hover:shadow-[0_0_20px_rgba(210,153,34,0.1)]">
                                    <div className="border-terminal-yellow/30 bg-terminal-yellow/10 flex h-12 w-12 items-center justify-center rounded-lg border">
                                        <svg
                                            className="text-terminal-yellow h-6 w-6"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={1.5}
                                                d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            />
                                        </svg>
                                    </div>
                                    <h3 className="text-terminal-text mt-4 font-mono text-lg font-semibold">
                                        methods: <span className="text-terminal-yellow">["*"]</span>
                                    </h3>
                                    <p className="text-terminal-text-muted mt-2">
                                        All HTTP methods supported: GET, POST, PUT, DELETE, PATCH,
                                        and more.
                                    </p>
                                </div>

                                {/* Feature 5 */}
                                <div className="group border-terminal-border bg-terminal-surface hover:border-terminal-orange/50 rounded-xl border p-6 transition-all hover:shadow-[0_0_20px_rgba(247,129,102,0.1)]">
                                    <div className="border-terminal-orange/30 bg-terminal-orange/10 flex h-12 w-12 items-center justify-center rounded-lg border">
                                        <svg
                                            className="text-terminal-orange h-6 w-6"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={1.5}
                                                d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"
                                            />
                                        </svg>
                                    </div>
                                    <h3 className="text-terminal-text mt-4 font-mono text-lg font-semibold">
                                        storage:{' '}
                                        <span className="text-terminal-orange">"persistent"</span>
                                    </h3>
                                    <p className="text-terminal-text-muted mt-2">
                                        Request history preserved. Review past webhooks anytime.
                                    </p>
                                </div>

                                {/* Feature 6 */}
                                <div className="group border-terminal-border bg-terminal-surface hover:border-terminal-pink/50 rounded-xl border p-6 transition-all hover:shadow-[0_0_20px_rgba(219,97,162,0.1)]">
                                    <div className="border-terminal-pink/30 bg-terminal-pink/10 flex h-12 w-12 items-center justify-center rounded-lg border">
                                        <svg
                                            className="text-terminal-pink h-6 w-6"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={1.5}
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </div>
                                    <h3 className="text-terminal-text mt-4 font-mono text-lg font-semibold">
                                        setup: <span className="text-terminal-pink">"instant"</span>
                                    </h3>
                                    <p className="text-terminal-text-muted mt-2">
                                        No configuration needed. Get a webhook URL in seconds.
                                    </p>
                                </div>
                            </div>

                            <div className="mt-16 text-center">
                                <span className="text-terminal-text font-mono text-3xl">
                                    &#125;;
                                </span>
                            </div>
                        </div>
                    </section>

                    {/* CTA Section */}
                    <section className="border-terminal-border border-t py-20">
                        <div className="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
                            <h2 className="text-terminal-text font-mono text-3xl font-bold sm:text-4xl">
                                Ready to <span className="text-terminal-green">debug</span>?
                            </h2>
                            <p className="text-terminal-text-muted mt-4 text-lg">
                                Join developers who trust viewhook.dev for webhook testing.
                            </p>
                            <div className="mt-8">
                                <Link
                                    href={route('register')}
                                    className="group border-terminal-green bg-terminal-green/10 text-terminal-green hover:bg-terminal-green/20 terminal-glow-strong inline-flex items-center gap-3 rounded-lg border px-8 py-4 font-mono text-lg transition-all"
                                >
                                    <span className="text-terminal-green">$</span>
                                    signup
                                    <span className="transition-transform group-hover:translate-x-1">
                                        →
                                    </span>
                                </Link>
                            </div>
                        </div>
                    </section>
                </main>

                {/* Footer */}
                <footer className="border-terminal-border bg-terminal-bg/50 border-t py-8">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="flex flex-col items-center justify-between gap-4 sm:flex-row">
                            <ApplicationLogo showText />
                            <p className="text-terminal-text-subtle font-mono text-sm">
                                © 2026 viewhook.dev • Built for developers
                            </p>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
