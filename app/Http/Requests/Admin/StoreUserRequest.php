<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\Mime\Message;

class StoreUserRequest extends FormRequest
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
            'name'               => 'required|string|max:255',
            'email'              => 'required|string|email|max:255|unique:users,email',
            'department_id'      => 'required|exists:departments,id',
            'role_id'            => 'required|exists:roles,id',
            'additional_roles'   => 'nullable|array',
            'additional_roles.*' => 'exists:roles,id',
        ];

        
    }
}
