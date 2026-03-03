import Modal from '@/Components/Modal';
import { useForm } from '@inertiajs/react';
import { FormEventHandler, useRef, useState } from 'react';

export default function DeleteUserForm({ className = '' }: { className?: string }) {
    const [confirmingUserDeletion, setConfirmingUserDeletion] = useState(false);
    const [focusedField, setFocusedField] = useState<string | null>(null);
    const passwordInput = useRef<HTMLInputElement>(null);

    const {
        data,
        setData,
        delete: destroy,
        processing,
        reset,
        errors,
        clearErrors,
    } = useForm({
        password: '',
    });

    const confirmUserDeletion = () => {
        setConfirmingUserDeletion(true);
    };

    const deleteUser: FormEventHandler = (e) => {
        e.preventDefault();

        destroy(route('profile.destroy'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: () => passwordInput.current?.focus(),
            onFinish: () => reset(),
        });
    };

    const closeModal = () => {
        setConfirmingUserDeletion(false);

        clearErrors();
        reset();
    };

    return (
        <section className={`${className}`}>
            <header className="mb-6">
                <div className="flex items-center gap-2 font-mono text-sm">
                    <span className="text-terminal-red">❯</span>
                    <span className="text-terminal-text">./delete --account</span>
                </div>
                <p className="text-terminal-text-muted mt-2 font-mono text-xs">
                    # Warning: This action is permanent and cannot be undone
                </p>
                <div className="border-terminal-red/30 bg-terminal-red/10 mt-3 rounded border p-3">
                    <div className="text-terminal-red flex items-start gap-2 font-mono text-xs">
                        <span>⚠</span>
                        <span>
                            Once deleted, all resources and data will be permanently removed.
                            Download any data you wish to retain before proceeding.
                        </span>
                    </div>
                </div>
            </header>

            <button
                onClick={confirmUserDeletion}
                className="group border-terminal-red bg-terminal-red/10 text-terminal-red hover:bg-terminal-red/20 flex items-center gap-2 rounded-md border px-6 py-2.5 font-mono text-sm font-medium transition-all"
            >
                <span>$</span>
                <span>delete --permanent</span>
                <span className="transition-transform group-hover:translate-x-1">→</span>
            </button>

            <Modal show={confirmingUserDeletion} onClose={closeModal}>
                <div className="border-terminal-border bg-terminal-surface rounded-xl border">
                    {/* Modal Header */}
                    <div className="border-terminal-border bg-terminal-bg flex items-center gap-3 border-b px-4 py-3">
                        <div className="flex items-center gap-2">
                            <span className="bg-terminal-red h-3 w-3 rounded-full" />
                            <span className="bg-terminal-yellow h-3 w-3 rounded-full" />
                            <span className="bg-terminal-green h-3 w-3 rounded-full" />
                        </div>
                        <span className="text-terminal-text-muted font-mono text-sm">
                            ~/account/delete — bash
                        </span>
                    </div>

                    {/* Modal Content */}
                    <form onSubmit={deleteUser} className="p-6">
                        <div className="mb-4">
                            <div className="flex items-center gap-2 font-mono text-sm">
                                <span className="text-terminal-red">❯</span>
                                <span className="text-terminal-text">confirm --deletion</span>
                            </div>
                            <div className="border-terminal-red/30 bg-terminal-red/10 mt-3 rounded border p-3">
                                <div className="text-terminal-red flex items-center gap-2 font-mono text-xs">
                                    <span>⚠</span>
                                    <span>Are you sure you want to delete your account?</span>
                                </div>
                            </div>
                        </div>

                        <p className="text-terminal-text-muted font-mono text-xs">
                            # All resources and data will be permanently deleted. Enter your
                            password to confirm.
                        </p>

                        <div className="mt-6">
                            <label
                                htmlFor="password"
                                className="text-terminal-text-muted block font-mono text-sm font-medium"
                            >
                                <span className="text-terminal-cyan">const</span> password =
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
                                className={`bg-terminal-bg text-terminal-text placeholder-terminal-text-subtle mt-2 block w-full rounded-md border px-4 py-3 font-mono text-sm transition-all focus:ring-0 focus:outline-none ${
                                    errors.password
                                        ? 'border-terminal-red focus:border-terminal-red'
                                        : focusedField === 'password'
                                          ? 'border-terminal-red terminal-border-glow'
                                          : 'border-terminal-border focus:border-terminal-red'
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

                        <div className="mt-6 flex justify-end gap-3">
                            <button
                                type="button"
                                onClick={closeModal}
                                className="border-terminal-border text-terminal-text-muted hover:border-terminal-green/50 hover:text-terminal-text rounded-md border px-6 py-2.5 font-mono text-sm transition-all"
                            >
                                cancel
                            </button>

                            <button
                                type="submit"
                                disabled={processing}
                                className="group border-terminal-red bg-terminal-red/10 text-terminal-red hover:bg-terminal-red/20 flex items-center gap-2 rounded-md border px-6 py-2.5 font-mono text-sm font-medium transition-all disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span>$</span>
                                <span>{processing ? 'deleting...' : 'delete'}</span>
                                {!processing && (
                                    <span className="transition-transform group-hover:translate-x-1">
                                        →
                                    </span>
                                )}
                            </button>
                        </div>
                    </form>
                </div>
            </Modal>
        </section>
    );
}
