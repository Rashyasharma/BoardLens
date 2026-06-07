@extends('layouts.app')

@section('title', 'System Settings')
@section('page-title', 'System Settings & Configuration')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto animate-fade-in">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Qualifications & Series -->
        <div class="space-y-6 lg:col-span-1">
            <!-- Qualifications Config Card -->
            <div class="bg-white rounded-2xl border border-slate-150 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Qualifications</h3>
                    <span class="text-xs bg-slate-100 border border-slate-200 text-slate-600 px-2 py-0.5 rounded font-bold">{{ $qualifications->count() }} total</span>
                </div>
                <div class="p-4 divide-y divide-slate-100">
                    @forelse($qualifications as $qual)
                        <div class="py-3 flex justify-between items-center text-sm">
                            <div>
                                <span class="font-bold text-slate-800">{{ $qual->qualification_name }}</span>
                                <span class="text-xxs text-slate-400 block mt-0.5 font-semibold">Type: {{ $qual->type_display }}</span>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xxs font-extrabold {{ $qual->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-slate-100 text-slate-455 border border-slate-200' }}">
                                {{ $qual->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    @empty
                        <p class="p-4 text-center text-slate-400 text-xs">No qualifications configured.</p>
                    @endforelse
                </div>
            </div>

            <!-- Exam Series Card -->
            <div class="bg-white rounded-2xl border border-slate-150 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Exam Series</h3>
                    <span class="text-xs bg-slate-100 border border-slate-200 text-slate-600 px-2 py-0.5 rounded font-bold">{{ $series->count() }} total</span>
                </div>
                <div class="p-4 divide-y divide-slate-100">
                    @forelse($series as $s)
                        <div class="py-3 flex justify-between items-center text-sm">
                            <div>
                                <span class="font-bold text-slate-800">{{ $s->series_name }}</span>
                                <span class="text-xxs text-slate-400 block mt-0.5 font-semibold">Code: {{ $s->series_code }} | Year: {{ $s->year }}</span>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xxs font-extrabold {{ $s->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-slate-100 text-slate-455 border border-slate-200' }}">
                                {{ $s->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    @empty
                        <p class="p-4 text-center text-slate-400 text-xs">No exam series configured.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Middle/Right Column: Subjects & Grade Thresholds -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Subjects list Card -->
            <div class="bg-white rounded-2xl border border-slate-150 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Registered Subjects</h3>
                    <span class="text-xs bg-slate-100 border border-slate-200 text-slate-600 px-2 py-0.5 rounded font-bold">{{ $subjects->count() }} total</span>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @forelse($subjects as $sub)
                            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 flex items-center justify-between">
                                <div class="min-w-0">
                                    <span class="text-xs font-bold text-slate-800 block truncate">{{ $sub->subject_name }}</span>
                                    <span class="text-xxs text-slate-450 font-semibold block font-mono mt-0.5">Code: {{ $sub->subject_code }}</span>
                                </div>
                                <span class="px-2 py-0.5 bg-indigo-50 border border-indigo-100 text-indigo-700 text-xxs font-bold rounded">
                                    {{ $sub->qualification->type_display }}
                                </span>
                            </div>
                        @empty
                            <p class="col-span-2 text-center text-slate-400 text-xs py-4">No subjects registered in the system.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Grade Threshold Boundaries list -->
            <div class="bg-white rounded-2xl border border-slate-150 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Grade Threshold Mapping Rules</h3>
                    <span class="text-xs bg-slate-100 border border-slate-200 text-slate-600 px-2 py-0.5 rounded font-bold">{{ $thresholds->count() }} boundaries</span>
                </div>
                <div class="overflow-x-auto max-h-96 overflow-y-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-150 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                                <th class="px-6 py-3">Subject</th>
                                <th class="px-6 py-3">Series</th>
                                <th class="px-6 py-3">Grade</th>
                                <th class="px-6 py-3 text-right">PUM Bounds</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs text-slate-600">
                            @forelse($thresholds as $thresh)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-3 font-semibold text-slate-700">{{ $thresh->subject->subject_name }}</td>
                                    <td class="px-6 py-3 font-mono text-xxs text-slate-400">{{ $thresh->series->series_name }}</td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center justify-center w-6 h-6 bg-slate-900 text-white rounded-full font-black text-xxs shadow-sm">
                                            {{ $thresh->grade }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-right font-bold text-slate-850">
                                        {{ $thresh->minimum_pum }}% - {{ $thresh->maximum_pum ?? 100 }}%
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-slate-400">
                                        No grade boundaries mapped yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
