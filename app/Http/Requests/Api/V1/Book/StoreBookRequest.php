<?php

namespace App\Http\Requests\Api\V1\Book;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'string', 'unique:books,isbn'],
            'description' => ['nullable', 'string'],
            'total_pages' => ['required', 'integer', 'min:1'],
            'cover_image' => ['nullable', 'string', 'url'],
            'publisher' => ['nullable', 'string'],
            'publication_date' => ['nullable', 'date'],
            'language' => ['required', 'string', 'size:2'],
        ];
    }
}
