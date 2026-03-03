export interface RateLimitInfo {
    limit: number;
    remaining: number;
    used: number;
    percentage: number;
    reset_at: number;
}

import type { Request } from './request.d';

export interface Url {
    id: string;
    name: string;
    description: string | null;
    last_request_at: string | null;
    created_at: string;
    updated_at: string;
    endpoint_url: string;
    requests_count?: number;
    requests_expire_after_hours: number;
    rate_limit: RateLimitInfo;
    requests?: Request[];
    forward_to_url: string | null;
    forward_method: string;
    forward_headers: Record<string, string> | null;
    has_forwarding: boolean;
    notify_email: boolean;
    notify_slack: boolean;
    slack_webhook_url: string | null;
    has_slack_webhook_url: boolean;
    has_email_notification: boolean;
    has_slack_notification: boolean;
}

export interface UrlIndexProps {
    urls: Url[];
}

export interface UrlShowProps {
    url: Url;
    recentForwards: WebhookForward[];
}

export interface WebhookForward {
    id: string;
    status_code: number | null;
    response_time_ms: number | null;
    error: string | null;
    is_successful: boolean;
    created_at: string;
}

export interface CreateUrlForm {
    name: string;
    description: string;
}

export interface UpdateUrlForm {
    name?: string;
    description?: string | null;
}
