@extends('layouts.app')

@section('title', 'Edit Exam Series')
@section('page-title', 'Edit Exam Series')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-slate-400 font-semibold">
        <a href="{{ route('exam-series.index') }}" class="hover:text-indigo-600 transition">Exam Series</a>
        <span>›</span>
        <span class="text-slate-600">Edit — {{ $series->series_code }}</span>
    </div>

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 rounded-xl px-5 py-3.5 text-sm text-rose-700">
            <ul class="space-y-1 list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('exam-series.update', $series->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')



        {{-- Year --}}
        <div class="bg-white rounded-2xl border border-slate-150 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Year</h2>
            </div>
            <div class="px-6 py-5">
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-2">Exam Year</label>
                <input type="number" name="year" value="{{ old('year', $series->year) }}"
                    min="2000" max="2100" required
                    class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20" />
            </div>
        </div>

        {{-- Month --}}
        <div class="bg-white rounded-2xl border border-slate-150 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Exam Session</h2>
            </div>
            <div class="px-6 py-5">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @foreach([
                        'March'    => ['label' => 'February / March',  'icon' => '❄️', 'desc' => 'Winter session'],
                        'June'     => ['label' => 'May / June',        'icon' => '☀️', 'desc' => 'Summer session'],
                        'November' => ['label' => 'October / November','icon' => '🍂', 'desc' => 'Autumn session'],
                    ] as $value => $opt)
                        <label class="relative cursor-pointer">
                            <input type="radio" name="month" value="{{ $value }}"
                                {{ old('month', $series->month) == $value ? 'checked' : '' }}
                                class="peer sr-only" required />
                            <div class="border-2 border-slate-200 rounded-2xl p-4 text-center transition-all duration-150
                                peer-checked:border-indigo-500 peer-checked:bg-indigo-50 hover:border-slate-300">
                                <span class="text-2xl block mb-1.5">{{ $opt['icon'] }}</span>
                                <p class="text-sm font-extrabold text-slate-700">{{ $opt['label'] }}</p>
                                <p class="text-xxs text-slate-400 font-semibold mt-0.5">{{ $opt['desc'] }}</p>
                            </div>
                            <div class="absolute top-2 right-2 hidden peer-checked:flex items-center justify-center w-5 h-5 bg-indigo-600 rounded-full">
                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Optional dates + status --}}
        <div class="bg-white rounded-2xl border border-slate-150 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Dates & Status</h2>
            </div>
            <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-2">Entry Deadline</label>
                    <input type="date" name="deadline_for_entry"
                        value="{{ old('deadline_for_entry', $series->deadline_for_entry?->format('Y-m-d')) }}"
                        class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-2">Results Publication Date</label>
                    <input type="date" name="result_publication_date"
                        value="{{ old('result_publication_date', $series->result_publication_date?->format('Y-m-d')) }}"
                        class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20" />
                </div>
                <div class="sm:col-span-2">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="is_active" value="0" />
                        <input type="checkbox" name="is_active" value="1"
                            {{ old('is_active', $series->is_active) ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                        <span class="text-sm font-semibold text-slate-700">Active Series</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('exam-series.index') }}"
               class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                Cancel
            </a>
            <button type="submit"
                class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
