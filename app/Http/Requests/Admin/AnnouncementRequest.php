<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnnouncementRequest extends FormRequest
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
        $announcementId = $this->route('announcement')?->id ?? $this->route('announcement');

        return [
            'content' => 'required|string|max:10000',
            'starts_at'  => 'required|date|after_or_equal:today',
            'expires_at' => 'required|date|after_or_equal:starts_at',
            'role_id'   => 'nullable',
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('announcements', 'title')
                    ->whereNull('deleted_at')
                    ->ignore($announcementId),
            ],
        ];
    }
}
