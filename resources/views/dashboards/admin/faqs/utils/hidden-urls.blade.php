<div id="admin-faqs-state" class="hidden" data-list-url="{{ route('admin.faqs.list') ?? route('admin.faqs.index') }}"
    data-update-status-url="{{ route('admin.faqs.update-status') }}"
    data-process-analysis-url="{{ route('admin.faqs.process-analysis') }}"
    data-unprocessed-tickets="{{ $unprocessedTickets ?? 0 }}">
</div>
