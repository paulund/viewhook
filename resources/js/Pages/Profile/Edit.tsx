import TerminalCard from '@/Components/TerminalCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';

export default function Edit() {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center gap-3">
                    <span className="text-terminal-text font-mono text-xl font-semibold">
                        <span className="text-terminal-green">$</span> profile
                    </span>
                    <span className="cursor-blink text-terminal-green"></span>
                </div>
            }
        >
            <Head title="Profile" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                    <TerminalCard title="profile.info" subtitle="user configuration" glow>
                        <UpdateProfileInformationForm className="max-w-xl" />
                    </TerminalCard>

                    <TerminalCard title="security" subtitle="password management">
                        <UpdatePasswordForm className="max-w-xl" />
                    </TerminalCard>

                    <TerminalCard title="danger-zone" subtitle="account deletion" variant="danger">
                        <DeleteUserForm className="max-w-xl" />
                    </TerminalCard>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
