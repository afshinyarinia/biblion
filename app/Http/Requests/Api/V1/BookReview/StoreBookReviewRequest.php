<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\BookReview;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'min:3', 'max:10000'],
            'contains_spoilers' => ['sometimes', 'boolean'],
        ];
    }
} 