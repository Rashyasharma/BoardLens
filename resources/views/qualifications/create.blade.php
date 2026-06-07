@extends('layouts.app')

@section('title', 'Create Qualification')
@section('page-title', 'Create New Qualification')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-8 rounded-2xl shadow-sm border border-slate-150">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('qualifications.index') }}" class="p-2 hover:bg-slate-100 rounded-xl transition text-slate-500 hover:text-slate-800">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <h2 class="text-xl font-bold text-slate-800">New Qualification Profile</h2>
    </div>

    <form method="POST" action="{{ route('qualifications.store') }}" class="space-y-6">
        @csrf

        <!-- Qualification Type -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Qualification Type *</label>
            <select name="qualification_type" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                <option value="">-- Select Type --</option>
                <option value="IGCSE" {{ old('qualification_type') == 'IGCSE' ? 'selected' : '' }}>IGCSE</option>
                <option value="AS_A_LEVEL" {{ old('qualification_type') == 'AS_A_LEVEL' ? 'selected' : '' }}>GCE AS and A Level</option>
            </select>
            <p class="text-xs text-slate-400 mt-1">IGCSE and GCE AS &amp; A Level are the two Cambridge qualification groups.</p>
        </div>

        <!-- Qualification Name -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Qualification Name *</label>
            <input type="text" name="qualification_name" value="{{ old('qualification_name') }}" placeholder="e.g. Cambridge IGCSE / IGCSE 2026" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm" />
        </div>

        <!-- Description -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Description (Optional)</label>
            <textarea name="description" rows="4" placeholder="Brief details about the curriculum or year group..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">{{ old('description') }}</textarea>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('qualifications.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-655 text-sm font-semibold rounded-xl transition">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-md transition">
                Save Qualification
            </button>
        </div>
    </form>
</div>
@endsection
