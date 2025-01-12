<?php

namespace App\Http\Requests\Api\V1\ReadingGoal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReadingGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_books' => ['sometimes', 'required', 'integer', 'min:1'],
            'target_pages' => ['sometimes', 'required', 'integer', 'min:1'],
        ];
    }
}
