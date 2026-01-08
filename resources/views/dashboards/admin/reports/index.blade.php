@extends('layouts.admin')

@section('title', 'Reports & Analytics')

@section('admin-content')
<div class="sm:px-2">
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
      <h1 class="text-2xl font-semibold text-slate-900">Reports & Analytics</h1>
      <p class="text-sm text-slate-500 mt-1">Monitor ticket trends and system performance</p>
    </div>
    <div class="flex items-center gap-3">
      <div class="flex items-center gap-2">
        <label class="text-sm font-medium text-gray-700">Time Range:</label>
        <select id="timeRangeSelect" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">
          <option value="7">Last 7 Days</option>
          <option value="30" selected>Last 30 Days</option>
          <option value="90">Last 90 Days</option>
        </select>
      </div>
    </div>
  </div>

  <!-- KPI Cards -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Current Open Tickets -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Current Open Tickets</p>
          <p class="text-3xl font-bold text-gray-900" id="currentOpenTicketsValue">{{ $currentOpenTickets }}</p>
        </div>
        <div class="p-3 bg-blue-50 rounded-lg">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
      </div>
    </div>

    <!-- Placeholder for future KPIs -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Avg Resolution Time</p>
          <p class="text-3xl font-bold text-gray-900" id="avgResolutionTimeValue">{{ $avgResolutionTime }}</p>
        </div>
        <div class="p-3 bg-green-50 rounded-lg">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Total Tickets</p>
          <p class="text-3xl font-bold text-gray-900" id="totalTicketsValue">{{ $totalTicketsThisMonth }}</p>
        </div>
        <div class="p-3 bg-purple-50 rounded-lg">
          <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4"/>
          </svg>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Overdue Tickets</p>
          <p class="text-3xl font-bold text-gray-900" id="overdueTicketsValue">{{ $overdueTickets }}</p>
        </div>
        <div class="p-3 bg-red-50 rounded-lg">
          <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
          </svg>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts Section -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Backlog Trend Chart -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Backlog Trend</h3>
        <div class="text-sm text-gray-500">Open tickets over time</div>
      </div>
      <div class="h-64">
        <canvas id="backlogTrendChart"></canvas>
      </div>
    </div>

    <!-- Tickets Solved/Closed (Last 30 Days) -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 id="ticketsSolvedTitle" class="text-lg font-semibold text-gray-900">Tickets Solved (30 Days)</h3>
        <div class="text-sm text-gray-500">Staff by resolution count</div>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-gray-600">
            <tr>
              <th class="py-2 pl-3 pr-2 text-left font-medium">Staff</th>
              <th class="px-2 py-2 text-left font-medium">Tickets</th>
            </tr>
          </thead>
          <tbody id="ticketsSolvedBody" class="divide-y divide-gray-100">
            @forelse(($ticketsSolved ?? []) as $staff)
            <tr class="hover:bg-gray-50">
              <td class="py-2 pl-3 pr-2 align-top text-gray-900">{{ $staff['name'] }}</td>
              <td class="px-2 py-2 align-top font-medium text-slate-900">{{ number_format($staff['count']) }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="2" class="px-3 py-6 text-center text-sm text-gray-500">No solved tickets.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Staff Performance Section -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Tickets Assigned (Current Workload) -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Current Workload</h3>
        <div class="text-sm text-gray-500">Assigned tickets by staff</div>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-gray-600">
            <tr>
              <th class="py-2 pl-3 pr-2 text-left font-medium">Staff</th>
              <th class="px-2 py-2 text-left font-medium">Tickets</th>
            </tr>
          </thead>
          <tbody id="ticketsAssignedBody" class="divide-y divide-gray-100">
            @forelse(($ticketsAssigned ?? []) as $agent)
            <tr class="hover:bg-gray-50">
              <td class="py-2 pl-3 pr-2 align-top text-gray-900">{{ $agent['name'] }}</td>
              <td class="px-2 py-2 align-top font-medium text-slate-900">{{ number_format($agent['count']) }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="2" class="px-3 py-6 text-center text-sm text-gray-500">No assigned tickets.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <!-- Workload Distribution -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Workload Distribution</h3>
        <div class="text-sm text-gray-500">Percentage share of open tickets</div>
      </div>
      <div class="h-64">
        <canvas id="workloadDistributionChart"></canvas>
      </div>
    </div>
  </div>

  <!-- Trend Identification and Root Cause Analysis Section -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8 mb-8">
    <!-- Top Ticket Drivers -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Top Ticket Drivers</h3>
        <div class="text-sm text-gray-500">Categories and sub-issues (Last 30 days)</div>
      </div>
      <div class="h-64">
        <canvas id="topTicketDriversChart"></canvas>
      </div>
    </div>

    <!-- Forwarded Tickets (by Forwarder) -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Forwarded Tickets (by Forwarder)</h3>
        <div class="text-sm text-gray-500">Top staff who forwarded tickets (Last 90 days)</div>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-gray-600">
            <tr>
              <th class="py-2 pl-3 pr-2 text-left font-medium">Organization</th>
              <th class="px-2 py-2 text-left font-medium">Tickets</th>
            </tr>
          </thead>
          <tbody id="ticketsByOrgBody" class="divide-y divide-gray-100">
            <!-- Data will be populated by JS -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
  <!-- Forwards modal (admin-style, hidden by default) -->
  <div id="forwardsModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity" data-modal-backdrop></div>
    <!-- Centered panel with modern minimal design (copied from ticket modal for consistent sizing) -->
    <div class="relative mx-auto my-0 sm:my-8 w-full h-full sm:h-auto sm:w-[95%] max-w-2xl flex items-center">
      <div class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-h-[95vh] sm:max-w-2xl overflow-hidden sm:rounded-2xl flex flex-col">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
          <div class="flex-1 min-w-0">
            <h3 class="modal-title text-lg font-semibold text-gray-900">Forwards</h3>
            <div class="text-xs text-gray-500">Breakdown of recipients for forwards by staff</div>
          </div>
          <div class="flex items-center gap-2 ml-4">
            <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg" aria-label="Close" data-modal-close>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
        </div>
        <div class="flex-1 overflow-y-auto px-6 py-5 modal-body text-sm text-gray-800">
          <!-- populated dynamically -->
        </div>
      </div>
    </div>
  </div>
@endsection

@section('admin-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function(){
  let backlogTrendChart, workloadDistributionChart, topTicketDriversChart;

  function updateTicketsSolvedTitle(days) {
    const el = document.getElementById('ticketsSolvedTitle');
    if (!el) return;
    const n = parseInt(days, 10);
    el.textContent = `Tickets Solved (${Number.isFinite(n) ? n : 30} Days)`;
  }

  function number_format(num) {
    return num.toLocaleString();
  }

  function updateTicketsSolvedTable(items) {
    const tbody = document.getElementById('ticketsSolvedBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    if (!Array.isArray(items) || items.length === 0) {
      tbody.innerHTML = '<tr><td colspan="2" class="px-3 py-6 text-center text-sm text-gray-500">No solved tickets.</td></tr>';
      return;
    }

    items.forEach(item => {
      const tr = document.createElement('tr');
      tr.className = 'hover:bg-gray-50';
      tr.innerHTML = `
        <td class="py-2 pl-3 pr-2 align-top text-gray-900">${item.name || 'Unknown'}</td>
        <td class="px-2 py-2 align-top font-medium text-slate-900">${number_format(item.count || 0)}</td>
      `;
      tbody.appendChild(tr);
    });
  }

  function initBacklogTrendChart(data) {
    const ctx = document.getElementById('backlogTrendChart');
    if (!ctx) return;

    if (backlogTrendChart) {
      backlogTrendChart.destroy();
    }

    backlogTrendChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: data.labels,
        datasets: [{
          label: 'Open Tickets',
          data: data.data,
          borderColor: 'rgb(59, 130, 246)',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });
  }


  function initWorkloadDistributionChart(data) {
    const ctx = document.getElementById('workloadDistributionChart');
    if (!ctx) return;

    if (workloadDistributionChart) {
      workloadDistributionChart.destroy();
    }

    const labels = data.map(item => item.name);
    const values = data.map(item => item.percentage);

    const palette = ['#6366F1','#10B981','#F59E0B','#EF4444','#06B6D4','#84CC16','#F472B6','#FB7185'];
    const colors = labels.map((_, i) => palette[i % palette.length]);

    workloadDistributionChart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{
          data: values,
          backgroundColor: colors,
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        },
        cutout: '60%'
      }
    });
  }

  function initTopTicketDriversChart(data) {
    const ctx = document.getElementById('topTicketDriversChart');
    if (!ctx) return;

    if (topTicketDriversChart) {
      topTicketDriversChart.destroy();
    }

    const labels = data.map(item => item.label || 'Unknown');
    const values = data.map(item => item.count || 0);

    const palette = ['#6366F1','#10B981','#F59E0B','#EF4444','#06B6D4','#84CC16','#F472B6','#FB7185'];
    const colors = labels.map((_, i) => palette[i % palette.length]);

    topTicketDriversChart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{
          data: values,
          backgroundColor: colors,
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        },
        cutout: '60%'
      }
    });
  }

  function initTicketsByOrgTable(data) {
    const tbody = document.getElementById('ticketsByOrgBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    if (!data || data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="2" class="px-3 py-6 text-center text-sm text-gray-500">No data available.</td></tr>';
      return;
    }
    data.forEach(item => {
      const tr = document.createElement('tr');
      tr.className = 'hover:bg-gray-50';
      tr.dataset.staffId = item.id || '';
      tr.style.cursor = 'pointer';
      tr.innerHTML = `
        <td class="py-2 pl-3 pr-2 align-top text-gray-900">${item.name || 'Unknown'}</td>
        <td class="px-2 py-2 align-top font-medium text-slate-900">${number_format(item.count || 0)}</td>
      `;
      tr.addEventListener('click', function () {
        const staffId = this.dataset.staffId;
        if (!staffId) return;
        const days = document.getElementById('timeRangeSelect').value || 30;
        fetch(`{{ url('/admin/reports/forwards') }}/${staffId}?days=${days}`)
          .then(r => r.json())
          .then(payload => {
            showForwardsModal(payload);
          }).catch(err => console.error(err));
      });
      tbody.appendChild(tr);
    });
  }

  // Modal for showing forwards breakdown
  function showForwardsModal(payload) {
    let modal = document.getElementById('forwardsModal');
    if (!modal) return;
    const title = modal.querySelector('.modal-title');
    const body = modal.querySelector('.modal-body');
    title.textContent = `Forwards by: ${payload.forwarder || 'Unknown'}`;
    if (!payload.recipients || payload.recipients.length === 0) {
      body.innerHTML = '<p class="text-sm text-gray-500">No forwards found for this staff in the selected period.</p>';
    } else {
      let html = '<div class="space-y-4">';
      payload.recipients.forEach(r => {
        // Build questions dropdown (don't display raw ticket IDs)
        const esc = (s) => s == null ? '' : String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        let questionsHtml = '';
        if (Array.isArray(r.tickets) && r.tickets.length) {
          questionsHtml = `<details class="mt-3 bg-gray-50 rounded-lg p-2">
            <summary class="flex items-center justify-between cursor-pointer text-sm text-gray-700">View questions <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></summary>
            <ul class="mt-2 space-y-2">`;
          r.tickets.forEach(t => {
            const q = esc(t.question || 'No question available');
            questionsHtml += `<li class="text-sm text-gray-700 bg-white px-3 py-2 rounded">${q}</li>`;
          });
          questionsHtml += '</ul></details>';
        } else {
          questionsHtml = '<div class="text-sm text-gray-500 mt-2">No questions available</div>';
        }

        html += `
          <div class="p-4 bg-white shadow-sm rounded-lg">
            <div class="flex items-start justify-between gap-4">
              <div class="min-w-0">
                <div class="text-sm font-medium text-gray-900 truncate">${r.name}</div>
              </div>
              <div class="flex-shrink-0">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">${r.count}</span>
              </div>
            </div>
            ${questionsHtml}
          </div>
        `;
      });
      html += '</div>';
      body.innerHTML = html;
    }
    modal.classList.remove('hidden');
  }

  // Close modal handler
  function closeForwardsModal() {
    const modal = document.getElementById('forwardsModal');
    if (modal) modal.classList.add('hidden');
  }

  // Local modal backdrop / close handling (ensures consistent behavior on this page)
  document.addEventListener('click', function (e) {
    if (!e.target) return;
    if (e.target.closest && (e.target.closest('[data-modal-backdrop]') || e.target.closest('[data-modal-close]'))) {
      const modal = document.getElementById('forwardsModal');
      if (modal) modal.classList.add('hidden');
    }
  });

  function loadBacklogTrendData(days) {
    fetch(`{{ route('admin.reports.backlog-trend-data') }}?days=${days}`)
      .then(response => response.json())
      .then(data => {
        initBacklogTrendChart(data);
      })
      .catch(error => {
        console.error('Error loading chart data:', error);
      });
  }

  function loadDynamicData(days) {
    fetch(`{{ route('admin.reports.dynamic-data') }}?days=${days}`)
      .then(response => response.json())
      .then(data => {
        // Update the Tickets Solved table first so it refreshes even if other widgets error.
        updateTicketsSolvedTable(data.ticketsSolved);

        const avgEl = document.getElementById('avgResolutionTimeValue');
        if (avgEl) avgEl.textContent = data.avgResolutionTime;

        const totalEl = document.getElementById('totalTicketsValue');
        if (totalEl) totalEl.textContent = data.totalTickets;

        // update other widgets
        initTicketsByOrgTable(data.ticketsByOrg);
        initTopTicketDriversChart(data.topTicketDrivers);
      })
      .catch(error => {
        console.error('Error loading dynamic data:', error);
      });
  }

  // Initialize with default data
  const initialBacklogData = @json($backlogTrendData);
  initBacklogTrendChart(initialBacklogData);


  const initialWorkloadData = @json($workloadDistribution ?? []);
  initWorkloadDistributionChart(initialWorkloadData);

  const initialTopTicketDriversData = @json($topTicketDrivers ?? []);
  initTopTicketDriversChart(initialTopTicketDriversData);

  const initialTicketsByOrgData = @json($ticketsByOrg ?? []);
  initTicketsByOrgTable(initialTicketsByOrgData);

  // Sync the Tickets Solved title with the current range (default is 30)
  updateTicketsSolvedTitle(document.getElementById('timeRangeSelect').value || 30);

  // Handle time range changes
  document.getElementById('timeRangeSelect').addEventListener('change', function(e) {
    const days = e.target.value;
    loadBacklogTrendData(days);
    loadDynamicData(days);
    updateTicketsSolvedTitle(days);
  });
})();
</script>
@endsection
