<div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="text-slate-400 text-xs font-semibold uppercase tracking-wider border-b border-slate-200 bg-slate-50">
                    <th class="py-3.5 px-6">Candidate Name</th>
                    <th class="py-3.5 px-6">Candidate Numbers (Series)</th>
                    <th class="py-3.5 px-6 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($candidates as $cand)
                    <tr class="hover:bg-slate-50/60 transition duration-150">
                        <td class="py-4 px-6 text-slate-800 font-bold text-[15px]">
                            <a href="{{ route('analysis.student-journey', ['candidate_name' => $cand->candidate_name]) }}" class="hover:text-indigo-650 transition">
                                {{ $cand->candidate_name }}
                            </a>
                        </td>
                        <td class="py-4 px-6 font-mono font-semibold text-slate-600">
                            {{ $cand->candidate_numbers_with_series }}
                        </td>
                        <td class="py-4 px-6 text-center">
                            <a href="{{ route('analysis.student-journey', ['candidate_name' => $cand->candidate_name]) }}" class="inline-flex items-center px-3.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-lg text-xs font-bold transition duration-150">
                                Analyse &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-12 px-6 text-center text-slate-400">
                            No candidates matched the criteria. Create candidates or upload a list to populate this table.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination links -->
    @if($candidates->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $candidates->appends(request()->only(['search', 'qualification_id', 'year', 'series_id']))->links() }}
        </div>
    @endif
</div>
