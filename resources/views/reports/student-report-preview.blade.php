<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Report Card — {{ $student->candidate_name }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
    :root {
        --indigo: #4f46e5;
        --indigo-light: #eef2ff;
        --emerald: #059669;
        --emerald-light: #ecfdf5;
        --amber: #d97706;
        --amber-light: #fffbeb;
        --rose: #e11d48;
        --slate-50: #f8fafc;
        --slate-100: #f1f5f9;
        --slate-200: #e2e8f0;
        --slate-400: #94a3b8;
        --slate-600: #475569;
        --slate-800: #1e293b;
        --page-width: 794px; /* A4 at 96dpi */
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Inter', sans-serif;
        background: #e5e7eb;
        color: var(--slate-800);
        min-height: 100vh;
    }

    /* ── Toolbar (hidden on print) ── */
    .toolbar {
        position: fixed;
        top: 0; left: 0; right: 0;
        background: #1e293b;
        padding: 12px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        z-index: 999;
        box-shadow: 0 2px 12px rgba(0,0,0,0.3);
    }

    .toolbar-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .toolbar-title {
        color: white;
        font-size: 13px;
        font-weight: 700;
    }

    .toolbar-sub {
        color: #94a3b8;
        font-size: 11px;
        font-weight: 500;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: 8px;
        background: #334155;
        color: #e2e8f0;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.15s;
    }
    .btn-back:hover { background: #475569; }

    .btn-download {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 20px;
        border-radius: 9px;
        background: var(--indigo);
        color: white;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        transition: background 0.15s, transform 0.1s;
        box-shadow: 0 2px 8px rgba(79,70,229,0.4);
    }
    .btn-download:hover { background: #4338ca; transform: translateY(-1px); }

    /* ── Page canvas ── */
    .page-canvas {
        padding: 80px 0 40px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0;
    }

    .a4-page {
        width: var(--page-width);
        min-height: 1123px; /* A4 at 96dpi */
        background: #ffffff;
        box-shadow: 0 4px 32px rgba(0,0,0,0.18);
        padding: 40px 44px 80px 44px;
        position: relative;
        margin-bottom: 24px;
        border-radius: 8px;
    }

    /* ── Report Content ── */

    .rpt-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        border-bottom: 3px solid var(--indigo);
        padding-bottom: 12px;
        margin-bottom: 18px;
    }

    .rpt-school {
        font-size: 18px;
        font-weight: 800;
        color: var(--slate-800);
        letter-spacing: -0.4px;
    }

    .rpt-doc-title {
        font-size: 10.5px;
        color: var(--slate-400);
        font-weight: 600;
        margin-top: 3px;
    }

    .rpt-date {
        text-align: right;
        font-size: 9.5px;
        color: var(--slate-400);
        font-weight: 600;
        line-height: 1.6;
    }

    /* Candidate Banner */
    .cand-banner {
        background: var(--slate-800);
        border-radius: 10px;
        padding: 14px 20px;
        margin-bottom: 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .cand-name {
        font-size: 17px;
        font-weight: 800;
        color: white;
        letter-spacing: -0.3px;
    }

    .cand-meta {
        font-size: 10px;
        color: #94a3b8;
        font-weight: 500;
        margin-top: 5px;
        line-height: 1.5;
    }

    .cand-badge {
        background: var(--indigo);
        color: white;
        font-size: 9px;
        font-weight: 700;
        padding: 5px 13px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        white-space: nowrap;
    }

    /* Stats row */
    .stats-row {
        display: flex;
        gap: 8px;
        margin-bottom: 16px;
    }

    .stat-box {
        flex: 1;
        border: 1.5px solid var(--slate-200);
        border-radius: 8px;
        padding: 10px 8px;
        text-align: center;
    }

    .stat-lbl {
        font-size: 7.5px;
        font-weight: 700;
        color: var(--slate-400);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        display: block;
        margin-bottom: 4px;
    }

    .stat-val {
        font-size: 20px;
        font-weight: 800;
        color: var(--slate-800);
        display: block;
        line-height: 1.1;
    }

    .stat-val.v-indigo  { color: var(--indigo); }
    .stat-val.v-emerald { color: var(--emerald); }
    .stat-val.v-amber   { color: var(--amber); }

    /* Section heading */
    .sec-title {
        font-size: 8.5px;
        font-weight: 700;
        color: var(--slate-600);
        text-transform: uppercase;
        letter-spacing: 1.2px;
        border-bottom: 1.5px solid var(--slate-200);
        padding-bottom: 5px;
        margin-bottom: 12px;
    }

    /* Series block */
    .series-block {
        margin-bottom: 18px;
        page-break-inside: avoid;
        break-inside: avoid;
    }

    .series-hd {
        background: var(--slate-50);
        border: 1.5px solid var(--slate-200);
        border-bottom: none;
        border-radius: 8px 8px 0 0;
        padding: 10px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .series-nm {
        font-size: 12.5px;
        font-weight: 800;
        color: var(--slate-800);
    }

    .series-sub {
        font-size: 9.5px;
        color: var(--slate-400);
        font-weight: 600;
        margin-top: 2px;
    }

    .pills {
        display: flex;
        align-items: center;
        gap: 5px;
        flex-shrink: 0;
    }

    .pill {
        font-size: 8.5px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 20px;
    }

    .p-indigo  { background: var(--indigo-light); color: #4338ca; border: 1px solid #c7d2fe; }
    .p-emerald { background: var(--emerald-light); color: #065f46; border: 1px solid #a7f3d0; }
    .p-up      { background: var(--emerald-light); color: #065f46; border: 1px solid #a7f3d0; }
    .p-down    { background: #fff1f2; color: #9f1239; border: 1px solid #fecdd3; }
    .p-flat    { background: var(--slate-100); color: var(--slate-600); border: 1px solid var(--slate-200); }

    /* Results table */
    .res-table {
        width: 100%;
        border-collapse: collapse;
        border: 1.5px solid var(--slate-200);
        border-top: none;
        border-radius: 0 0 8px 8px;
        overflow: hidden;
    }

    .res-table th {
        background: #f1f5f9;
        font-size: 8px;
        font-weight: 700;
        color: var(--slate-600);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 7px 11px;
        text-align: left;
        border-bottom: 1.5px solid var(--slate-200);
    }

    .res-table td {
        padding: 8px 11px;
        font-size: 10px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .res-table tbody tr:last-child td { border-bottom: none; }
    .res-table tbody tr.subj-row td { border-bottom: none; }
    .res-table tbody tr.comp-row td { padding: 2px 11px 8px 20px; border-bottom: 1px solid #f1f5f9; }
    .res-table tbody tr:nth-child(4n+3) td,
    .res-table tbody tr:nth-child(4n+4) td { background: #fafafa; }

    .subj-nm {
        font-weight: 700;
        font-size: 10.5px;
        color: var(--slate-800);
    }

    .code-tag {
        font-size: 8px;
        font-family: monospace;
        color: var(--slate-400);
        background: var(--slate-100);
        border: 1px solid var(--slate-200);
        padding: 1px 5px;
        border-radius: 3px;
        margin-left: 5px;
    }

    .qual-tag {
        display: inline-block;
        font-size: 8px;
        font-weight: 700;
        color: #6366f1;
        background: var(--indigo-light);
        border: 1px solid #c7d2fe;
        padding: 2px 7px;
        border-radius: 4px;
    }

    .grade-dot {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        font-weight: 800;
        font-size: 9.5px;
        color: white;
    }

    .gd-top  { background: #059669; }
    .gd-good { background: #4f46e5; }
    .gd-mid  { background: #d97706; }
    .gd-low  { background: #dc2626; }

    .pum-num {
        font-weight: 800;
        font-size: 12px;
        color: var(--indigo);
    }

    .comp-txt {
        font-size: 9px;
        color: var(--slate-400);
        font-family: monospace;
        word-break: break-all;
    }

    .no-res {
        border: 1.5px solid var(--slate-200);
        border-top: none;
        border-radius: 0 0 8px 8px;
        padding: 14px;
        text-align: center;
        font-size: 9px;
        color: var(--slate-400);
        font-style: italic;
    }

    /* Footer */
    .rpt-footer {
        border-top: 1.5px solid var(--slate-200);
        padding-top: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: absolute;
        bottom: 30px;
        left: 44px;
        right: 44px;
    }

    .footer-note { font-size: 8.5px; color: var(--slate-400); font-weight: 500; }
    .footer-brand { font-size: 8.5px; font-weight: 700; color: var(--indigo); }

    /* ── Print Styles ── */
    @media print {
        body { background: white !important; }
        .toolbar { display: none !important; }
        .page-canvas { padding: 0 !important; align-items: unset; }
        .a4-page {
            box-shadow: none !important;
            width: 100% !important;
            min-height: unset !important;
            padding: 20px 25px !important;
            margin: 0 !important;
            page-break-after: always;
        }
        .series-block { page-break-inside: avoid; break-inside: avoid; }
        .page-break { page-break-after: always; }
    }

    @page { size: A4; margin: 15mm; }
</style>
</head>
<body>

{{-- ── TOOLBAR ── --}}
<div class="toolbar">
    <div class="toolbar-left">
        <a href="{{ url()->previous() }}" class="btn-back">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
        <div>
            <div class="toolbar-title">Report Card Preview</div>
            <div class="toolbar-sub">{{ $student->candidate_name }} &mdash; {{ count($journey) }} Series</div>
        </div>
    </div>
    <a href="{{ route('analysis.student-journey.pdf', ['candidate_name' => $student->candidate_name]) }}" class="btn-download">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Download PDF
    </a>
</div>

{{-- ── A4 PAGE ── --}}
<div class="page-canvas">
    @foreach($journey as $index => $stage)
    <div class="a4-page">
        {{-- Header --}}
        @if($index === 0)
            <div class="rpt-header">
                <div>
                    <div class="rpt-school">{{ $schoolName }}</div>
                    <div class="rpt-doc-title">Cambridge Academic Report Card &mdash; Student Journey</div>
                </div>
                <div class="rpt-date">
                    Generated: {{ now()->format('d M Y, H:i') }}<br>
                    Academic Record Document
                </div>
            </div>

            {{-- Candidate Banner --}}
            <div class="cand-banner">
                <div>
                    <div class="cand-name">{{ $student->candidate_name }}</div>
                    <div class="cand-meta">
                        Candidate No: {{ implode(', ', $all_candidate_numbers) }}
                        &nbsp;|&nbsp; School: {{ $student->school?->school_name ?? 'N/A' }}
                        @if($student->date_of_birth)
                            &nbsp;|&nbsp; DOB: {{ $student->date_of_birth->format('d M Y') }}
                        @endif
                    </div>
                </div>
                <span class="cand-badge">Official Record</span>
            </div>

            {{-- Stats --}}
            <div class="stats-row">
                <div class="stat-box">
                    <span class="stat-lbl">Total Papers</span>
                    <span class="stat-val">{{ $total_results_count }}</span>
                </div>
                <div class="stat-box">
                    <span class="stat-lbl">Average PUM</span>
                    <span class="stat-val v-indigo">{{ $avg_pum_overall }}%</span>
                </div>
                <div class="stat-box">
                    <span class="stat-lbl">Best Grade</span>
                    <span class="stat-val v-emerald">{{ $best_grade }}</span>
                </div>
                <div class="stat-box">
                    <span class="stat-lbl">Pass Rate</span>
                    <span class="stat-val v-amber">{{ $pass_rate_overall }}%</span>
                </div>
                <div class="stat-box">
                    <span class="stat-lbl">Exam Series</span>
                    <span class="stat-val">{{ count($journey) }}</span>
                </div>
            </div>

            <div class="sec-title">Examination Series Results</div>
        @else
            {{-- Subsequent Page Header --}}
            <div class="rpt-header" style="border-bottom: 2px solid var(--indigo); padding-bottom: 8px; margin-bottom: 15px;">
                <div>
                    <div class="rpt-school" style="font-size: 15px;">{{ $schoolName }}</div>
                    <div class="rpt-doc-title" style="font-size: 9px; margin-top: 2px;">Cambridge Academic Report Card &mdash; {{ $student->candidate_name }}</div>
                </div>
                <div class="rpt-date" style="font-size: 8.5px; line-height: 1.4;">
                    Page {{ $index + 1 }} of {{ count($journey) }}<br>
                    Academic Record Document
                </div>
            </div>

            {{-- Subsequent Page Candidate Summary --}}
            <div class="cand-banner" style="padding: 8px 16px; margin-bottom: 15px;">
                <div class="cand-name" style="font-size: 13px;">{{ $student->candidate_name }}</div>
                <div class="cand-meta" style="font-size: 8.5px; margin-top: 0; color: #94a3b8;">
                    Candidate No: {{ $stage['candidate_number'] }} &nbsp;|&nbsp; Series: {{ $stage['series_name'] }}
                </div>
            </div>
        @endif

        {{-- Series Block --}}
        <div class="series-block">
            <div class="series-hd">
                <div>
                    <div class="series-nm">{{ $stage['series_name'] }}</div>
                    <div class="series-sub">
                        {{ $stage['month'] }} {{ $stage['year'] }}
                        &middot;
                        {{ $stage['total_subjects'] }} {{ $stage['total_subjects'] == 1 ? 'Subject' : 'Subjects' }} Attempted
                        &middot;
                        <span style="font-family:monospace; background:#f1f5f9; border:1px solid #e2e8f0; padding:1px 6px; border-radius:4px; font-size:8.5px; color:#475569; font-weight:700;">
                            Cand. No: {{ $stage['candidate_number'] }}
                        </span>
                    </div>
                </div>
                <div class="pills">
                    <span class="pill p-indigo">Avg PUM: {{ $stage['avg_pum'] }}%</span>
                    <span class="pill p-emerald">Best: {{ $stage['best_grade'] }}</span>
                    @if($stage['pum_delta'] !== null)
                        @if($stage['pum_delta'] > 0)
                            <span class="pill p-up">&#9650; +{{ $stage['pum_delta'] }}</span>
                        @elseif($stage['pum_delta'] < 0)
                            <span class="pill p-down">&#9660; {{ $stage['pum_delta'] }}</span>
                        @else
                            <span class="pill p-flat">&#9679; 0.0</span>
                        @endif
                    @endif
                </div>
            </div>

            @if($stage['results']->isNotEmpty())
            <table class="res-table">
                <thead>
                    <tr>
                        <th style="width:40%">Subject</th>
                        <th style="width:20%">Qualification</th>
                        <th style="width:8%;text-align:center">Grade</th>
                        <th style="width:10%;text-align:center">PUM</th>
                        <th style="width:12%;text-align:center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stage['results'] as $res)
                    @php
                        $g = strtoupper($res->grade ?? '');
                        $gc = 'gd-mid';
                        if (in_array($g, ['A*','A*A*','A','AA'])) $gc = 'gd-top';
                        elseif (in_array($g, ['B','BB','C','CC'])) $gc = 'gd-good';
                        elseif (in_array($g, ['U','UU'])) $gc = 'gd-low';
                        $hasMarks = $res->componentMarks->isNotEmpty();
                    @endphp
                    {{-- Subject main row --}}
                    <tr class="{{ $hasMarks ? 'subj-row' : '' }}">
                        <td>
                            <span class="subj-nm">{{ $res->subject->subject_name }}</span>
                            <span class="code-tag">{{ $res->subject->subject_code }}</span>
                        </td>
                        <td>
                            <span class="qual-tag">{{ $res->subject->qualification->qualification_name ?? '—' }}</span>
                        </td>
                        <td style="text-align:center">
                            <span class="grade-dot {{ $gc }}">{{ $res->grade }}</span>
                        </td>
                        <td style="text-align:center">
                            <span class="pum-num">{{ $res->pum }}%</span>
                        </td>
                        <td style="text-align:center;font-size:9px;font-weight:700;color:{{ $res->is_passed ? '#059669' : '#dc2626' }}">
                            {{ $res->is_passed ? 'PASS' : 'FAIL' }}
                        </td>
                    </tr>
                    {{-- Component marks sub-row --}}
                    @if($hasMarks)
                    <tr class="comp-row">
                        <td colspan="5" style="padding-left:18px;">
                            @foreach($res->componentMarks as $mark)
                                <span style="display:block;width:fit-content;font-size:8.5px;font-family:monospace;background:#f8fafc;border:1px solid #e2e8f0;padding:2px 7px;border-radius:4px;margin-bottom:3px;white-space:nowrap;">
                                    <strong style="color:#1e293b;">{{ $mark->component->component_label ?? $mark->component->component_name }} ({{ $mark->component->component_code }})</strong>
                                    &nbsp;
                                    <span style="color:#4f46e5;">{{ number_format($mark->obtained_marks, 0) }}/{{ $mark->component->total_marks }}</span>
                                    <span style="color:#94a3b8;font-size:7.5px;">&nbsp;({{ number_format($mark->percentage, 1) }}%)</span>
                                </span>
                            @endforeach
                            <span style="display:block;width:fit-content;font-size:8.5px;font-family:monospace;background:#e2e8f0;border:1px solid #cbd5e1;padding:2px 7px;border-radius:4px;margin-bottom:3px;white-space:nowrap;font-weight:bold;">
                                <strong style="color:#0f172a;">Total Component Marks</strong>
                                &nbsp;
                                <span style="color:#1e1b4b;">{{ number_format($res->componentMarks->sum('obtained_marks'), 0) }}/{{ $res->componentMarks->sum('total_marks') }}</span>
                                <span style="color:#475569;font-size:7.5px;">&nbsp;({{ number_format(($res->componentMarks->sum('obtained_marks') / max(1, $res->componentMarks->sum('total_marks'))) * 100, 1) }}%)</span>
                            </span>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
            @else
                <div class="no-res">No results recorded for this series.</div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="rpt-footer">
            <span class="footer-note">This report is generated from BoardLens. Page {{ $index + 1 }} of {{ count($journey) }}</span>
            <span class="footer-brand">BoardLens &middot; {{ $schoolName }}</span>
        </div>
    </div>
    @endforeach
</div>

</body>
</html>
