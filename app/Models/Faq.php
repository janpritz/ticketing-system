<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faq extends Model
{
    use SoftDeletes;

    protected $table = 'faqs';

    protected $fillable = [
        'intent',
        'response',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Revisions (audit history) for this FAQ — newest first.
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(\App\Models\FaqRevision::class, 'faq_id')->orderByDesc('created_at');
    }
}