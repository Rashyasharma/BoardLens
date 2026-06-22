@extends('layouts.app')

@section('title', 'CBSE Qualifications')
@section('page-title', 'CBSE Qualifications')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <p class="text-slate-500 text-sm">Select a qualification to inspect subjects and curriculum requirements.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($qualifications as $qualification)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 hover:shadow-md transition duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-{{ $qualification->color_class }}-50 text-{{ $qualification->color_class }}-750 uppercase tracking-wide">
                            {{ $qualification->qualification_type }}
                        </span>
                        <h3 class="text-xl font-extrabold text-slate-800 mt-2">{{ $qualification->qualification_name }}</h3>
                        <p class="text-slate-500 text-sm mt-1">Board Code: {{ $qualification->board_code ?? 'N/A' }}</p>
                    </div>
                    <span class="text-4xl">🎓</span>
                </div>
                
                <p class="text-slate-650 text-sm mt-4 leading-relaxed">
                    {{ $qualification->description ?? 'No description available for this CBSE qualification.' }}
                </p>

                <div class="mt-6 pt-6 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-450">{{ $qualification->subjects_count }} Subjects cataloged</span>
                    <a href="{{ route('cbse.qualifications.show', $qualification->id) }}" class="text-sm font-bold text-amber-600 hover:text-amber-700 transition">
                        View Subjects →
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
