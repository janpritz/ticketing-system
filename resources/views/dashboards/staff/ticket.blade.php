@extends('layouts.app')

@section('title', 'Ticket Details')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5 p-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-lg font-semibold text-gray-900">Ticket Details</h1>
                <div class="mt-1 text-sm text-gray-500">Ticket #{{ 'T-' . \Illuminate\Support\Carbon::parse($ticket->date_created)->format('Y') . '-' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }}</div>
            </div>
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $ticket->status === 'Open' ? 'bg-blue-50 text-blue-700' : ($ticket->status === 'Re-routed' ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700') }}">
                    {{ $ticket->status }}
                </span>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6">
            <div>
                <div class="text-xs text-gray-500">Category</div>
                <div class="text-sm font-medium text-gray-900">{{ $ticket->category }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500">Question</div>
                <div class="text-sm text-gray-800 whitespace-pre-wrap">{{ $ticket->question }}</div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="text-xs text-gray-500">Sender Email</div>
                    <div class="text-sm text-gray-800">{{ $ticket->email }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Recipient ID</div>
                    <div class="text-sm text-gray-800">{{ $ticket->recepient_id }}</div>
                </div>
            </div>

            @if($ticket->response)
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3">
                <div class="text-xs font-semibold text-emerald-700 mb-1">Sent Response</div>
                <div class="text-sm text-gray-800 whitespace-pre-wrap">{{ $ticket->response }}</div>
            </div>
            @endif

            <div>
                <div class="text-xs text-gray-500 mb-2">Routing History</div>
                <div class="space-y-2">
                    @forelse($ticket->routingHistories ?? [] as $h)
                        <div class="rounded-md p-3 bg-gray-50 border">
                            <div class="text-xs text-gray-600">{{ $h->status }} • {{ optional($h->staff)->name ?? '-' }}</div>
                            <div class="text-sm text-gray-800 mt-1">{{ $h->notes }}</div>
                            <div class="text-xs text-gray-500 mt-1">{{ optional($h->routed_at)->format('Y-m-d h:i a') }}</div>
                        </div>
                    @empty
                        <div class="text-xs text-gray-500">No routing history.</div>
                    @endforelse
                </div>
            </div>

            @if($ticket->status !== 'Closed')
            <form method="POST" action="{{ url('/staff/tickets/' . $ticket->id . '/respond') }}">
                @csrf
                <div class="mt-4">
                    <label for="message" class="block text-xs font-medium text-gray-700">Response Message</label>
                    <textarea id="message" name="message" rows="6" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Type your response..."></textarea>
                </div>
                <div class="mt-4 flex items-center justify-end gap-3">
                    <a href="{{ route('staff.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50">Back</a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Send Response & Close</button>
                </div>
            </form>
            @else
            <div class="mt-4 text-sm text-gray-500">Ticket is closed and cannot be responded to.</div>
            @endif
        </div>
    </div>
</div>
@endsection