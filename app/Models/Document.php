<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{

    use SoftDeletes;
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
    /**
     * Convert the document content into a clean block of text for the Rasa server.
     */
    public function toTxtBlock(): string
    {
        // 1. Get the raw content from the database
        $content = $this->content ?? '';

        // 2. Remove the metadata header (Roles: ...)
        $sanitized = $this->stripLeadingRolesLine($content);

        // 3. Add a footer or ID for traceability (optional, but helpful for debugging)
        return "Ref ID: {$this->id}\n\n" . trim($sanitized);
    }

    /**
     * Sanitizer: Removes the first line if it's a roles metadata header.
     */
    private function stripLeadingRolesLine(string $content): string
    {
        // Using the optimized regex approach to save memory
        // Looks for "roles:" at the very start of the string
        return preg_replace('/^\s*roles\s*:.*(\r\n|\n|\r)/i', '', $content, 1);
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
