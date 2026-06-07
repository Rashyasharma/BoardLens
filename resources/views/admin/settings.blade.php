@extends('layouts.app')

@section('title', 'System Settings - Cambridge Exam Portal')
@section('page-title', 'System Settings')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <h3 class="text-lg font-bold text-slate-800 tracking-tight">System Settings</h3>
        <p class="text-sm text-slate-500 mt-1">Configure schools and basic qualification listings.</p>
        
        <div class="mt-6 space-y-6">
            <div>
                <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-3">Affiliated Schools Catalog</h4>
                <div class="space-y-3">
                    @foreach($schools as $school)
                        <div class="p-4 bg-slate-50 border border-slate-100 rounded-xl flex justify-between items-center text-sm">
                            <div>
                                <span class="font-bold text-slate-800">{{ $school->school_name }}</span>
                                <span class="text-xs text-slate-400 block mt-0.5">Code: {{ $school->school_code }} &bull; Email: {{ $school->contact_email }}</span>
                            </div>
                            <span class="text-xs text-slate-500 font-medium">{{ $school->contact_phone }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="border-t border-slate-100 pt-6">
                <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-3">Academic Qualification Levels</h4>
                <div class="space-y-3">
                    @foreach($qualifications as $qual)
                        <div class="p-4 bg-slate-50 border border-slate-100 rounded-xl text-sm">
                            <span class="font-bold text-slate-800">{{ $qual->qualification_name }}</span>
                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 font-bold rounded text-[10px] ml-2 font-mono">{{ $qual->qualification_type }}</span>
                            <span class="text-xs text-slate-400 block mt-1">{{ $qual->description }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
