@extends('layouts.admin')

@section('title', 'FAQs Management')

@section('admin-content')
    <div class="p-6 bg-white rounded-lg shadow">
        <h2 class="text-2xl font-semibold mb-4">FAQs Management</h2>
        <p class="text-gray-600 mb-6">Manage frequently asked questions diplayed in your create ticket page.</p>
        <!-- FAQ management interface goes here -->
        <button id="process-faqs" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded" style="float: right;">Process FAQs</button>
        <button id="analyze-sentiment" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Analyze Sentiment</button>
        <table id="sentiment-table" class="mt-4 w-full border-collapse border border-gray-300">
            <thead>
                <tr>
                    <th class="border p-2">Ticket ID</th>
                    <th class="border p-2">Question</th>
                    <th class="border p-2">Response</th>
                    <th class="border p-2">Sentiment Score</th>
                    <th class="border p-2">General Topic</th>
                    <th class="border p-2">Processed At</th>
                </tr>
            </thead>
            <tbody>
                @foreach(\App\Models\SentimentAnalysis::with('ticket')->orderBy('processed_at', 'desc')->get() as $analysis)
                    <tr>
                        <td class="border p-2">{{ $analysis->ticket_id }}</td>
                        <td class="border p-2">{{ $analysis->ticket->question ?? 'N/A' }}</td>
                        <td class="border p-2">{{ $analysis->ticket->response ?? 'N/A' }}</td>
                        <td class="border p-2">{{ $analysis->sentiment_score }}</td>
                        <td class="border p-2">{{ $analysis->general_topic }}</td>
                        <td class="border p-2">{{ $analysis->processed_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <script>
            document.getElementById('process-faqs').addEventListener('click', function() {
                fetch('/process-faqs', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({})
                }).then(response => response.json()).then(data => console.log(data));
            });
        </script>
        <script>
            document.getElementById('analyze-sentiment').addEventListener('click', function() {
                // Placeholder for AJAX call; replace with actual endpoint
                fetch('/analyze-sentiment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' // Assuming Laravel CSRF protection
                    },
                    body: JSON.stringify({
                        ticket_id: 1  // Replace with dynamic ticket ID selection logic
                    })
                }).then(response => response.json()).then(data => console.log(data));
            });
        </script>
        <div class="border-t pt-4">
            <p class="text-gray-500">This section is under construction. Please check back later.</p>
        </div>
    </div>
@endsection

@section('admin-scripts')
    @parent
@endsection