import { Transition } from '@headlessui/react';
import { useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

export default function UpdateProfileInformation({ className = '' }: { className?: string }) {
    const user = usePage().props.auth.user;
    const [focusedField, setFocusedField] = useState<string | null>(null);

    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm({
        name: user.name,
        email: user.email,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        patch(route('profile.update'));
    };

    return (
        <section className={className}>
            <header className="mb-6">
                <div className="flex items-center gap-2 font-mono text-sm">
                    <span className="text-terminal-green">❯</span>
                    <span className="text-terminal-text">./update --profile</span>
                </div>
                <p className="text-terminal-text-muted mt-2 font-mono text-xs">
                    # Update your account profile information and email address
                </p>
            </header>

            <form onSubmit={submit} className="space-y-5">
                <div>
                    <label
                        htmlFor="name"
                        className="text-terminal-text-muted block font-mono text-sm font-medium"
                    >
                        <span className="text-terminal-cyan">const</span> name =
                    </label>
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
                        className={`bg-terminal-bg text-terminal-text placeholder-terminal-text-subtle mt-2 block w-full rounded-md border px-4 py-3 font-mono text-sm transition-all focus:ring-0 focus:outline-none ${
                            errors.name
                                ? 'border-terminal-red focus:border-terminal-red'
                                : focusedField === 'name'
                                  ? 'border-terminal-green terminal-border-glow'
                                  : 'border-terminal-border focus:border-terminal-green'
                        }`}
                        placeholder='"Your Name"'
                    />
                    {errors.name && (
                        <div className="text-terminal-red mt-2 flex items-center gap-2 font-mono text-xs">
                            <span>✗</span>
                            <span>{errors.name}</span>
                        </div>
                    )}
                </div>

                <div>
                    <label
                        htmlFor="email"
                        className="text-terminal-text-muted block font-mono text-sm font-medium"
                    >
                        <span className="text-terminal-cyan">const</span> email =
                    </label>
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
                        className={`bg-terminal-bg text-terminal-text placeholder-terminal-text-subtle mt-2 block w-full rounded-md border px-4 py-3 font-mono text-sm transition-all focus:ring-0 focus:outline-none ${
                            errors.email
                                ? 'border-terminal-red focus:border-terminal-red'
                                : focusedField === 'email'
                                  ? 'border-terminal-green terminal-border-glow'
                                  : 'border-terminal-border focus:border-terminal-green'
                        }`}
                        placeholder='"dev@example.com"'
                    />
                    {errors.email && (
                        <div className="text-terminal-red mt-2 flex items-center gap-2 font-mono text-xs">
                            <span>✗</span>
                            <span>{errors.email}</span>
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
                        <span>{processing ? 'saving...' : 'save'}</span>
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
