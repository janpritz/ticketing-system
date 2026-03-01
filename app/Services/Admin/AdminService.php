<?php

namespace App\Services\Admin;

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
}
