<?php

namespace App\Http\Requests\Api\V1\ReadingChallenge;

use Illuminate\Foundation\Http\FormRequest;

class StoreReadingChallengeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Temporary: Allow any user to create challenges until admin system is fully implemented
        return true;
        // TODO: Uncomment when admin system is implemented
        // return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'requirements' => ['required', 'array', 'min:1'],
            'requirements.*' => ['required', 'integer', 'min:1'],
            'is_public' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'requirements.min' => 'A reading challenge must have at least one requirement.',
            'requirements.*.min' => 'Each requirement must require at least one book.',
            'start_date.after_or_equal' => 'The challenge must start today or in the future.',
            'end_date.after' => 'The end date must be after the start date.',
        ];
    }
} 