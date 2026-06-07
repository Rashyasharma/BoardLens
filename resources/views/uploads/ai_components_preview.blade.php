@extends('layouts.app')

@section('title', 'Provisional Component Marks Mapping Preview - Lucky International School')
@section('page-title', 'AI Provisional Component Marks Preview')

@section('content')
<style>
    .vertical-header-cell {
        height: 180px;
        vertical-align: bottom;
        padding-bottom: 15px !important;
        text-align: center;
        width: 60px;
        min-width: 60px;
    }
    .vertical-header-text {
        writing-mode: vertical-rl;
        transform: rotate(180deg);
        white-space: nowrap;
        text-align: left;
        display: inline-block;
        margin: 0 auto;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #475569;
    }
</style>

<div class="max-w-7xl mx-auto space-y-6">
    <form method="POST" action="{{ route('uploads.ai_components.confirm') }}">
        @csrf
        <input type="hidden" name="session_key" value="{{ $sessionKey }}" />

        {{-- Header Options Bar --}}
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-150 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800 tracking-tight">Preview & Validate Component Marks</h2>
                <p class="text-slate-500 text-xs mt-1">
                    Map sheets to DB Subjects and components, verify candidate matches, and manually review before confirming import.
                </p>
            </div>
            <div class="flex items-center gap-3 self-start md:self-center">
                <span class="px-4 py-2 bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-bold rounded-xl whitespace-nowrap">
                    Series: {{ $series->series_name }}
                </span>
                <span class="px-4 py-2 bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl whitespace-nowrap">
                    Qualification: {{ $qualification->qualification_name }}
                </span>
                <a href="{{ route('uploads.ai_components') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-655 text-xs font-bold rounded-xl transition border border-slate-250">
                    Cancel
                </a>
            </div>
        </div>

        {{-- Tabs Navigation Bar --}}
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden p-2 flex flex-wrap gap-1">
            @foreach($sheetsOrder as $index => $subCode)
                @php
                    $sub = $parsedData[$subCode];
                    $isActive = $index === 0;
                @endphp
                <button type="button" 
                        onclick="switchTab('{{ $subCode }}')" 
                        id="tab-btn-{{ $subCode }}"
                        class="px-4 py-3 rounded-2xl text-xs font-bold tracking-wider transition-all duration-200 flex items-center gap-2 tab-btn {{ $isActive ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span class="font-mono text-[10px] px-1.5 py-0.5 rounded {{ $isActive ? 'bg-indigo-700 text-white' : 'bg-slate-100 text-slate-500' }}">
                        {{ $subCode }}
                    </span>
                    <span class="truncate max-w-[120px]">{{ $sub['subject_name'] }}</span>
                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded-full {{ $isActive ? 'bg-indigo-500 text-indigo-100' : 'bg-slate-100 text-slate-400' }}">
                        {{ count($sub['candidates']) }}
                    </span>
                </button>
            @endforeach
        </div>

        {{-- Content Sheets --}}
        @foreach($sheetsOrder as $index => $subCode)
            @php
                $sub = $parsedData[$subCode];
                $isActive = $index === 0;
                
                // Map automatically using sheet code match
                $matchedSubject = $dbSubjects->first(fn($s) => $s->subject_code === $subCode);
                $selectedSubId = $matchedSubject ? $matchedSubject->id : ($sub['subject_id'] ?? '');

                $totalCandidates = count($sub['candidates']);
                $readyCount = collect($sub['candidates'])->where('status', 'Ready to import')->count();
                $warningCount = collect($sub['candidates'])->where('status', 'No Grade Uploaded')->count();
                $newCount = collect($sub['candidates'])->where('status', 'New Candidate')->count();
            @endphp
            <div id="tab-content-{{ $subCode }}" class="tab-content space-y-6 {{ $isActive ? '' : 'hidden' }}">
                
                {{-- Mappings & Configuration Panel --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Subject Mapping --}}
                    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                        <div>
                            <span class="text-[10px] font-extrabold text-indigo-700 uppercase bg-indigo-50 border border-indigo-150 px-2 py-0.5 rounded">Syllabus Sheet Mapping</span>
                            <h3 class="text-sm font-bold text-slate-800 mt-2">Map Excel Tab '{{ $subCode }}' to DB Subject:</h3>
                        </div>
                        <div>
                            <select name="subject_mappings[{{ $subCode }}]" 
                                    id="subject-select-{{ $subCode }}"
                                    onchange="updateComponentDropdowns('{{ $subCode }}'); renderTabStyles();"
                                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-xs font-bold text-slate-700">
                                <option value="">-- Ignore Sheet --</option>
                                @foreach($dbSubjects as $dbSub)
                                    <option value="{{ $dbSub->id }}" data-components='@json($dbSub->components)' {{ $selectedSubId == $dbSub->id ? 'selected' : '' }}>
                                        {{ $dbSub->subject_code }} — {{ $dbSub->subject_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Components Mappings --}}
                    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm lg:col-span-2 space-y-4">
                        <div>
                            <span class="text-[10px] font-extrabold text-emerald-700 uppercase bg-emerald-50 border border-emerald-150 px-2 py-0.5 rounded">Component Marks Mappings</span>
                            <h3 class="text-sm font-bold text-slate-800 mt-2">Map Detected Components to Database Papers:</h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4" id="component-mappings-container-{{ $subCode }}">
                            @foreach($sub['components'] as $comp)
                                @php
                                     // Heuristic auto-match: find components for subject and select one with same Paper digit
                                     $firstDigit = substr($comp['code'], 0, 1);
                                     $paperDigit = ($firstDigit === '0' && strlen($comp['code']) > 1) ? substr($comp['code'], 1, 1) : $firstDigit;
                                     $matchedDbComp = null;
                                     if ($matchedSubject) {
                                         $matchedDbComp = $matchedSubject->components->first(function($dc) use ($paperDigit) {
                                             return str_contains($dc->component_code, $paperDigit) || str_contains($dc->component_name, $paperDigit);
                                         });
                                     }
                                @endphp
                                <div class="bg-slate-50 border border-slate-150 p-3 rounded-2xl flex flex-col justify-between gap-2.5">
                                    <div class="flex items-center justify-between">
                                        <span class="font-mono text-xs font-extrabold text-slate-750 bg-white border border-slate-200 px-2 py-0.5 rounded">
                                            {{ $comp['code'] }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-semibold">{{ $comp['name'] }}</span>
                                    </div>
                                    
                                    <select name="component_mappings[{{ $subCode }}][{{ $comp['code'] }}]" 
                                            data-comp-code="{{ $comp['code'] }}"
                                            data-paper-digit="{{ $paperDigit }}"
                                            onchange="renderTabStyles()"
                                            class="w-full px-2 py-1.5 bg-white border border-slate-200 rounded-lg focus:outline-none text-[10px] font-bold text-slate-600 component-dropdown">
                                        <option value="">-- Ignore Component --</option>
                                        @if($matchedSubject)
                                            @foreach($matchedSubject->components as $dc)
                                                <option value="{{ $dc->id }}" {{ ($matchedDbComp && $matchedDbComp->id == $dc->id) ? 'selected' : '' }}>
                                                    {{ $dc->component_code }} ({{ $dc->total_marks }}m)
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Table Card --}}
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Candidate Score Details</h3>
                        <span class="text-xs text-slate-450 font-semibold font-mono">{{ $totalCandidates }} total records</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-150 text-slate-450 text-[10px] font-bold uppercase tracking-wider bg-slate-50/20">
                                    <th class="py-3.5 px-6">Candidate</th>
                                    <th class="py-3.5 px-6 text-center">Option</th>
                                    @foreach($sub['components'] as $comp)
                                        <th class="vertical-header-cell">
                                            <span class="vertical-header-text">
                                                {{ $comp['name'] }}
                                            </span>
                                        </th>
                                    @endforeach
                                    <th class="py-3.5 px-6 text-center">Verification Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                @forelse($sub['candidates'] as $c)
                                    @php
                                        $rowBg = $c['status'] === 'Ready to import' ? 'hover:bg-slate-55' : 'bg-slate-50/50 text-slate-400';
                                    @endphp
                                    <tr class="{{ $rowBg }} transition duration-150">
                                        <td class="py-4 px-6">
                                            <div class="font-bold text-slate-800 text-sm {{ $c['status'] !== 'Ready to import' ? 'text-slate-400 line-through' : '' }}">
                                                {{ $c['candidate_name'] }}
                                            </div>
                                            <div class="text-slate-400 font-mono mt-0.5">{{ $c['candidate_number'] }}</div>
                                        </td>
                                        <td class="py-4 px-6 text-center font-bold text-slate-500 font-mono">
                                            {{ $c['option_code'] }}
                                        </td>
                                        
                                        @foreach($sub['components'] as $comp)
                                            @php
                                                $m = $c['marks'][$comp['code']] ?? null;
                                            @endphp
                                            <td class="py-4 px-4 text-center">
                                                @if($m && $m['obtained_marks'] !== null)
                                                    <div class="font-extrabold text-sm font-mono {{ $c['status'] === 'Ready to import' ? 'text-slate-800' : 'text-slate-400' }}">
                                                        {{ round($m['obtained_marks']) }}
                                                    </div>
                                                @else
                                                    <span class="text-slate-300 font-mono">—</span>
                                                @endif
                                            </td>
                                        @endforeach

                                        <td class="py-4 px-6 text-center">
                                            @if($c['status'] === 'Ready to import')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 border border-emerald-100 text-emerald-700">
                                                    ✓ Ready to Import
                                                </span>
                                            @elseif($c['status'] === 'No Grade Uploaded')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-amber-50 border border-amber-200 text-amber-600 hover:cursor-help" title="{{ $c['error_message'] }} (Skipped)">
                                                    ⚠️ Result Missing (Skip)
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-rose-50 border border-rose-200 text-rose-600 hover:cursor-help" title="{{ $c['error_message'] }} (Skipped)">
                                                    ✕ New Candidate (Skip)
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 3 + count($sub['components']) }}" class="py-8 text-center text-slate-400 italic">
                                            No candidate marks loaded for this syllabus sheet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Confirm Form Box --}}
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-150 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs text-slate-500 font-medium">
                <span class="text-amber-600 font-bold block mb-1">⚠️ Crucial Warning Notice</span>
                Only existing candidates with uploaded syllabus results (Grade + PUM) will be parsed and imported. New candidates or missing result rows are automatically skipped to guarantee data integrity.
            </div>
            <button type="submit" class="w-full sm:w-auto px-8 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-2xl transition duration-150 shadow-lg shadow-indigo-600/20 flex items-center justify-center gap-2">
                <span>✓</span> Confirm & Import Component Marks
            </button>
        </div>
    </form>
