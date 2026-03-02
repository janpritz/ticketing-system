<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role_id'       => 'nullable|integer|exists:roles,id',
            'question'      => 'required|string',
            'recepient_id'  => ['required'],
            'email'         => 'required|email|max:255',
            'attachments'   => 'nullable|array|max:5',
            'attachments.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ];
    }
}
