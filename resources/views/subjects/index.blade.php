@extends('layouts.app')

@section('title', 'Subjects')
@section('page-title', 'Subjects')

@section('content')
<div class="max-w-6xl mx-auto space-y-5">
    {{-- Filter Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3 flex-1">
            <select id="filter-qual-select" onchange="applyFilter()" class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 transition">
                <option value="all">All Qualifications</option>
                @foreach($qualifications as $qual)
                    <option value="{{ $qual['id'] }}">{{ $qual['name'] }}</option>
                @endforeach
            </select>
            <div class="relative flex-1 max-w-xs">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="filter-search" oninput="applyFilter()" placeholder="Search..." class="w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 transition" />
            </div>
            <span id="count-badge" class="text-xs text-slate-400 font-medium whitespace-nowrap"></span>
        </div>
        <a href="{{ route('subjects.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold rounded-lg transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add Subject
        </a>
    </div>

    {{-- Subjects Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b border-slate-100 text-xs text-slate-500 font-medium uppercase tracking-wide">
                    <th class="px-5 py-3">Code</th>
                    <th class="px-5 py-3">Subject Name</th>
                    <th class="px-5 py-3">Qualification</th>
                    <th class="px-5 py-3">Components</th>
                    <th class="px-5 py-3 text-right">Total Marks</th>
                    <th class="px-5 py-3 text-right w-20"></th>
                </tr>
            </thead>
            <tbody id="subjects-tbody" class="divide-y divide-slate-50">
                @php
                    $allSubjects = [];
                    foreach($qualifications as $qual) {
                        foreach($qual['subjects_with_stats'] as $subj) {
                            $allSubjects[] = [
                                'id' => $subj['id'],
                                'code' => $subj['code'],
                                'name' => $subj['name'],
                                'qual_id' => $qual['id'],
                                'qual_name' => $qual['name'],
                                'qual_type' => $qual['type'],
                                'components' => $subj['components'],
                                'total_marks' => $subj['components']->sum('total_marks'),
                            ];
                        }
                    }
                    $allSubjects = collect($allSubjects)->sortBy('name')->values()->all();
                @endphp

                @forelse($allSubjects as $s)
                    <tr class="subject-row group hover:bg-slate-50/60 transition-colors cursor-pointer"
                        data-qual-id="{{ $s['qual_id'] }}"
                        data-search="{{ strtolower($s['code'] . ' ' . $s['name']) }}"
                        onclick="window.location.href='{{ route('subjects.edit', $s['id']) }}'">
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-sm font-semibold text-slate-700">{{ $s['code'] }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="text-sm font-semibold text-slate-800">{{ $s['name'] }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="text-xs text-slate-500">{{ $s['qual_type'] }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($s['components']->sortBy('component_code') as $comp)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-slate-100 text-slate-600 text-xs font-medium rounded-md" title="{{ $comp->component_name }}">
                                        {{ $comp->component_code }}
                                        <span class="text-slate-400 font-normal">/ {{ $comp->total_marks }}</span>
                                    </span>
                                @endforeach
                                @if($s['components']->isEmpty())
                                    <span class="text-xs text-slate-300 italic">No components</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <span class="text-sm font-semibold text-slate-700">{{ $s['total_marks'] }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-right" onclick="event.stopPropagation()">
                            <form action="{{ route('subjects.destroy', $s['id']) }}" method="POST" onsubmit="return confirm('Delete {{ $s['name'] }} ({{ $s['code'] }}) and all its components?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="opacity-0 group-hover:opacity-100 text-slate-400 hover:text-rose-500 transition p-1" title="Delete">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr id="empty-row">
                        <td colspan="6" class="px-5 py-12 text-center text-slate-400 text-sm">
                            No subjects registered yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => applyFilter());

function applyFilter() {
    const qualVal = document.getElementById('filter-qual-select').value;
    const search = document.getElementById('filter-search').value.toLowerCase().trim();
    const rows = document.querySelectorAll('.subject-row');
    let visible = 0;

    rows.forEach(row => {
        const matchQual = qualVal === 'all' || row.dataset.qualId === qualVal;
        const matchSearch = !search || row.dataset.search.includes(search);
        row.classList.toggle('hidden', !(matchQual && matchSearch));
        if (matchQual && matchSearch) visible++;
    });

    document.getElementById('count-badge').textContent = `${visible} of ${rows.length}`;
}
</script>
@endsection
