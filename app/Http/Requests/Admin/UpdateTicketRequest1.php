<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest1 extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            // Content updates
            'question'           => 'sometimes|required|string|max:5000',

            // Attachment management
            'attachments'        => 'nullable|array|max:5',
            'attachments.*'      => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB limit
            'delete_attachments' => 'nullable|string', // JSON string of paths to remove

            // Administrative updates (Optional)
            'status'             => 'sometimes|string|in:Open,Forwarded,Closed',
            'role_id'            => 'sometimes|integer|exists:roles,id',
            'staff_id'           => 'sometimes|nullable|integer|exists:users,id',
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'attachments.*.image' => 'The uploaded file must be an image.',
            'attachments.*.max'   => 'Each image must not exceed 5MB.',
            'status.in'           => 'The selected status is invalid.',
        ];
    }
}
