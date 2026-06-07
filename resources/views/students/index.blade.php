@extends('layouts.app')

@section('title', 'Students - Cambridge Exam Portal')
@section('page-title', 'Students Catalog')

@section('content')
<div class="space-y-6">
    <!-- Filters & Action Bar -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-3">
                <!-- Search Input -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" id="search-input" name="search" value="{{ $search }}" placeholder="Search name or cand no..."
                           class="block w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white text-sm transition">
                </div>

                <!-- Qualification Dropdown -->
                <div>
                    <select id="qual-select" name="qualification_id" class="block w-full px-3 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white text-sm transition">
                        <option value="">All Qualifications</option>
                        @foreach($qualifications as $qual)
                            <option value="{{ $qual->id }}" {{ $qualId == $qual->id ? 'selected' : '' }}>{{ $qual->qualification_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Year Dropdown -->
                <div>
                    <select id="year-select" name="year" class="block w-full px-3 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white text-sm transition">
                        <option value="">All Years</option>
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Series Dropdown -->
                <div>
                    <select id="series-select" name="series_id" class="block w-full px-3 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white text-sm transition">
                        <option value="">All Series</option>
                        @foreach($seriesList as $s)
                            <option value="{{ $s->id }}" {{ $seriesId == $s->id ? 'selected' : '' }}>{{ $s->series_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Clear link -->
            <div class="flex items-center gap-3 shrink-0 justify-end md:justify-start">
                <a href="#" id="clear-filters" class="text-sm font-semibold text-slate-500 hover:text-indigo-600 transition">
                    Clear Filters
                </a>
            </div>
        </div>
    </div>

    <!-- Student Table Container -->
    <div id="students-table-container">
        @include('students._table')
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const qualSelect = document.getElementById('qual-select');
    const yearSelect = document.getElementById('year-select');
    const seriesSelect = document.getElementById('series-select');
    const clearFilters = document.getElementById('clear-filters');
    const tableContainer = document.getElementById('students-table-container');

    let debounceTimer;

    function fetchFilteredCandidates() {
        const queryParams = new URLSearchParams({
            search: searchInput.value,
            qualification_id: qualSelect.value,
            year: yearSelect.value,
            series_id: seriesSelect.value
        });

        // Set loading state
        tableContainer.style.opacity = '0.5';

        fetch('{{ route("students.index") }}?' + queryParams.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            tableContainer.innerHTML = html;
            tableContainer.style.opacity = '1';
            bindPaginationLinks();
        })
        .catch(error => {
            console.error('Error fetching filtered candidates:', error);
            tableContainer.style.opacity = '1';
        });
    }

    function debounce(func, delay) {
        return function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(func, delay);
        };
    }

    // Input listeners
    searchInput.addEventListener('input', debounce(fetchFilteredCandidates, 300));
    qualSelect.addEventListener('change', fetchFilteredCandidates);
    yearSelect.addEventListener('change', fetchFilteredCandidates);
    seriesSelect.addEventListener('change', fetchFilteredCandidates);

    // Clear filters
    clearFilters.addEventListener('click', function(e) {
        e.preventDefault();
        searchInput.value = '';
        qualSelect.value = '';
        yearSelect.value = '';
        seriesSelect.value = '';
        fetchFilteredCandidates();
    });

    // Handle AJAX pagination links
    function bindPaginationLinks() {
        tableContainer.querySelectorAll('a.page-link, .pagination a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                if (!url) return;

                tableContainer.style.opacity = '0.5';
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    tableContainer.innerHTML = html;
                    tableContainer.style.opacity = '1';
                    bindPaginationLinks();
                    tableContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                })
                .catch(error => {
                    console.error('Error paginating:', error);
                    tableContainer.style.opacity = '1';
                });
            });
        });
    }

    // Initial binding
    bindPaginationLinks();
});
</script>
@endsection
