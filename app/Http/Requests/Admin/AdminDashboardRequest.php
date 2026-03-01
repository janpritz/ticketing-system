<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminDashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        // Strictly following your logic: must be 'primary administrator'
        return $u && (strtolower((string) ($u->role ?? '')) === 'primary administrator');
    }

    public function rules(): array
    {
        return [
            //
        ];
    }
}
