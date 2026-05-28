<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Ensure the session has a verified email before allowing the store
        return session()->has('verified_email');
    }

    public function rules(): array
    {
        return [
            'role_id'              => 'nullable|integer|exists:roles,id',
            'question'             => 'required|string',
            'recepient_id'         => ['nullable'],
            'attachments'          => 'nullable|array|max:5',
            'attachments.*'        => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'g-recaptcha-response' => 'required|captcha',
        ];
    }
}
