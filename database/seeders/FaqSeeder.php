<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faq;
use Illuminate\Support\Facades\File;

class FaqSeeder extends Seeder
{
    /**
     * Parse ../response.yml and seed faqs table.
     *
     * The parser looks for keys under "response:" that start with "utter_"
     * and captures the indented multiline block after "- text: |".
     */
    public function run(): void
    {
        // Try the provided relative path first
        $candidates = [
            base_path('../response.yml'),
            base_path('response.yml'),
            base_path('..' . DIRECTORY_SEPARATOR . 'response.yml'),
        ];

        $path = null;
        foreach ($candidates as $p) {
            if ($p && file_exists($p)) { $path = $p; break; }
        }

        if (!$path) {
            $this->command->info('response.yml not found; skipping FAQ seed.');
            return;
        }

        $contents = File::get($path);
        $lines = preg_split("/\r\n|\n|\r/", $contents);

        $inResponse = false;
        $currentKey = null;
        $collecting = false;
        $buffer = [];
        $items = [];

        foreach ($lines as $line) {
            // detect start of the response section
            if (!$inResponse && preg_match('/^\s*response\s*:\s*$/', $line)) {
                $inResponse = true;
                continue;
            }
            if (!$inResponse) continue;

            // detect new utter key
            if (preg_match('/^\s*(utter_[A-Za-z0-9_]+)\s*:\s*$/', $line, $m)) {
                // save previous
                if ($currentKey && count($buffer) > 0) {
                    $text = $this->normalizeBlock($buffer);
                    $items[$currentKey] = $text;
                }
                // start new
                $currentKey = $m[1];
                $buffer = [];
                $collecting = false;
                continue;
            }

            // detect "- text: |" line which signals block start
            if ($currentKey && preg_match('/^\s*-\s*text\s*:\s*\|\s*$/', $line)) {
                $collecting = true;
                continue;
            }

            // collect block lines (they are indented). Stop collecting when we hit another top-level key (handled above).
            if ($collecting) {
                // Remove up to 8 leading spaces (to normalize indentation), but preserve relative indentation/newlines
                // also trim right newline characters
                $buffer[] = preg_replace('/^\s{0,8}/', '', rtrim($line, "\r\n"));
            }
        }

        // flush last
        if ($currentKey && count($buffer) > 0) {
            $text = $this->normalizeBlock($buffer);
            $items[$currentKey] = $text;
        }

        if (empty($items)) {
            $this->command->info('No utter_* items found in response.yml; nothing to seed.');
            return;
        }

        $this->command->info('Seeding FAQs from response.yml ...');

        foreach ($items as $utter => $text) {
            // generate topic from utter name: remove leading "utter_", replace underscores with spaces, trim, and title case
            $topic = preg_replace('/^utter_/', '', $utter);
            $topic = str_replace('_', ' ', $topic);
            $topic = trim($topic);
            $topic = mb_convert_case($topic, MB_CASE_TITLE, "UTF-8");

            // Avoid duplicating existing exact entries; update or create
            Faq::updateOrCreate(
                ['topic' => $topic],
                ['response' => $text]
            );
        }

        $this->command->info('FAQ seeding complete. Inserted/updated: ' . count($items));
    }

    /**
     * Normalize block array lines into a single text string.
     */
    private function normalizeBlock(array $lines): string
    {
        // Remove possible leading/trailing empty lines
        while (count($lines) && trim($lines[0]) === '') array_shift($lines);
        while (count($lines) && trim($lines[count($lines)-1]) === '') array_pop($lines);

        // Join with newline, preserve paragraphs
        $text = implode("\n", $lines);

        // Collapse any repeated internal spaces at line starts
        $text = preg_replace("/\r\n/", "\n", $text);

        // Trim overall
        return trim($text);
    }
}