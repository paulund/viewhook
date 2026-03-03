import { Transition } from '@headlessui/react';
import { useForm } from '@inertiajs/react';
import { FormEventHandler, useRef, useState } from 'react';

export default function UpdatePasswordForm({ className = '' }: { className?: string }) {
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);
    const [focusedField, setFocusedField] = useState<string | null>(null);

    const { data, setData, errors, put, reset, processing, recentlySuccessful } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const updatePassword: FormEventHandler = (e) => {
        e.preventDefault();

        put(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => reset(),
            onError: (errors) => {
                if (errors.password) {
                    reset('password', 'password_confirmation');
                    passwordInput.current?.focus();
                }

                if (errors.current_password) {
                    reset('current_password');
                    currentPasswordInput.current?.focus();
                }
            },
        });
    };

    return (
        <section className={className}>
            <header className="mb-6">
                <div className="flex items-center gap-2 font-mono text-sm">
                    <span className="text-terminal-green">❯</span>
                    <span className="text-terminal-text">./update --password</span>
                </div>
                <p className="text-terminal-text-muted mt-2 font-mono text-xs">
                    # Ensure your account uses a long, random password to stay secure
                </p>
            </header>

            <form onSubmit={updatePassword} className="space-y-5">
                <div>
                    <label
                        htmlFor="current_password"
                        className="text-terminal-text-muted block font-mono text-sm font-medium"
                    >
                        <span className="text-terminal-cyan">const</span> currentPassword =
                    </label>
                    <input
                        id="current_password"
                        name="current_password"
                        ref={currentPasswordInput}
                        type="password"
                        value={data.current_password}
                        onChange={(e) => setData('current_password', e.target.value)}
                        onFocus={() => setFocusedField('current_password')}
                        onBlur={() => setFocusedField(null)}
                        autoComplete="current-password"
                        className={`bg-terminal-bg text-terminal-text placeholder-terminal-text-subtle mt-2 block w-full rounded-md border px-4 py-3 font-mono text-sm transition-all focus:ring-0 focus:outline-none ${
                            errors.current_password
                                ? 'border-terminal-red focus:border-terminal-red'
                                : focusedField === 'current_password'
                                  ? 'border-terminal-green terminal-border-glow'
                                  : 'border-terminal-border focus:border-terminal-green'
                        }`}
                        placeholder='"•••••••••"'
                    />
                    {errors.current_password && (
                        <div className="text-terminal-red mt-2 flex items-center gap-2 font-mono text-xs">
                            <span>✗</span>
                            <span>{errors.current_password}</span>
                        </div>
                    )}
                </div>

                <div>
                    <label
                        htmlFor="password"
                        className="text-terminal-text-muted block font-mono text-sm font-medium"
                    >
                        <span className="text-terminal-cyan">const</span> newPassword =
                    </label>
                    <input
                        id="password"
                        name="password"
                        ref={passwordInput}
                        type="password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        onFocus={() => setFocusedField('password')}
                        onBlur={() => setFocusedField(null)}
                        autoComplete="new-password"
                        className={`bg-terminal-bg text-terminal-text placeholder-terminal-text-subtle mt-2 block w-full rounded-md border px-4 py-3 font-mono text-sm transition-all focus:ring-0 focus:outline-none ${
                            errors.password
                                ? 'border-terminal-red focus:border-terminal-red'
                                : focusedField === 'password'
                                  ? 'border-terminal-green terminal-border-glow'
                                  : 'border-terminal-border focus:border-terminal-green'
                        }`}
                        placeholder='"•••••••••"'
                    />
                    {errors.password && (
                        <div className="text-terminal-red mt-2 flex items-center gap-2 font-mono text-xs">
                            <span>✗</span>
                            <span>{errors.password}</span>
                        </div>
                    )}
                </div>

                <div>
                    <label
                        htmlFor="password_confirmation"
                        className="text-terminal-text-muted block font-mono text-sm font-medium"
                    >
                        <span className="text-terminal-cyan">const</span> confirmPassword =
                    </label>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        value={data.password_confirmation}
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                        onFocus={() => setFocusedField('password_confirmation')}
                        onBlur={() => setFocusedField(null)}
                        autoComplete="new-password"
                        className={`bg-terminal-bg text-terminal-text placeholder-terminal-text-subtle mt-2 block w-full rounded-md border px-4 py-3 font-mono text-sm transition-all focus:ring-0 focus:outline-none ${
                            errors.password_confirmation
                                ? 'border-terminal-red focus:border-terminal-red'
                                : focusedField === 'password_confirmation'
                                  ? 'border-terminal-green terminal-border-glow'
                                  : 'border-terminal-border focus:border-terminal-green'
                        }`}
                        placeholder='"•••••••••"'
                    />
                    {errors.password_confirmation && (
                        <div className="text-terminal-red mt-2 flex items-center gap-2 font-mono text-xs">
                            <span>✗</span>
                            <span>{errors.password_confirmation}</span>
                        </div>
                    )}
                </div>

                <div className="flex items-center gap-4 pt-2">
                    <button
                        type="submit"
                        disabled={processing}
                        className="group border-terminal-green bg-terminal-green/10 text-terminal-green hover:bg-terminal-green/20 flex items-center gap-2 rounded-md border px-6 py-2.5 font-mono text-sm font-medium transition-all disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span>$</span>
                        <span>{processing ? 'updating...' : 'save'}</span>
                        {!processing && (
                            <span className="transition-transform group-hover:translate-x-1">
                                →
                            </span>
                        )}
                    </button>

                    <Transition
                        show={recentlySuccessful}
                        enter="transition ease-in-out"
                        enterFrom="opacity-0"
                        leave="transition ease-in-out"
                        leaveTo="opacity-0"
                    >
                        <div className="text-terminal-green flex items-center gap-2 font-mono text-sm">
                            <span>✓</span>
                            <span>Saved</span>
                        </div>
                    </Transition>
                </div>
            </form>
        </section>
    );
}
