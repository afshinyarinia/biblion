<?php

namespace App\Http\Requests\Api\V1\ReadingGoal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReadingGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year' => [
                'required',
                'integer',
                'min:' . date('Y'),
                Rule::unique('reading_goals')->where(function ($query) {
                    return $query->where('user_id', $this->user()->id);
                }),
            ],
            'target_books' => ['required', 'integer', 'min:1'],
            'target_pages' => ['required', 'integer', 'min:1'],
        ];
    }
} 