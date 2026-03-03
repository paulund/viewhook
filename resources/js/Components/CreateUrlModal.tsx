import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Modal from '@/Components/Modal';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import { useForm } from '@inertiajs/react';
import { FormEventHandler, useRef } from 'react';

interface CreateUrlModalProps {
    show: boolean;
    onClose: () => void;
}

export default function CreateUrlModal({ show, onClose }: CreateUrlModalProps) {
    const nameInput = useRef<HTMLInputElement>(null);

    const { data, setData, post, processing, reset, errors } = useForm({
        name: '',
        description: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('urls.store'), {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                onClose();
            },
            onError: () => nameInput.current?.focus(),
        });
    };

    const handleClose = () => {
        reset();
        onClose();
    };

    return (
        <Modal show={show} onClose={handleClose}>
            <form onSubmit={submit} className="p-6">
                <h2 className="text-lg font-medium text-gray-900">Create New Webhook URL</h2>

                <p className="mt-1 text-sm text-gray-600">
                    Create a new endpoint to capture incoming webhook requests.
                </p>

                <div className="mt-6">
                    <InputLabel htmlFor="name" value="Name" />

                    <TextInput
                        id="name"
                        ref={nameInput}
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        className="mt-1 block w-full"
                        placeholder="My Webhook"
                        required
                        maxLength={100}
                    />

                    <InputError message={errors.name} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="description" value="Description (optional)" />

                    <TextInput
                        id="description"
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                        className="mt-1 block w-full"
                        placeholder="Payment notifications from Stripe"
                        maxLength={500}
                    />

                    <InputError message={errors.description} className="mt-2" />
                </div>

                <div className="mt-6 flex justify-end gap-3">
                    <SecondaryButton onClick={handleClose}>Cancel</SecondaryButton>

                    <PrimaryButton disabled={processing}>Create URL</PrimaryButton>
                </div>
            </form>
        </Modal>
    );
}
