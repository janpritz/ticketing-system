<?php

namespace App\Events;

use App\Models\Faq;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FaqDeleted
{
    use Dispatchable, SerializesModels;

    public $faq;

    public function __construct(Faq $faq)
    {
        $this->faq = $faq;
    }
}