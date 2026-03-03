export interface User {
    id: string;
    name: string;
    email: string;
    avatar: string | null;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User;
    };
    flash: {
        success: string | null;
        error: string | null;
    };
};

export interface PaginatedData<T> {
    data: T[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        from: number | null;
        last_page: number;
        path: string;
        per_page: number;
        to: number | null;
        total: number;
    };
}

export type { Request, RequestSummary } from './request.d';
export type {
    CreateUrlForm,
    UpdateUrlForm,
    Url,
    UrlIndexProps,
    UrlShowProps,
    WebhookForward,
} from './url.d';
