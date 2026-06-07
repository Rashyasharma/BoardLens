@extends('layouts.app')

@section('title', 'Edit Candidate Profile - Cambridge Exam Portal')
@section('page-title', 'Edit Candidate Profile')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('students.show', $candidate->id) }}" class="inline-flex items-center text-sm font-semibold text-indigo-650 hover:text-indigo-800 transition duration-150">
            &larr; Back to Profile
        </a>
    </div>

    <!-- Edit Card -->
    <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm space-y-6">
        <div class="border-b border-slate-100 pb-4">
            <h3 class="text-xl font-bold text-slate-800 tracking-tight">Candidate Details</h3>
            <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-semibold">Candidate Number: {{ $candidate->candidate_number }}</p>
        </div>

        <form method="POST" action="{{ route('students.update', $candidate->id) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Candidate Name</label>
                <input type="text" name="candidate_name" value="{{ old('candidate_name', $candidate->candidate_name) }}" required
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all duration-150">
            </div>

            <!-- Number -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Candidate Number</label>
                <input type="text" name="candidate_number" value="{{ old('candidate_number', $candidate->candidate_number) }}" required
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all duration-150 font-mono">
            </div>

            <!-- Birth Date -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Date of Birth</label>
                <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $candidate->date_of_birth ? $candidate->date_of_birth->format('Y-m-d') : '') }}"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all duration-150">
            </div>

            <!-- Gender & Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Gender -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Gender</label>
                    <select name="gender" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all duration-150">
                        <option value="M" {{ old('gender', $candidate->gender) == 'M' ? 'selected' : '' }}>Male</option>
                        <option value="F" {{ old('gender', $candidate->gender) == 'F' ? 'selected' : '' }}>Female</option>
                        <option value="O" {{ old('gender', $candidate->gender) == 'O' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Enrollment Status</label>
                    <select name="status" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all duration-150">
                        <option value="active" {{ old('status', $candidate->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $candidate->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="graduated" {{ old('status', $candidate->status) == 'graduated' ? 'selected' : '' }}>Graduated</option>
                    </select>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('students.show', $candidate->id) }}"
                   class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">
                    Cancel
                </a>
                <button type="submit"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
