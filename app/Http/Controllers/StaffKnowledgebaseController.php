<?php

namespace App\Http\Controllers;

use App\Models\DocumentChange;
// use App\Models\Faq; // Removed - FAQ system deleted
// use App\Models\ProcessedFaq; // Removed - FAQ system deleted
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class StaffKnowledgebaseController extends Controller
{
    // FAQ CRUD - COMMENTED OUT: FAQ system deleted
    /*
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $query = Faq::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('intent', 'like', '%' . $search . '%')
                  ->orWhere('response', 'like', '%' . $search . '%');
            });
        }

        $faqs = $query->get();

        if ($request->ajax()) {
            return response()->json(['faqs' => $faqs]);
        }

        return view('staff.knowledgebase.index', compact('faqs'));
    }

    public function create()
    {
        return view('staff.knowledgebase.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'intent' => 'required|string',
            'description' => 'nullable|string',
            'response' => 'required|string',
        ]);

        $faq = Faq::create($request->all());

        // Log the change
        $content = $faq->intent . $faq->response;
        DocumentChange::create([
            'file_name' => (string) $faq->id,
            'action' => 'created',
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'old_content_hash' => null,
            'new_content_hash' => hash('sha256', $content),
            'change_timestamp' => now(),
            'training_required' => true,
            'training_completed' => false,
            'model_name' => 'Faq',
        ]);

        return redirect()->route('staff.knowledgebase.index')->with('success', 'FAQ created successfully');
    }

    public function show(Faq $faq)
    {
        return view('staff.knowledgebase.show', compact('faq'));
    }

    public function edit(Faq $faq)
    {
        return view('staff.knowledgebase.edit', compact('faq'));
    }

    public function update(Request $request, Faq $faq)
    {
        $request->validate([
            'intent' => 'required|string',
            'description' => 'nullable|string',
            'response' => 'required|string',
        ]);

        // Get old content hash
        $oldContent = $faq->intent . $faq->response;
        $oldHash = hash('sha256', $oldContent);

        $faq->update($request->all());

        // Get new content hash
        $newContent = $faq->intent . $faq->response;
        $newHash = hash('sha256', $newContent);

        // Log the change
        DocumentChange::create([
            'file_name' => (string) $faq->id,
            'action' => 'updated',
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'old_content_hash' => $oldHash,
            'new_content_hash' => $newHash,
            'change_timestamp' => now(),
            'training_required' => true,
            'training_completed' => false,
            'model_name' => 'Faq',
        ]);

        return redirect()->route('staff.knowledgebase.index')->with('success', 'FAQ updated successfully');
    }

    public function destroy(Faq $faq)
    {
        // Get old content hash before deletion
        $oldContent = $faq->intent . $faq->response;
        $oldHash = hash('sha256', $oldContent);

        // Log the change
        DocumentChange::create([
            'file_name' => (string) $faq->id,
            'action' => 'deleted',
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'old_content_hash' => $oldHash,
            'new_content_hash' => null,
            'change_timestamp' => now(),
            'training_required' => true,
            'training_completed' => false,
            'model_name' => 'Faq',
        ]);

        $faq->delete();
        return redirect()->route('staff.knowledgebase.index')->with('success', 'FAQ deleted successfully');
    }
    */

    // File upload handling
    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $rasaUrl = config('services.rasa.url');
        $uploadUrl = $rasaUrl . '/upload';

        try {
            $fileContent = file_get_contents($file->getRealPath());
            $response = Http::attach(
                'file',
                $fileContent,
                $file->getClientOriginalName()
            )->post($uploadUrl);

            if ($response->successful()) {
                // Log the change
                DocumentChange::create([
                    'file_name' => $file->getClientOriginalName(),
                    'action' => 'created',
                    'user_id' => Auth::id(),
                    'user_name' => Auth::user()->name,
                    'old_content_hash' => null,
                    'new_content_hash' => hash('sha256', $fileContent),
                    'change_timestamp' => now(),
                    'training_required' => true,
                    'training_completed' => false,
                    'model_name' => 'File',
                ]);

                return response()->json(['success' => true, 'message' => 'File uploaded successfully']);
            } else {
                return response()->json(['success' => false, 'message' => 'Failed to upload file']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error uploading file']);
        }
    }

    // Announcements CRUD
    public function announcementsIndex()
    {
        // Similar to admin, but for staff
        // Assuming announcements are fetched from Rasa
        $rasaUrl = config('services.rasa.url');
        try {
            $response = Http::get($rasaUrl . '/list-docs');
            $docs = $response->json();
            $announcements = [];
            foreach ($docs as $doc) {
                if ($doc['file_name'] === 'Announcements.txt') {
                    $downloadResponse = Http::get(str_replace('/list-docs', '/download/Announcements.txt', $rasaUrl));
                    $content = $downloadResponse->body();
                    // Parse announcements from content
                    $announcements = $this->parseAnnouncements($content);
                    break;
                }
            }
        } catch (\Exception $e) {
            $announcements = [];
        }

        return view('staff.knowledgebase.announcements.index', compact('announcements'));
    }

    public function announcementsStore(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        $rasaUrl = config('services.rasa.url');
        $uploadUrl = $rasaUrl . '/upload';

        try {
            // Get current announcements
            $downloadUrl = str_replace('/list-docs', '/download/Announcements.txt', $rasaUrl);
            $downloadResponse = Http::get($downloadUrl);
            $currentContent = $downloadResponse->successful() ? $downloadResponse->body() : '';

            $newAnnouncement = "Title: " . $request->input('title') . "\nContent: " . $request->input('content') . "\n---\n";
            $updatedContent = $currentContent . $newAnnouncement;

            Http::post($uploadUrl, [
                'file_name' => 'Announcements.txt',
                'file_content' => $updatedContent,
            ]);

            // Log the change
            DocumentChange::create([
                'file_name' => $request->input('title'),
                'action' => 'created',
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
                'old_content_hash' => hash('sha256', $currentContent),
                'new_content_hash' => hash('sha256', $updatedContent),
                'change_timestamp' => now(),
                'training_required' => true,
                'training_completed' => false,
                'model_name' => 'Announcement',
            ]);

            return response()->json(['success' => true, 'message' => 'Announcement added successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to add announcement']);
        }
    }

    public function announcementsUpdate(Request $request, $id)
    {
        // Similar logic for update
        // Omitted for brevity, implement similarly
        // Note: Add logging similar to store, with action 'updated'
    }

    public function announcementsDestroy($id)
    {
        // Similar logic for delete
        // Note: Add logging similar to store, with action 'deleted'
    }

    // Process closed tickets - COMMENTED OUT: FAQ system deleted
    /*
    public function processClosedTickets()
    {
        $staffId = Auth::id();

        // Get closed tickets handled by this staff that are not yet processed
        $closedTickets = Ticket::where('status', 'closed')
            ->where('staff_id', $staffId)
            ->whereDoesntHave('processedFaqs')
            ->get();

        foreach ($closedTickets as $ticket) {
            // Send to LLM for FAQ generation
            $faqData = $this->generateFaqFromTicket($ticket);

            if ($faqData) {
                $processedFaq = ProcessedFaq::create([
                    'ticket_id' => $ticket->id,
                    'staff_id' => $staffId,
                    'question' => $faqData['question'],
                    'response' => $faqData['response'],
                    'processed_at' => now(),
                ]);

                // Log the change
                $content = $processedFaq->question . $processedFaq->response;
                DocumentChange::create([
                    'file_name' => (string) $processedFaq->id,
                    'action' => 'created',
                    'user_id' => $staffId,
                    'user_name' => Auth::user()->name,
                    'old_content_hash' => null,
                    'new_content_hash' => hash('sha256', $content),
                    'change_timestamp' => now(),
                    'training_required' => true,
                    'training_completed' => false,
                    'model_name' => 'ProcessedFaq',
                ]);
            }
        }

        return response()->json(['message' => 'Closed tickets processed successfully']);
    }
    */

    private function generateFaqFromTicket(Ticket $ticket)
    {
        // Stub for LLM call
        // In real implementation, call OpenAI or other LLM API
        // For now, return dummy data
        return [
            'question' => 'How to ' . $ticket->subject,
            'response' => $ticket->response,
        ];
    }

    private function parseAnnouncements($content)
    {
        // Parse announcements from text
        $announcements = [];
        $parts = explode('---', $content);
        foreach ($parts as $index => $part) {
            $lines = explode("\n", trim($part));
            $title = '';
            $content = '';
            foreach ($lines as $line) {
                if (str_starts_with($line, 'Title: ')) {
                    $title = substr($line, 7);
                } elseif (str_starts_with($line, 'Content: ')) {
                    $content = substr($line, 9);
                }
            }
            if ($title || $content) {
                $announcements[] = [
                    'id' => $index + 1,
                    'title' => $title,
                    'content' => $content,
                ];
            }
        }
        return $announcements;
    }
}
