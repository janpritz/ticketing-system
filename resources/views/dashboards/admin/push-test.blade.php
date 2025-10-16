@extends('layouts.app')

@section('title', 'Push Test')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8">
  <h1 class="text-xl font-semibold text-slate-900 mb-4">Push Notification Test</h1>
  @php $authId = auth()->id(); @endphp
  <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5 p-5 space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label for="userId" class="block text-xs text-gray-600 mb-1">User ID</label>
        <input id="userId" type="number" value="{{ $authId }}" class="w-full rounded-md border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Target user id">
      </div>
      <div>
        <label for="title" class="block text-xs text-gray-600 mb-1">Title</label>
        <input id="title" type="text" value="Test notification" class="w-full rounded-md border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Notification title">
      </div>
    </div>
    <div>
      <label for="body" class="block text-xs text-gray-600 mb-1">Body</label>
      <textarea id="body" rows="3" class="w-full rounded-md border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Notification body">This is a test push notification.</textarea>
    </div>
    <div>
      <label for="url" class="block text-xs text-gray-600 mb-1">Click URL (optional)</label>
      <input id="url" type="text" value="/staff/dashboard" class="w-full rounded-md border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="/some/path">
    </div>
    <div class="flex items-center gap-3">
      <button id="sendBtn" type="button" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Send Push</button>
      <button id="sendToMeBtn" type="button" class="inline-flex items-center rounded-md bg-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-300">Send To Me</button>
      <div class="text-sm text-gray-600">Authenticated ID: <span class="font-medium">{{ $authId ?? 'N/A' }}</span></div>
    </div>
    <div>
      <label class="block text-xs text-gray-600 mb-1">Result</label>
      <pre id="result" class="text-xs bg-gray-50 rounded-md border border-gray-200 p-3 overflow-auto max-h-64"></pre>
    </div>
  </div>
  <p class="mt-3 text-xs text-gray-500">Note: Ensure the target user has an active push subscription saved in storage/app/push_subscriptions.</p>
</div>
@endsection

@section('scripts')
<script>
(function() {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const userIdInput = document.getElementById('userId');
  const titleInput = document.getElementById('title');
  const bodyInput = document.getElementById('body');
  const urlInput = document.getElementById('url');
  const sendBtn = document.getElementById('sendBtn');
  const sendToMeBtn = document.getElementById('sendToMeBtn');
  const result = document.getElementById('result');

  async function send(userId) {
    if (!userId) { alert('Please enter a user id'); return; }
    try {
      const res = await fetch(`/admin/push/user/${encodeURIComponent(userId)}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf
        },
        credentials: 'same-origin',
        body: JSON.stringify({
          title: titleInput.value || 'Notification',
          body: bodyInput.value || '',
          data: { url: urlInput.value || '/' }
        })
      });
      const data = await res.json().catch(() => ({}));
      result.textContent = JSON.stringify(data, null, 2);
      if (window.showToast) window.showToast(res.ok ? 'success' : 'error', res.ok ? 'Push queued' : 'Push failed');
    } catch (e) {
      result.textContent = String(e);
      if (window.showToast) window.showToast('error', 'Network error');
    }
  }

  if (sendBtn) {
    sendBtn.addEventListener('click', () => send(userIdInput.value.trim()));
  }
  if (sendToMeBtn) {
    sendToMeBtn.addEventListener('click', () => {
      const id = {{ (int)($authId ?? 0) }};
      if (!id) { alert('No authenticated user id.'); return; }
      userIdInput.value = id;
      send(id);
    });
  }
})();
</script>
@endsection