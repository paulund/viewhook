<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Url;
use App\Rules\NotPrivateUrl;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Url $url */
        $url = $this->route('url');

        return $this->user()?->can('update', $url) ?? false;
    }

    /**
     * @return array<string, array<int, string|ValidationRule|\Illuminate\Validation\Rules\Unique>>
     */
    public function rules(): array
    {
        $this->route('url');

        return [
            'name' => ['sometimes', 'required', 'string', 'min:1', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'forward_to_url' => ['nullable', 'url:https', 'max:2000', new NotPrivateUrl],
            'forward_method' => ['sometimes', 'string', 'in:GET,POST,PUT,PATCH,DELETE'],
            'forward_headers' => ['nullable', 'array'],
            'forward_headers.*' => ['string', 'max:1000'],
            'notify_email' => ['sometimes', 'boolean'],
            'notify_slack' => ['sometimes', 'boolean'],
            'slack_webhook_url' => ['nullable', 'url', 'max:2000', new NotPrivateUrl],
        ];
    }
}
