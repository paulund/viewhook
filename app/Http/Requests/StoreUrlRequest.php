<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Url;
use Illuminate\Foundation\Http\FormRequest;

final class StoreUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Url::class) ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
