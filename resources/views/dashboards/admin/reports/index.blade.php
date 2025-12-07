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
          <p class="text-3xl font-bold text-gray-900" id="currentOpenTickets">{{ $currentOpenTickets }}</p>
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
          <p class="text-3xl font-bold text-gray-900">-</p>
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
          <p class="text-3xl font-bold text-gray-900">-</p>
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
          <p class="text-sm font-medium text-gray-600">Satisfaction Rate</p>
          <p class="text-3xl font-bold text-gray-900">-</p>
        </div>
        <div class="p-3 bg-yellow-50 rounded-lg">
          <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
          </svg>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts Section -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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

    <!-- Placeholder for future charts -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Category Distribution</h3>
        <div class="text-sm text-gray-500">Tickets by category</div>
      </div>
      <div class="h-64 flex items-center justify-center text-gray-400">
        <div class="text-center">
          <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
          <p>Coming Soon</p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('admin-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function(){
  let backlogTrendChart;

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

  // Initialize with default data
  const initialData = @json($backlogTrendData);
  initBacklogTrendChart(initialData);

  // Handle time range changes
  document.getElementById('timeRangeSelect').addEventListener('change', function(e) {
    const days = e.target.value;
    loadBacklogTrendData(days);
  });
})();
</script>
@endsection