<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\{TicketResponseRequest, UpdateTicketRequest, TicketRequest};
use Illuminate\Support\Facades\{DB, Log, Mail, Auth};
use App\Http\Controllers\Controller;
use App\Services\Admin\TicketService;

class AdminTicketsController extends Controller
{

    public function index(Request $request, TicketService $service)
    {
        $filters = $request->only(['q', 'status', 'assignee', 'role', 'assignee_id', 'sort', 'per_page', 'page']);

        $result = $service->getFilteredTickets($filters);

        return response()->json($result)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }


    /**
     * Display the specified ticket and trigger "viewed" logic.
     */
    public function show(Ticket $ticket, TicketService $service)
    {
        // 1. Process the view (Updates first_viewed_at and sends email if needed)
        $service->markTicketAsViewed($ticket, Auth::user());

        // 2. Fetch the enriched data for the UI
        $data = $service->getTicketDetails($ticket);

        return response()->json($data)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    /**
     * Forward a ticket to a different staff member.
     */
    public function forward(Request $request, Ticket $ticket, TicketService $service)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $result = $service->forwardTicket($ticket, $validated['user_id'], Auth::user());

        return response()->json($result, $result['success'] ? 200 : 500);
    }
    public function respond(TicketResponseRequest $request, Ticket $ticket, TicketService $service)
    {
        // The request validation is handled by TicketResponseRequest
        $result = $service->respondToTicket(
            $ticket,
            $request->input('message'),
            $request->boolean('close', true),
            Auth::user()
        );

        return response()->json($result, $result['success'] ? 200 : 500);
    }


    /**
     * Update the specified ticket's metadata and status.
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket, TicketService $service)
    {
        $updatedTicket = $service->updateTicket($ticket, $request->validated, Auth::user());

        return response()->json($updatedTicket);
    }

    /**
     * Delete (soft) a ticket.
     */
    public function destroy($id, TicketService $service)
    {
        $service->handleDeleteTicket(Ticket::findOrFail($id));
        return response()->json(['deleted' => true]);
    }

    /**
     * Create a new ticket from the admin UI and assign staff based on category_id.
     * This runs the same assignment logic as the queued job but executes it synchronously
     * so admins see immediate assignment results in the UI.
     */
    /**
     * Create a new ticket manually from the admin dashboard.
     */
    public function store(TicketRequest $request, TicketService $service)
    {
        $validatedData = $request->validated();
        $ticket = $service->createTicket($validatedData, $validatedData->file('attachments'));

        if ($request->wantsJson()) {
            return response()->json(['ticket' => $ticket->load('staff', 'role')], 201);
        }

        return redirect()->route('admin.tickets.index')
            ->with('status', 'Ticket created. Assignment in progress.');
    }
}
