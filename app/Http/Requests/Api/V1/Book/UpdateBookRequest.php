<?php

namespace App\Http\Requests\Api\V1\Book;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'author' => ['sometimes', 'required', 'string', 'max:255'],
            'isbn' => ['sometimes', 'required', 'string', 'size:13', Rule::unique('books')->ignore($this->route('book'))],
            'description' => ['sometimes', 'nullable', 'string'],
            'total_pages' => ['sometimes', 'required', 'integer', 'min:1'],
            'cover_image' => ['sometimes', 'nullable', 'url'],
            'publisher' => ['sometimes', 'nullable', 'string', 'max:255'],
            'publication_date' => ['sometimes', 'nullable', 'date'],
            'language' => ['sometimes', 'required', 'string', 'size:2'],
        ];
    }
}
