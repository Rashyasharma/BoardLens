@extends('layouts.app')

@section('title', 'Upload Log History - Cambridge Exam Portal')
@section('page-title', 'Data Upload Logs')

@section('content')
<div class="space-y-6">
    
    <!-- Logs Table -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-slate-400 text-xs font-semibold uppercase tracking-wider border-b border-slate-200 bg-slate-50">
                        <th class="py-3.5 px-6">Date & Time</th>
                        <th class="py-3.5 px-6">File Name</th>
                        <th class="py-3.5 px-6">Upload Type</th>
                        <th class="py-3.5 px-6">School</th>
                        <th class="py-3.5 px-6">Uploaded By</th>
                        <th class="py-3.5 px-6 text-center">Success Rows</th>
                        <th class="py-3.5 px-6 text-center">Failed Rows</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($uploads as $upload)
                        <tr class="hover:bg-slate-50/60 transition duration-150">
                            <td class="py-4 px-6 font-medium text-slate-600">
                                {{ $upload->uploaded_at->format('M d, Y H:i:s') }}
                            </td>
                            <td class="py-4 px-6 text-slate-800 font-medium">
                                {{ $upload->file_name }}
                            </td>
                            <td class="py-4 px-6">
                                <span class="capitalize text-slate-500 font-semibold text-xs">
                                    {{ str_replace('_', ' ', $upload->upload_type) }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-slate-500">
                                {{ $upload->school ? $upload->school->school_name : '-' }}
                            </td>
                            <td class="py-4 px-6 text-slate-500 font-medium">
                                {{ $upload->user ? $upload->user->name : 'System' }}
                            </td>
                            <td class="py-4 px-6 text-center text-slate-600 font-bold">
                                {{ $upload->records_processed }}
                            </td>
                            <td class="py-4 px-6 text-center text-rose-500 font-bold">
                                {{ $upload->records_failed }}
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if($upload->status === 'success')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        Success
                                    </span>
                                @elseif($upload->status === 'partial')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                        Partial
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                        Failed
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 px-6 text-center text-slate-400">
                                No uploads logged in history.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($uploads->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $uploads->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
