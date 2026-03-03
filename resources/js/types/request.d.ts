export interface Request {
    id: string;
    method: 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH' | 'OPTIONS' | 'HEAD';
    path: string;
    content_type: string | null;
    content_length: number;
    headers: Record<string, string>;
    query_params: Record<string, string> | null;
    body: string | null;
    parsed_body: unknown | null;
    ip_address: string | null;
    user_agent: string | null;
    is_json: boolean;
    is_form_data: boolean;
    is_xml: boolean;
    created_at: string;
}

export interface RequestSummary {
    id: string;
    method: Request['method'];
    path: string;
    content_type: string | null;
    content_length: number;
    ip_address: string | null;
    created_at: string;
}
