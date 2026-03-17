<div class="px-5 py-3 border-t border-gray-200">
    <div class="flex items-center justify-between">
        <div class="text-sm text-slate-600">
            Showing {{ $users->perPage() }} per page — {{ $users->total() }} total
            @if ($users->total() > 0)
                &nbsp;•&nbsp; displaying {{ $users->firstItem() }}–{{ $users->lastItem() }}
            @endif
        </div>
        <div>
            {{ $users->links() }}
        </div>
    </div>
</div>
