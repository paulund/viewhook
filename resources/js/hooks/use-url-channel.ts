import type { Request } from '@/types';
import { useEffect, useRef } from 'react';

interface UseUrlChannelOptions {
    urlId: string;
    onRequestCaptured: (request: Request) => void;
}

export function useUrlChannel({ urlId, onRequestCaptured }: UseUrlChannelOptions) {
    const callbackRef = useRef(onRequestCaptured);

    // Keep callback ref updated
    useEffect(() => {
        callbackRef.current = onRequestCaptured;
    }, [onRequestCaptured]);

    useEffect(() => {
        const channel = window.Echo.private(`urls.${urlId}`);

        channel.listen('.request.captured', (data: { request: Request }) => {
            callbackRef.current(data.request);
        });

        return () => {
            channel.stopListening('.request.captured');
            window.Echo.leave(`urls.${urlId}`);
        };
    }, [urlId]);
}