</div>

<script>
    let activeTabCode = '{{ $sheetsOrder[0] }}';

    function switchTab(subCode) {
        activeTabCode = subCode;
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        const activeContent = document.getElementById(`tab-content-${subCode}`);
        if (activeContent) activeContent.classList.remove('hidden');

        renderTabStyles();
    }

    function renderTabStyles() {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            const subCode = btn.id.replace('tab-btn-', '');
            const isActive = (subCode === activeTabCode);

            const container = document.getElementById(`component-mappings-container-${subCode}`);
            const subjectSelect = document.getElementById(`subject-select-${subCode}`);
            let hasUnmapped = false;

            if (subjectSelect && subjectSelect.value !== "") {
                const dropdowns = container ? container.querySelectorAll('.component-dropdown') : [];
                dropdowns.forEach(dd => {
                    if (dd.value === "") {
                        hasUnmapped = true;
                    }
                });
            }

            // Reset classes
            btn.className = 'px-4 py-3 rounded-2xl text-xs font-bold tracking-wider transition-all duration-200 flex items-center gap-2 tab-btn ';
            const badge = btn.querySelector('.font-mono');
            const countBadge = btn.querySelector('.text-\\[10px\\]:not(.font-mono)');

            if (isActive) {
                if (hasUnmapped) {
                    btn.className += 'bg-rose-600 text-white shadow-md shadow-rose-600/10';
                    if (badge) badge.className = 'font-mono text-[10px] px-1.5 py-0.5 rounded bg-rose-700 text-white';
                    if (countBadge) countBadge.className = 'text-[10px] font-mono px-1.5 py-0.5 rounded-full bg-rose-500 text-rose-100';
                } else {
                    btn.className += 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10';
                    if (badge) badge.className = 'font-mono text-[10px] px-1.5 py-0.5 rounded bg-indigo-700 text-white';
                    if (countBadge) countBadge.className = 'text-[10px] font-mono px-1.5 py-0.5 rounded-full bg-indigo-500 text-indigo-100';
                }
            } else {
                if (hasUnmapped) {
                    btn.className += 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100';
                    if (badge) badge.className = 'font-mono text-[10px] px-1.5 py-0.5 rounded bg-rose-100 text-rose-800';
                    if (countBadge) countBadge.className = 'text-[10px] font-mono px-1.5 py-0.5 rounded-full bg-rose-200 text-rose-700';
                } else {
                    btn.className += 'text-slate-600 hover:bg-slate-50 border border-transparent';
                    if (badge) badge.className = 'font-mono text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-500';
                    if (countBadge) countBadge.className = 'text-[10px] font-mono px-1.5 py-0.5 rounded-full bg-slate-100 text-slate-400';
                }
            }
        });
    }

    function updateComponentDropdowns(subCode) {
        const subjectSelect = document.getElementById(`subject-select-${subCode}`);
        const selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
        
        const container = document.getElementById(`component-mappings-container-${subCode}`);
        const dropdowns = container ? container.querySelectorAll('.component-dropdown') : [];

        if (!selectedOption || !selectedOption.value) {
            dropdowns.forEach(dd => {
                dd.innerHTML = '<option value="">-- Ignore Component --</option>';
            });
            return;
        }

        const components = JSON.parse(selectedOption.getAttribute('data-components') || '[]');

        dropdowns.forEach(dd => {
            const rawCompCode = dd.getAttribute('data-comp-code');
            const paperDigit = dd.getAttribute('data-paper-digit');
            
            let html = '<option value="">-- Ignore Component --</option>';
            
            components.forEach(c => {
                const isMatched = c.component_code.includes(paperDigit) || c.component_name.includes(paperDigit);
                html += `<option value="${c.id}" ${isMatched ? 'selected' : ''}>${c.component_code} (${c.total_marks}m)</option>`;
            });

            dd.innerHTML = html;
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        renderTabStyles();
    });
</script>
@endsection
