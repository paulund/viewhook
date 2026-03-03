import DangerButton from '@/Components/DangerButton';
import Modal from '@/Components/Modal';
import SecondaryButton from '@/Components/SecondaryButton';
import type { Url } from '@/types';
import { useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface DeleteUrlModalProps {
    url: Url;
    show: boolean;
    onClose: () => void;
}

export default function DeleteUrlModal({ url, show, onClose }: DeleteUrlModalProps) {
    const { delete: destroy, processing } = useForm();

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        destroy(route('urls.destroy', url.id), {
            preserveScroll: true,
            onSuccess: () => onClose(),
        });
    };

    return (
        <Modal show={show} onClose={onClose}>
            <form onSubmit={submit} className="p-6">
                <h2 className="text-lg font-medium text-gray-900">Delete Webhook URL</h2>

                <p className="mt-1 text-sm text-gray-600">
                    Are you sure you want to delete <strong>{url.name}</strong>? This action cannot
                    be undone. All captured requests will be permanently deleted.
                </p>

                <div className="mt-6 flex justify-end gap-3">
                    <SecondaryButton onClick={onClose}>Cancel</SecondaryButton>

                    <DangerButton disabled={processing}>Delete URL</DangerButton>
                </div>
            </form>
        </Modal>
    );
}
