<?php

namespace App\Services\Admin;
use App\Models\DocumentChange;
use App\Http\Requests\Admin\DocumentChangeRequest;
use Illuminate\Support\Facades\Auth;

class DocumentChangeService
{
    /**
     * Create a new class instance.
     */
    public function handleDocumentChange(DocumentChangeRequest $request)
    {
        DocumentChange::create([
            'file_name' => $request->file_name,
            'action' => $request->action,
            'user_id' => Auth::user()->id ?? null,
            'user_name' => $user->name ?? null,
            'training_required' => true,
            'training_completed' => false,
        ]);
    }
}
