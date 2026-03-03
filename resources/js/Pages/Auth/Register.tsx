import ApplicationLogo from '@/Components/ApplicationLogo';
import { useCursorBlink } from '@/hooks/useCursorBlink';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const showCursor = useCursorBlink();
    const [focusedField, setFocusedField] = useState<string | null>(null);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <>
            <Head title="Register - viewhook.dev" />

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
                        <Link href="/">
                            <ApplicationLogo showText />
                        </Link>

                        <nav className="flex items-center gap-4">
                            <Link
                                href={route('login')}
                                className="text-terminal-text-muted hover:text-terminal-text rounded-md px-4 py-2 font-mono text-sm transition-colors"
                            >
                                login
                            </Link>
                        </nav>
                    </div>
                </header>

                {/* Main Content */}
                <main className="relative z-10 flex min-h-[calc(100vh-4rem)] items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
                    <div className="w-full max-w-md">
                        {/* Terminal Card */}
                        <div className="border-terminal-border bg-terminal-surface terminal-glow overflow-hidden rounded-xl border">
                            {/* Terminal Header */}
                            <div className="border-terminal-border bg-terminal-bg flex items-center gap-3 border-b px-4 py-3">
                                <div className="flex items-center gap-2">
                                    <span className="bg-terminal-red h-3 w-3 rounded-full" />
                                    <span className="bg-terminal-yellow h-3 w-3 rounded-full" />
                                    <span className="bg-terminal-green h-3 w-3 rounded-full" />
                                </div>
                                <span className="text-terminal-text-muted font-mono text-sm">
                                    ~/auth/register — bash
                                </span>
                            </div>

                            {/* Terminal Content */}
                            <div className="p-8">
                                {/* Command Prompt Header */}
                                <div className="mb-6">
                                    <div className="flex items-center gap-2 font-mono text-sm">
                                        <span className="text-terminal-green">❯</span>
                                        <span className="text-terminal-text">
                                            ./register --new-user
                                        </span>
                                    </div>
                                    <div className="border-terminal-green/30 bg-terminal-green/5 mt-3 rounded border p-3">
                                        <div className="text-terminal-green flex items-center gap-2 font-mono text-xs">
                                            <span>→</span>
                                            <span>Initialize new developer account</span>
                                        </div>
                                    </div>
                                </div>

                                <form onSubmit={submit} className="space-y-5">
                                    {/* Name Field */}
                                    <div>
                                        <label
                                            htmlFor="name"
                                            className="text-terminal-text-muted block font-mono text-sm font-medium"
                                        >
                                            <span className="text-terminal-cyan">const</span> name =
                                        </label>
                                        <div className="relative mt-2">
                                            <input
                                                id="name"
                                                name="name"
                                                type="text"
                                                value={data.name}
                                                onChange={(e) => setData('name', e.target.value)}
                                                onFocus={() => setFocusedField('name')}
                                                onBlur={() => setFocusedField(null)}
                                                autoComplete="name"
                                                required
                                                className={`bg-terminal-bg text-terminal-text placeholder-terminal-text-subtle block w-full rounded-md border px-4 py-3 font-mono text-sm transition-all focus:ring-0 focus:outline-none ${
                                                    errors.name
                                                        ? 'border-terminal-red focus:border-terminal-red'
                                                        : focusedField === 'name'
                                                          ? 'border-terminal-green terminal-border-glow'
                                                          : 'border-terminal-border focus:border-terminal-green'
                                                }`}
                                                placeholder='"John Doe"'
                                            />
                                            {focusedField === 'name' && (
                                                <div className="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2">
                                                    <span
                                                        className={`text-terminal-green font-mono text-sm ${showCursor ? 'opacity-100' : 'opacity-0'}`}
                                                    >
                                                        ▊
                                                    </span>
                                                </div>
                                            )}
                                        </div>
                                        {errors.name && (
                                            <div className="text-terminal-red mt-2 flex items-center gap-2 font-mono text-xs">
                                                <span>✗</span>
                                                <span>{errors.name}</span>
                                            </div>
                                        )}
                                    </div>

                                    {/* Email Field */}
                                    <div>
                                        <label
                                            htmlFor="email"
                                            className="text-terminal-text-muted block font-mono text-sm font-medium"
                                        >
                                            <span className="text-terminal-cyan">const</span> email
                                            =
                                        </label>
                                        <div className="relative mt-2">
                                            <input
                                                id="email"
                                                name="email"
                                                type="email"
                                                value={data.email}
                                                onChange={(e) => setData('email', e.target.value)}
                                                onFocus={() => setFocusedField('email')}
                                                onBlur={() => setFocusedField(null)}
                                                autoComplete="username"
                                                required
                                                className={`bg-terminal-bg text-terminal-text placeholder-terminal-text-subtle block w-full rounded-md border px-4 py-3 font-mono text-sm transition-all focus:ring-0 focus:outline-none ${
                                                    errors.email
                                                        ? 'border-terminal-red focus:border-terminal-red'
                                                        : focusedField === 'email'
                                                          ? 'border-terminal-green terminal-border-glow'
                                                          : 'border-terminal-border focus:border-terminal-green'
                                                }`}
                                                placeholder='"dev@example.com"'
                                            />
                                            {focusedField === 'email' && (
                                                <div className="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2">
                                                    <span
                                                        className={`text-terminal-green font-mono text-sm ${showCursor ? 'opacity-100' : 'opacity-0'}`}
                                                    >
                                                        ▊
                                                    </span>
                                                </div>
                                            )}
                                        </div>
                                        {errors.email && (
                                            <div className="text-terminal-red mt-2 flex items-center gap-2 font-mono text-xs">
                                                <span>✗</span>
                                                <span>{errors.email}</span>
                                            </div>
                                        )}
                                    </div>

                                    {/* Password Field */}
                                    <div>
                                        <label
                                            htmlFor="password"
                                            className="text-terminal-text-muted block font-mono text-sm font-medium"
                                        >
                                            <span className="text-terminal-cyan">const</span>{' '}
                                            password =
                                        </label>
                                        <div className="relative mt-2">
                                            <input
                                                id="password"
                                                name="password"
                                                type="password"
                                                value={data.password}
                                                onChange={(e) =>
                                                    setData('password', e.target.value)
                                                }
                                                onFocus={() => setFocusedField('password')}
                                                onBlur={() => setFocusedField(null)}
                                                autoComplete="new-password"
                                                required
                                                className={`bg-terminal-bg text-terminal-text placeholder-terminal-text-subtle block w-full rounded-md border px-4 py-3 font-mono text-sm transition-all focus:ring-0 focus:outline-none ${
                                                    errors.password
                                                        ? 'border-terminal-red focus:border-terminal-red'
                                                        : focusedField === 'password'
                                                          ? 'border-terminal-green terminal-border-glow'
                                                          : 'border-terminal-border focus:border-terminal-green'
                                                }`}
                                                placeholder='"•••••••••"'
                                            />
                                            {focusedField === 'password' && (
                                                <div className="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2">
                                                    <span
                                                        className={`text-terminal-green font-mono text-sm ${showCursor ? 'opacity-100' : 'opacity-0'}`}
                                                    >
                                                        ▊
                                                    </span>
                                                </div>
                                            )}
                                        </div>
                                        {errors.password && (
                                            <div className="text-terminal-red mt-2 flex items-center gap-2 font-mono text-xs">
                                                <span>✗</span>
                                                <span>{errors.password}</span>
                                            </div>
                                        )}
                                    </div>

                                    {/* Confirm Password Field */}
                                    <div>
                                        <label
                                            htmlFor="password_confirmation"
                                            className="text-terminal-text-muted block font-mono text-sm font-medium"
                                        >
                                            <span className="text-terminal-cyan">const</span>{' '}
                                            confirmPassword =
                                        </label>
                                        <div className="relative mt-2">
                                            <input
                                                id="password_confirmation"
                                                name="password_confirmation"
                                                type="password"
                                                value={data.password_confirmation}
                                                onChange={(e) =>
                                                    setData('password_confirmation', e.target.value)
                                                }
                                                onFocus={() =>
                                                    setFocusedField('password_confirmation')
                                                }
                                                onBlur={() => setFocusedField(null)}
                                                autoComplete="new-password"
                                                required
                                                className={`bg-terminal-bg text-terminal-text placeholder-terminal-text-subtle block w-full rounded-md border px-4 py-3 font-mono text-sm transition-all focus:ring-0 focus:outline-none ${
                                                    errors.password_confirmation
                                                        ? 'border-terminal-red focus:border-terminal-red'
                                                        : focusedField === 'password_confirmation'
                                                          ? 'border-terminal-green terminal-border-glow'
                                                          : 'border-terminal-border focus:border-terminal-green'
                                                }`}
                                                placeholder='"•••••••••"'
                                            />
                                            {focusedField === 'password_confirmation' && (
                                                <div className="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2">
                                                    <span
                                                        className={`text-terminal-green font-mono text-sm ${showCursor ? 'opacity-100' : 'opacity-0'}`}
                                                    >
                                                        ▊
                                                    </span>
                                                </div>
                                            )}
                                        </div>
                                        {errors.password_confirmation && (
                                            <div className="text-terminal-red mt-2 flex items-center gap-2 font-mono text-xs">
                                                <span>✗</span>
                                                <span>{errors.password_confirmation}</span>
                                            </div>
                                        )}
                                    </div>

                                    {/* Submit Button */}
                                    <div className="pt-2">
                                        <button
                                            type="submit"
                                            disabled={processing}
                                            className="group border-terminal-green bg-terminal-green/10 text-terminal-green hover:bg-terminal-green/20 terminal-glow-strong flex w-full items-center justify-center gap-3 rounded-lg border px-6 py-3 font-mono text-sm font-medium transition-all disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            <span className="text-terminal-green">$</span>
                                            <span>
                                                {processing
                                                    ? 'initializing...'
                                                    : 'register --execute'}
                                            </span>
                                            {!processing && (
                                                <span className="transition-transform group-hover:translate-x-1">
                                                    →
                                                </span>
                                            )}
                                        </button>
                                    </div>

                                    {/* Login Link */}
                                    <div className="border-terminal-border flex items-center justify-center gap-2 border-t pt-5">
                                        <span className="text-terminal-text-muted font-mono text-sm">
                                            already registered?
                                        </span>
                                        <Link
                                            href={route('login')}
                                            className="text-terminal-cyan hover:text-terminal-green font-mono text-sm transition-colors hover:underline"
                                        >
                                            login →
                                        </Link>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {/* Footer Info */}
                        <div className="mt-6 text-center">
                            <p className="text-terminal-text-subtle font-mono text-xs">
                                By registering, you agree to our terms of service
                            </p>
                        </div>
                    </div>
                </main>
            </div>
        </>
    );
}
