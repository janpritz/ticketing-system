<?php

namespace App\Observers;

use App\Models\Faq;
use App\Models\FaqRevision;
use Illuminate\Support\Facades\Auth;

class FaqObserver
{
    protected function createRevision(Faq $faq, string $action, array $meta = null, array $snapshot = null)
    {
        // Prefer explicit snapshot if provided (e.g., original values on update)
        $intent = $snapshot['intent'] ?? $faq->intent;
        $response = $snapshot['response'] ?? $faq->response;

        FaqRevision::create([
            'faq_id'   => $faq->id,
            'intent'   => $intent,
            'response' => $response,
            'user_id'  => Auth::id(),
            'action'   => $action,
            'meta'     => $meta,
        ]);
    }

    public function created(Faq $faq)
    {
        $this->createRevision($faq, 'create');
    }

    public function updating(Faq $faq)
    {
        // Record snapshot of original values before update
        $original = $faq->getOriginal();
        $this->createRevision($faq, 'update', ['changed' => $faq->getDirty()], [
            'intent' => $original['intent'] ?? ($original['topic'] ?? null),
            'response' => $original['response'] ?? null,
        ]);
    }

    /**
     * Record revision BEFORE the model is removed from the database.
     * Using the deleting event ensures the referenced FAQ row still exists
     * so the foreign key on faq_revisions can reference it safely.
     */
    public function deleting(Faq $faq)
    {
        $this->createRevision($faq, 'delete');
    }
}