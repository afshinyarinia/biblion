<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\ReadingProgress;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateReadingProgressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $book = $this->route('book');
        
        return [
            'status' => ['sometimes', 'required', 'string', 'in:not_started,in_progress,completed'],
            'current_page' => [
                'required_unless:status,not_started',
                'integer',
                'min:1',
                'max:' . $book->total_pages
            ],
            'reading_time_minutes' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'status' => 'reading status',
            'current_page' => 'current page',
            'reading_time_minutes' => 'reading time',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->input('status') === 'completed') {
            $this->merge([
                'current_page' => $this->route('book')->total_pages
            ]);
        }
    }
} 