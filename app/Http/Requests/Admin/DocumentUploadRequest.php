<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentUploadRequest extends FormRequest
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
            // 📝 Standard text columns
            'file_name' => [
                'required',
                'string',
                'max:255',
                // This replaces your boot model check with a clean UI validation error!
                Rule::unique('documents', 'file_name')->ignore($this->route('document')),
            ],
            'file_content' => ['required', 'string'],

            // 🔑 Relational columns
            'role_id' => ['nullable', 'integer', 'exists:roles,id'], // Make sure your roles table is called 'roles'
            'created_by' => ['nullable', 'integer', 'exists:users,id'],

            // ⚙️ System columns
            'rasa_doc_id' => ['nullable', 'string', 'max:255'],
            'file_size' => ['nullable', 'integer', 'min:0'],
            'file_type' => ['nullable', 'string', 'max:50'],
        ];
    }
}
