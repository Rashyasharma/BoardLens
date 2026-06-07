@extends('layouts.app')

@section('title', 'Upload Grade Thresholds - Cambridge Exam Portal')
@section('page-title', 'Upload Grade Thresholds')

@section('content')
<div class="grid grid-cols-12 gap-8">
    
    <!-- Upload Form Card -->
    <div class="col-span-12 lg:col-span-4 space-y-6">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <h3 class="text-lg font-bold text-slate-800 tracking-tight">Upload Thresholds Spreadsheet</h3>
            <p class="text-sm text-slate-500 mt-1">Upload grade boundary thresholds for an exam series.</p>

            <form method="POST" action="{{ route('uploads.thresholds.store') }}" enctype="multipart/form-data" class="space-y-5 mt-6">
                @csrf
                
                <!-- Series Select -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Exam Series</label>
                    <select name="series_id" required class="w-full px-3 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Select Series --</option>
                        @foreach($examSeries as $series)
                            <option value="{{ $series->id }}">{{ $series->series_name }} ({{ $series->series_code }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- File Selector -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Spreadsheet File (CSV / XLSX)</label>
                    <input type="file" name="thresholds_file" required accept=".csv,.xlsx,.xls"
                           class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 border border-slate-200 rounded-xl p-2.5 bg-slate-50 focus:outline-none">
                </div>

                <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm rounded-xl transition duration-150 shadow-lg shadow-indigo-600/20">
                    Process Spreadsheet
                </button>
            </form>
        </div>

        <!-- Format Guide -->
        <div class="bg-slate-900 text-slate-300 p-6 rounded-3xl shadow-sm border border-slate-800">
            <h4 class="text-sm font-bold text-white uppercase tracking-wider">File Layout Guide</h4>
            <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                The uploaded sheet must have a header row followed by data in these exact columns:
            </p>
            <div class="mt-4 overflow-x-auto text-[11px] font-mono border border-slate-800 rounded-xl bg-slate-950 p-3">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-slate-500 border-b border-slate-800">
                            <th class="pb-1">Col</th>
                            <th class="pb-1">Name</th>
                            <th class="pb-1">Example</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-900/60">
                        <tr>
                            <td class="py-1">A</td>
                            <td class="text-slate-300">subject_code</td>
                            <td class="text-emerald-400">0580</td>
                        </tr>
                        <tr>
                            <td class="py-1">B</td>
                            <td class="text-slate-300">grade</td>
                            <td class="text-emerald-400">A*</td>
                        </tr>
                        <tr>
                            <td class="py-1">C</td>
                            <td class="text-slate-300">qualification_type</td>
                            <td class="text-emerald-400">IGCSE</td>
                        </tr>
                        <tr>
                            <td class="py-1">D</td>
                            <td class="text-slate-300">minimum_percentage</td>
                            <td class="text-emerald-400">85</td>
                        </tr>
                        <tr>
                            <td class="py-1">E</td>
                            <td class="text-slate-300">minimum_marks</td>
                            <td class="text-emerald-400">170</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- History Panel -->
    <div class="col-span-12 lg:col-span-8 bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden flex flex-col justify-between">
        <div>
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-800">Recent Thresholds Uploads</h3>
                <p class="text-sm text-slate-500 mt-0.5">Logs of recently imported grade boundaries spreadsheets</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-slate-400 text-xs font-semibold uppercase tracking-wider border-b border-slate-200 bg-slate-50">
                            <th class="py-3 px-6">Date</th>
                            <th class="py-3 px-6">File Name</th>
                            <th class="py-3 px-6">Series</th>
                            <th class="py-3 px-6 text-center">Loaded</th>
                            <th class="py-3 px-6 text-center">Failed</th>
                            <th class="py-3 px-6 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($uploadHistory as $upload)
                            <tr class="hover:bg-slate-50/60 transition duration-150">
                                <td class="py-4 px-6 font-medium text-slate-600">
                                    {{ $upload->uploaded_at->format('M d, Y H:i') }}
                                </td>
                                <td class="py-4 px-6 text-slate-800 font-medium">
                                    {{ $upload->file_name }}
                                </td>
                                <td class="py-4 px-6 text-slate-500 font-semibold">
                                    {{ $upload->series ? $upload->series->series_name : 'N/A' }}
                                </td>
                                <td class="py-4 px-6 text-center text-slate-600 font-semibold">
                                    {{ $upload->records_processed }}
                                </td>
                                <td class="py-4 px-6 text-center text-rose-500 font-semibold">
                                    {{ $upload->records_failed }}
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @if($upload->status === 'success')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Success</span>
                                    @elseif($upload->status === 'partial')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Partial</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">Failed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 px-6 text-center text-slate-400">
                                    No threshold uploads logged yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($uploadHistory->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $uploadHistory->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
