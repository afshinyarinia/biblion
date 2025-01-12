<?php

namespace App\Http\Requests\Api\V1\ReadingChallenge;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReadingChallengeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Temporary: Allow challenge creator to update until admin system is implemented
        return $this->route('reading_challenge')->created_by === $this->user()->id;
        // TODO: Uncomment when admin system is implemented
        // return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string', 'max:1000'],
            'start_date' => [
                'sometimes', 
                'required', 
                'date', 
                'after_or_equal:today',
                function ($attribute, $value, $fail) {
                    if ($this->route('reading_challenge')->participants()->exists()) {
                        $fail('Cannot change start date after participants have joined.');
                    }
                },
            ],
            'end_date' => ['sometimes', 'required', 'date', 'after:start_date'],
            'requirements' => [
                'sometimes', 
                'required', 
                'array', 
                'min:1',
                function ($attribute, $value, $fail) {
                    if ($this->route('reading_challenge')->participants()->exists()) {
                        $fail('Cannot change requirements after participants have joined.');
                    }
                },
            ],
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