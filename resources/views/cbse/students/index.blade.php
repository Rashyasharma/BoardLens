@extends('layouts.app')

@section('title', 'CBSE Students')
@section('page-title', 'CBSE Students')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-slate-500 text-sm">Manage enrolled student profiles for CBSE qualifications.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('cbse.students.create') }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition">
                + Add Student
            </a>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
        <form method="GET" action="{{ route('cbse.students.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="space-y-1.5 flex-1 min-w-[240px]">
                <label for="search" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Search</label>
                <input type="text" name="search" id="search" value="{{ $search }}" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150" placeholder="Search by name or admission number...">
            </div>

            <div class="space-y-1.5 w-[200px]">
                <label for="qualification_type" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Class</label>
                <select name="qualification_type" id="qualification_type" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150">
                    <option value="">All Classes</option>
                    <option value="CLASS_10" {{ $qualification == 'CLASS_10' ? 'selected' : '' }}>Class 10</option>
                    <option value="CLASS_12" {{ $qualification == 'CLASS_12' ? 'selected' : '' }}>Class 12</option>
                </select>
            </div>

            <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition">
                Search
            </button>
            <a href="{{ route('cbse.students.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                Reset
            </a>
        </form>
    </div>

    <!-- Students Catalog -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-150 text-xs uppercase">
                        <th class="px-6 py-3">Adm No.</th>
                        <th class="px-6 py-3">Student Name</th>
                        <th class="px-6 py-3">Gender</th>
                        <th class="px-6 py-3">Qualification</th>
                        <th class="px-6 py-3">Year of Admission</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-mono font-bold text-slate-800">{{ $student->admission_number }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('cbse.students.show', $student->id) }}" class="font-semibold text-slate-800 hover:text-amber-600 transition">
                                    {{ $student->student_name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-slate-500">{{ $student->gender_label }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                    {{ $student->qualification_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500">{{ $student->admission_year }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $student->status == 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ ucfirst($student->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('cbse.students.show', $student->id) }}" class="p-1 text-slate-400 hover:text-amber-600 transition">
                                        👁️ View
                                    </a>
                                    <a href="{{ route('cbse.students.edit', $student->id) }}" class="p-1 text-slate-400 hover:text-amber-600 transition">
                                        ✏️ Edit
                                    </a>
                                    <form action="{{ route('cbse.students.destroy', $student->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this student and their results?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 transition bg-transparent border-none cursor-pointer">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-450 italic">No student records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($students->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $students->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
