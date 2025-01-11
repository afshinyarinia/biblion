<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\BookReview;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('review')->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'rating' => ['sometimes', 'required', 'integer', 'min:1', 'max:5'],
            'review' => ['sometimes', 'nullable', 'string', 'min:3', 'max:10000'],
            'contains_spoilers' => ['sometimes', 'boolean'],
        ];
    }
} 