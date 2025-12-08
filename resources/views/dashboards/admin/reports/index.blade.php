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
        <h3 class="text-lg font-semibold text-gray-900">Tickets Solved (30 Days)</h3>
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

    <!-- Tickets by Customer/Org -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Tickets by Customer/Org</h3>
        <div class="text-sm text-gray-500">Top 10 organizations (Last 90 days)</div>
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
@endsection

@section('admin-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function(){
  let backlogTrendChart, workloadDistributionChart, topTicketDriversChart;

  function number_format(num) {
    return num.toLocaleString();
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
    tbody.innerHTML = '';
    if (!data || data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="2" class="px-3 py-6 text-center text-sm text-gray-500">No data available.</td></tr>';
      return;
    }
    data.forEach(item => {
      const tr = document.createElement('tr');
      tr.className = 'hover:bg-gray-50';
      tr.innerHTML = `
        <td class="py-2 pl-3 pr-2 align-top text-gray-900">${item.name || 'Unknown'}</td>
        <td class="px-2 py-2 align-top font-medium text-slate-900">${number_format(item.count || 0)}</td>
      `;
      tbody.appendChild(tr);
    });
  }

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
        document.getElementById('avgResolutionTimeValue').textContent = data.avgResolutionTime;
        document.getElementById('totalTicketsValue').textContent = data.totalTickets;
        // update tables
        initTicketsByOrgTable(data.ticketsByOrg);
        initTopTicketDriversChart(data.topTicketDrivers);
        // for tickets solved table
        const tbody = document.getElementById('ticketsSolvedBody');
        tbody.innerHTML = '';
        if (!data.ticketsSolved || data.ticketsSolved.length === 0) {
          tbody.innerHTML = '<tr><td colspan="2" class="px-3 py-6 text-center text-sm text-gray-500">No solved tickets.</td></tr>';
          return;
        }
        data.ticketsSolved.forEach(item => {
          const tr = document.createElement('tr');
          tr.className = 'hover:bg-gray-50';
          tr.innerHTML = `
            <td class="py-2 pl-3 pr-2 align-top text-gray-900">${item.name || 'Unknown'}</td>
            <td class="px-2 py-2 align-top font-medium text-slate-900">${number_format(item.count || 0)}</td>
          `;
          tbody.appendChild(tr);
        });
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

  // Handle time range changes
  document.getElementById('timeRangeSelect').addEventListener('change', function(e) {
    const days = e.target.value;
    loadBacklogTrendData(days);
    loadDynamicData(days);
  });
})();
</script>
@endsection