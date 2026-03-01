<?php

namespace App\Services\Admin;
use App\Models\DocumentChange;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminService
{
    /**
     * Create a new class instance.
     */
    public function handleStripLeadingRolesLine(string $content)
    {
        $content = ltrim($content, "\r\n");
        $lines = preg_split("/\r\n|\n|\r/", $content);
        if (!$lines || count($lines) === 0) {
            return $content;
        }

        if (preg_match('/^roles\s*:/i', (string) $lines[0])) {
            array_shift($lines);
            return ltrim(implode("\n", $lines), "\r\n");
        }
    }

    public function getDocumentLogs(array $filters): LengthAwarePaginator
    {
        $q = $filters['q'];

        return DocumentChange::with('user')
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . $q . '%';
                $query->where(function ($sub) use ($like) {
                    $sub->where('file_name', 'like', $like)
                        ->orWhere('action', 'like', $like)
                        ->orWhereHas('user', fn($u) => $u->where('name', 'like', $like));
                });
            })
            ->orderBy('change_timestamp', 'desc')
            ->paginate($filters['per_page'])
            ->appends(['q' => $q, 'per_page' => $filters['per_page']]);
    }
}
