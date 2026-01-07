<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $table = 'documents';

    protected $fillable = [
        'file_name',
        'role_id',
        'created_by',
        'content',
        'rasa_doc_id',
        'file_size',
        'file_type',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * Prevent global duplicate file names at model level.
     * This will throw during create if a document with the same file_name already exists.
     */
    protected static function booted()
    {
        static::creating(function ($doc) {
            if (!empty($doc->file_name) && self::where('file_name', $doc->file_name)->exists()) {
                throw new \Exception('Duplicate file name not allowed');
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Build the TXT block representation for this document suitable for the Rasa Announcements-like format.
     * Example:
     * id: 123
     * file_name: example.txt
     * <content...>
     * ---------
     */
    public function toTxtBlock(): string
    {
        $content = (string) ($this->content ?? '');
        return "id: {$this->id}\nfile_name: {$this->file_name}\n{$content}\n---------\n";
    }

    /**
     * Rebuilds a single TXT snapshot for all documents (ordered by id desc).
     */
    public static function buildAllDocumentsTxt(): string
    {
        $content = '';
        $all = self::orderByDesc('id')->get();
        foreach ($all as $doc) {
            $content .= $doc->toTxtBlock();
        }
        return $content;
    }
}
