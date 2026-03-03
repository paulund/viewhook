import { useEffect, useState } from 'react';

export function useCursorBlink(intervalMs = 530): boolean {
    const [show, setShow] = useState(true);

    useEffect(() => {
        const id = setInterval(() => setShow((v) => !v), intervalMs);
        return () => clearInterval(id);
    }, [intervalMs]);

    return show;
}
