<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Request
 */
final class RequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(HttpRequest $request): array
    {
        return [
            'id' => $this->resource_id,
            'method' => $this->method,
            'path' => $this->path,
            'content_type' => $this->content_type,
            'content_length' => $this->content_length,
            'headers' => $this->headers,
            'query_params' => $this->query_params,
            'body' => $this->body,
            'parsed_body' => $this->getParsedBody(),
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'is_json' => $this->isJson(),
            'is_form_data' => $this->isFormData(),
            'is_xml' => $this->isXml(),
            'webhook_forwards_count' => $this->whenCounted('webhookForwards'),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
