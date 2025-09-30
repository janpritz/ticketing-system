@extends('layouts.admin')

@section('title', 'FAQ Revisions')

@section('admin-content')
<div class="sm:px-2">
  <div class="flex items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-slate-900">FAQ Revisions</h1>
      <div class="text-sm text-slate-500 mt-1">History for: <span class="font-medium text-slate-800">{{ $faq->topic }}</span></div>
    </div>
    <div>
      <a href="{{ route('admin.faqs.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium px-3 py-2">
        ← Back to FAQ Management
      </a>
    </div>
  </div>

  <div class="mt-4 bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="py-3 pl-5 pr-3 text-left font-medium">#</th>
            <th class="px-3 py-3 text-left font-medium">Action</th>
            <th class="px-3 py-3 text-left font-medium">User</th>
            <th class="px-3 py-3 text-left font-medium">When</th>
            <th class="px-3 py-3 text-left font-medium">Topic</th>
            <th class="px-3 py-3 text-left font-medium">Response (snippet)</th>
            <th class="py-3 pl-3 pr-5 text-left font-medium">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($revisions as $rev)
          <tr class="hover:bg-gray-50">
            <td class="py-3 pl-5 pr-3 align-top">{{ $rev->id }}</td>
            <td class="px-3 py-3 align-top">
              <div class="text-xs text-slate-700 font-medium">{{ ucfirst($rev->action) }}</div>
              @if($rev->meta && is_array($rev->meta) && isset($rev->meta['changed']))
                <div class="text-xs text-slate-500">Changed: {{ implode(', ', array_keys($rev->meta['changed'])) }}</div>
              @endif
            </td>
            <td class="px-3 py-3 align-top">
              <div class="text-slate-900 text-sm">{{ $rev->user->name ?? 'System' }}</div>
              <div class="text-xs text-slate-500">{{ $rev->user->email ?? '' }}</div>
            </td>
            <td class="px-3 py-3 align-top text-xs text-slate-500">{{ optional($rev->created_at)->format('Y-m-d h:i a') }}</td>
            <td class="px-3 py-3 align-top">
              <div class="text-slate-800">{{ \Illuminate\Support\Str::limit($rev->topic, 80) }}</div>
            </td>
            <td class="px-3 py-3 align-top">
              <div class="text-slate-600 whitespace-pre-line">{{ \Illuminate\Support\Str::limit($rev->response, 180) }}</div>
            </td>
            <td class="py-3 pl-3 pr-5 align-top">
              <div class="flex items-center gap-2">
                <button data-rev-id="{{ $rev->id }}" class="revertBtn inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                  Revert
                </button>
                <a href="{{ route('admin.faqs.show', ['faq' => $faq->id]) }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                  View Current
                </a>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="px-5 py-10 text-center text-sm text-gray-500">No revisions found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="px-5 py-3 border-t border-gray-200">
      {{ $revisions->links() }}
    </div>
  </div>
</div>
@endsection

@section('admin-scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  function showToast(type, message) {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: type === 'error' ? 'error' : (type === 'success' ? 'success' : 'info'),
      title: message,
      showConfirmButton: false,
      timer: 2500,
      timerProgressBar: true
    });
  }

  document.querySelectorAll('.revertBtn').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      const revId = btn.getAttribute('data-rev-id');
      if (!revId) return;
      const result = await Swal.fire({
        title: 'Revert FAQ?',
        text: 'This will restore the FAQ response to the selected revision. You can undo this later from revisions.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, revert',
        cancelButtonText: 'Cancel'
      });
      if (!result.isConfirmed) return;

      try {
        btn.disabled = true;
        const faqId = "{{ $faq->id }}";
        const url = "{{ url('/') }}/admin/faqs/" + faqId + "/revert/" + revId;
        const res = await fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({})
        });
        const json = await res.json();
        if (!res.ok) {
          const msg = json.message || 'Failed to revert';
          throw new Error(msg);
        }
        showToast('success', 'FAQ reverted');
        // Reload to show new current value and updated revision list
        setTimeout(() => location.reload(), 800);
      } catch (err) {
        console.error(err);
        showToast('error', err.message || 'Error');
      } finally {
        btn.disabled = false;
      }
    });
  });
})();
</script>
@endsection