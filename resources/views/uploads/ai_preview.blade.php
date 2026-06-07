@extends('layouts.app')

@section('title', 'AI Import Preview - Lucky International School')
@section('page-title', 'AI Import Preview')

@section('content')
<form method="POST" action="{{ route('uploads.ai_importer.confirm') }}" class="space-y-6 max-w-full">
    @csrf
    <input type="hidden" name="session_key" value="{{ $sessionKey }}">

    {{-- Header Banner --}}
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h3 class="text-lg font-bold text-slate-800 tracking-tight">Verify Extracted Data</h3>
            <p class="text-sm text-slate-500 mt-1">Review the AI-mapped subjects, candidate profiles, and grades before persisting to the database.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('uploads.ai_importer') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl border border-slate-200 transition flex items-center">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-md transition flex items-center gap-2">
                <span>💾</span> Confirm & Save Import
            </button>
        </div>
    </div>

    @if(!empty($importErrors))
        <div class="bg-rose-50 border border-rose-150 p-4 rounded-2xl text-rose-800 text-sm">
            <h4 class="font-bold mb-1">Some warnings/errors occurred:</h4>
            <ul class="list-disc pl-5 space-y-1">
                @foreach($importErrors as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @foreach($filesData as $fIdx => $fileData)
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden space-y-6 p-6">
            {{-- File Title Bar --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-4 gap-2">
                <div>
                    <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">File #{{ $fIdx + 1 }}</span>
                    <h4 class="text-base font-bold text-slate-800">{{ $fileData['file_name'] }}</h4>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="px-2.5 py-1 rounded-lg font-bold bg-slate-150 text-slate-700">
                        Series: {{ $fileData['series_name'] }}
                    </span>
                    <span class="px-2.5 py-1 rounded-lg font-bold bg-slate-150 text-slate-700">
                        Qual: {{ $fileData['qualification_name'] }}
                    </span>
                    @if($fileData['ai_used'])
                        <span class="px-2.5 py-1 rounded-lg font-bold bg-emerald-100 text-emerald-800 flex items-center gap-1">
                            ✨ AI ({{ $fileData['model_name'] }})
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-lg font-bold bg-amber-100 text-amber-800">
                            ⚙️ Local Fallback
                        </span>
                    @endif
                </div>
            </div>

            {{-- AI Audit Warning Block --}}
            @if(!empty($fileData['ai_audit']) && $fileData['ai_audit']['mismatch_found'])
                <div class="bg-amber-50 border border-amber-200 p-4 rounded-2xl text-amber-800 text-sm">
                    <h4 class="font-bold flex items-center gap-1">⚠️ AI Verification Warning:</h4>
                    <p class="mt-1">{{ $fileData['ai_audit']['mismatch_details'] }}</p>
                    @if(!empty($fileData['ai_audit']['missing_candidate_numbers']))
                        <p class="mt-2 font-semibold">Missing Candidate Numbers: {{ implode(', ', $fileData['ai_audit']['missing_candidate_numbers']) }}</p>
                    @endif
                </div>
            @endif

            {{-- Stat Cards --}}
            @php
                $stats = $fileData['comparison']['stats'];
            @endphp
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <div class="text-xs font-bold text-slate-400 uppercase">Total Results</div>
                    <div class="text-xl font-bold text-slate-800 mt-1">{{ $stats['total_parsed'] }}</div>
                </div>
                <div class="bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100">
                    <div class="text-xs font-bold text-emerald-600 uppercase">New Candidates</div>
                    <div class="text-xl font-bold text-emerald-800 mt-1">{{ $stats['new_candidates'] }}</div>
                </div>
                <div class="bg-indigo-50/50 p-4 rounded-2xl border border-indigo-100">
                    <div class="text-xs font-bold text-indigo-600 uppercase">New Results</div>
                    <div class="text-xl font-bold text-indigo-800 mt-1">{{ $stats['new_results'] }}</div>
                </div>
                <div class="bg-amber-50/50 p-4 rounded-2xl border border-amber-100">
                    <div class="text-xs font-bold text-amber-600 uppercase">Updated Results</div>
                    <div class="text-xl font-bold text-amber-800 mt-1">{{ $stats['updated_results'] }}</div>
                </div>
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <div class="text-xs font-bold text-slate-400 uppercase">No Change</div>
                    <div class="text-xl font-bold text-slate-700 mt-1">{{ $stats['no_change_results'] }}</div>
                </div>
            </div>

            {{-- Subject Mapping Dropdowns --}}
            <div class="space-y-2">
                <h5 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Header Column Manual Overrides</h5>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($fileData['subjects'] as $col => $subData)
                        @php
                            $isMapped = $dbSubjects->contains('subject_code', $subData['subject_code']);
                        @endphp
                        <div class="bg-slate-50/70 p-4 rounded-2xl border {{ $isMapped ? 'border-slate-150' : 'border-rose-300 bg-rose-50/10' }} flex flex-col justify-between space-y-2">
                            <div>
                                <span class="block text-[10px] font-mono font-bold text-slate-400">Col / Identifer: {{ $col }}</span>
                                <span class="block text-xs font-bold text-slate-800 truncate" title="{{ $subData['header_name'] }}">{{ $subData['header_name'] }}</span>
                            </div>
                            
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">Map To Database Subject:</label>
                                <select name="mappings[{{ $fIdx }}][{{ $col }}]" class="w-full bg-white border {{ $isMapped ? 'border-slate-200' : 'border-rose-400' }} rounded-xl px-2 py-1.5 text-xs font-semibold text-slate-700 focus:ring-1 focus:ring-indigo-500">
                                    <option value="">-- Unmapped / Select --</option>
                                    <optgroup label="IGCSE">
                                        @foreach($dbSubjects->where('qualification.qualification_type', 'IGCSE') as $sub)
                                            <option value="{{ $sub->subject_code }}" {{ $subData['subject_code'] == $sub->subject_code ? 'selected' : '' }}>
                                                {{ $sub->subject_code }} - {{ $sub->subject_name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="AS & A Level">
                                        @foreach($dbSubjects->where('qualification.qualification_type', 'AS_A_LEVEL') as $sub)
                                            <option value="{{ $sub->subject_code }}" {{ $subData['subject_code'] == $sub->subject_code ? 'selected' : '' }}>
                                                {{ $sub->subject_code }} - {{ $sub->subject_name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Candidates Comparison Details --}}
            <div class="space-y-2">
                <h5 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Candidate Row Review</h5>
                <div class="border border-slate-200 rounded-2xl overflow-hidden overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 font-bold uppercase border-b border-slate-200">
                                <th class="py-3 px-4 w-28">No.</th>
                                <th class="py-3 px-4 w-60">Candidate Name</th>
                                @foreach($fileData['subjects'] as $col => $subData)
                                    <th class="py-3 px-4 text-center min-w-[7rem] border-l border-slate-100">{{ $subData['subject_code'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($fileData['comparison']['candidates'] as $cand)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-3 px-4 font-mono font-bold text-slate-700">
                                        {{ $cand['candidate_number'] }}
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-slate-800">
                                        <div class="flex items-center gap-2">
                                            <span class="uppercase">{{ $cand['candidate_name'] }}</span>
                                            @if($cand['status'] === 'new')
                                                <span class="px-1.5 py-0.5 bg-emerald-100 text-emerald-800 text-[9px] font-extrabold rounded">NEW CANDIDATE</span>
                                            @endif
                                        </div>
                                    </td>
                                    @foreach($fileData['subjects'] as $col => $subData)
                                        @php
                                            $res = $cand['results'][$subData['subject_code']] ?? null;
                                        @endphp
                                        <td class="py-3 px-4 text-center border-l border-slate-50">
                                            @if($res)
                                                @if($res['status'] === 'new')
                                                    <span class="inline-block px-2 py-1 bg-indigo-50 border border-indigo-100 text-indigo-700 font-extrabold rounded text-[11px]" title="New result to be inserted">
                                                        {{ $res['grade'] }} ({{ $res['pum'] }})
                                                        <span class="block text-[8px] text-indigo-500 font-normal">New Result</span>
                                                    </span>
                                                @elseif($res['status'] === 'update')
                                                    <span class="inline-block px-2 py-1 bg-amber-50 border border-amber-100 text-amber-700 font-extrabold rounded text-[11px]" title="Values will be updated">
                                                        <span class="block text-[9px] line-through text-slate-400 font-normal">{{ $res['db_grade'] }} ({{ $res['db_pum'] }})</span>
                                                        <span class="text-emerald-600">➔ {{ $res['grade'] }} ({{ $res['pum'] }})</span>
                                                        <span class="block text-[8px] text-amber-500 font-normal">Update</span>
                                                    </span>
                                                @elseif($res['status'] === 'no_change')
                                                    <span class="text-slate-500 font-semibold" title="Matches database value exactly">
                                                        {{ $res['grade'] }} ({{ $res['pum'] }})
                                                        <span class="block text-[8px] text-slate-400 font-normal">No Change</span>
                                                    </span>
                                                @elseif($res['status'] === 'error')
                                                    <span class="text-rose-500 font-bold" title="{{ $res['error_message'] }}">
                                                        ⚠ Error
                                                        <span class="block text-[8px] font-normal">{{ $res['error_message'] }}</span>
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-slate-300">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Bottom Floating Actions Bar --}}
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex items-center justify-between">
        <a href="{{ route('uploads.ai_importer') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl border border-slate-200 transition">
            Cancel and Back
        </a>
        <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-600/20 transition flex items-center gap-2">
            <span>✨</span> Import Mapped Data
        </button>
    </div>
</form>
@endsection
