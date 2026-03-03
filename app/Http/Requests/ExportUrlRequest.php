<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Url;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

final class ExportUrlRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Url $url */
        $url = $this->route('url');

        /** @var User $user */
        $user = $this->user();

        return $user->can('view', $url);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'format' => ['sometimes', 'string', 'in:csv,json'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'format.in' => 'Invalid export format. Use "csv" or "json".',
        ];
    }

    /**
     * Get the export format.
     */
    public function getExportFormat(): string
    {
        return $this->validated('format') ?? 'csv';
    }
}
