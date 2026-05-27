<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FAQStoreRequest extends FormRequest
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
            'general_topic' => 'required|string|max:255',
            'semantic_key' => 'required|string|max:255',
            'suggested_q' => 'required|string',
            'suggested_a' => 'required|string',
            'ticket_id' => 'nullable|integer|exists:tickets,id',
            'status' => 'required|string|in:publish,unpublish,pending',
        ];
    }
}