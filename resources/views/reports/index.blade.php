@extends('layouts.app')

@section('title', 'Audit Reports - Cambridge Exam Portal')
@section('page-title', 'Audit Reports')

@section('content')
<div class="space-y-6">
    <!-- Reports Header Info -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <h3 class="text-lg font-bold text-slate-800 tracking-tight">System Audit Log & Generated Records</h3>
        <p class="text-sm text-slate-500 mt-1">This panel shows the transaction log of files uploaded and processed. To export complete analytics summaries, use the download links on the Performance Analytics dashboard page.</p>
    </div>

    <!-- Reports Table -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-slate-400 text-xs font-semibold uppercase tracking-wider border-b border-slate-200 bg-slate-50">
                        <th class="py-3.5 px-6">Date Generated</th>
                        <th class="py-3.5 px-6">Report Category</th>
                        <th class="py-3.5 px-6">Reference File</th>
                        <th class="py-3.5 px-6">Source School</th>
                        <th class="py-3.5 px-6 text-center">Rows Added</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($reports as $report)
                        <tr class="hover:bg-slate-50/60 transition duration-150">
                            <td class="py-4 px-6 font-medium text-slate-600">
                                {{ $report->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="py-4 px-6">
                                <span class="capitalize text-slate-800 font-bold text-xs">
                                    {{ str_replace('_', ' ', $report->upload_type) }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-slate-500 font-mono text-xs">
                                {{ $report->file_name }}
                            </td>
                            <td class="py-4 px-6 text-slate-500">
                                {{ $report->school ? $report->school->school_name : '-' }}
                            </td>
                            <td class="py-4 px-6 text-center text-slate-600 font-bold font-mono">
                                {{ $report->records_processed }}
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if($report->status === 'success')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Success
                                    </span>
                                @elseif($report->status === 'partial')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-50 text-amber-700 border border-emerald-200">
                                        Partial
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Failed
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-6 text-center text-slate-400">
                                No audit records logged.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($reports->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
