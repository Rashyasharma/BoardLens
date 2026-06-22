<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Report Card — {{ $student->candidate_name }}</title>
<style>
@page { margin: 14mm 12mm 14mm 12mm; size: A4 portrait; }

* { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 10pt;
    color: #1e293b;
    background: #fff;
    line-height: 1.45;
}

/* ─ Header ─ */
table.hdr { width:100%; border-collapse:collapse; margin-bottom:12pt; border-bottom:2.5pt solid #4f46e5; padding-bottom:8pt; }
.hdr-school { font-size:14pt; font-weight:700; color:#1e293b; }
.hdr-sub    { font-size:9pt;  font-weight:600; color:#64748b; margin-top:2pt; }
.hdr-date   { font-size:8pt;  font-weight:600; color:#94a3b8; text-align:right; }

/* ─ Candidate banner ─ */
table.banner { width:100%; border-collapse:collapse; background:#1e293b; margin-bottom:12pt; }
.banner td   { padding:10pt 14pt; vertical-align:middle; }
.cand-name   { font-size:14pt; font-weight:700; color:#fff; }
.cand-meta   { font-size:8.5pt; color:#94a3b8; font-weight:500; margin-top:3pt; }
.badge       { background:#4f46e5; color:#fff; font-size:8pt; font-weight:700; padding:3pt 10pt; border-radius:20pt; text-transform:uppercase; letter-spacing:0.4pt; }

/* ─ Stats ─ */
table.stats { width:100%; border-collapse:separate; border-spacing:5pt 0; margin-bottom:12pt; }
table.stats td { border:1.5pt solid #e2e8f0; padding:7pt 5pt; text-align:center; }
.s-lbl { font-size:7pt; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.6pt; display:block; margin-bottom:2pt; }
.s-val { font-size:16pt; font-weight:700; color:#1e293b; display:block; }
.s-indigo  { color:#4f46e5; }
.s-emerald { color:#059669; }
.s-amber   { color:#d97706; }

/* ─ Section title ─ */
.sec { font-size:7.5pt; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:1pt; border-bottom:1.5pt solid #e2e8f0; padding-bottom:4pt; margin-bottom:9pt; }

/* ─ Series block ─ */
.series-block { margin-bottom:14pt; page-break-inside:avoid; }
.page-break { page-break-after: always; }

table.ser-hdr { width:100%; border-collapse:collapse; background:#f8fafc; border:1.5pt solid #e2e8f0; border-bottom:none; }
.ser-hdr td { padding:8pt 12pt; vertical-align:middle; }
.ser-nm  { font-size:11pt; font-weight:700; color:#1e293b; }
.ser-sub { font-size:8.5pt; font-weight:600; color:#64748b; margin-top:1pt; }

.pill { font-size:7.5pt; font-weight:700; padding:2pt 7pt; border-radius:10pt; display:inline-block; margin-left:3pt; }
.pi { background:#eef2ff; color:#4338ca; border:0.75pt solid #c7d2fe; }
.pe { background:#ecfdf5; color:#065f46; border:0.75pt solid #a7f3d0; }
.pu { background:#ecfdf5; color:#065f46; border:0.75pt solid #a7f3d0; }
.pd { background:#fff1f2; color:#9f1239; border:0.75pt solid #fecdd3; }
.pf { background:#f1f5f9; color:#475569; border:0.75pt solid #cbd5e1; }

/* ─ Results table ─ */
table.res { width:100%; border-collapse:collapse; border:1.5pt solid #e2e8f0; border-top:none; }
.res th { background:#f1f5f9; font-size:7pt; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.4pt; padding:5.5pt 9pt; text-align:left; border-bottom:1.5pt solid #e2e8f0; }
.res td { padding:6pt 9pt; font-size:9.5pt; color:#334155; border-bottom:1pt solid #f1f5f9; vertical-align:middle; }
.res tr.subj-row td { border-bottom:none; }
.res tr.comp-row td { padding:2pt 9pt 6pt 9pt; border-bottom:1pt solid #f1f5f9; }
.res tr:last-child td { border-bottom:none; }
.res tr.even td, .res tr.even-comp td { background:#fafafa; }

.sn  { font-weight:700; font-size:9.5pt; color:#1e293b; }
.sc  { font-size:7pt; font-family:monospace; color:#64748b; background:#f1f5f9; border:0.75pt solid #e2e8f0; padding:1pt 4pt; border-radius:2pt; margin-left:3pt; }
.qt  { font-size:7.5pt; font-weight:700; color:#6366f1; background:#eef2ff; border:0.75pt solid #c7d2fe; padding:1.5pt 6pt; border-radius:3pt; display:inline-block; }

.gc { display:inline-block; width:20pt; height:20pt; border-radius:50%; text-align:center; line-height:20pt; font-size:8.5pt; font-weight:700; color:#fff; }
.gt { background:#059669; }
.gg { background:#4f46e5; }
.gm { background:#d97706; }
.gl { background:#dc2626; }

.pv  { font-weight:700; font-size:11pt; color:#4f46e5; }

/* Component marks — inline tags per paper */
.comp-wrap { line-height:1.8; }
.comp-tag {
    display:block;
    width:fit-content;
    font-size:7.5pt;
    font-family:monospace;
    color:#475569;
    background:#f8fafc;
    border:0.75pt solid #e2e8f0;
    padding:1.5pt 5pt;
    border-radius:3pt;
    margin-bottom:3pt;
    white-space:nowrap;
}
.comp-code { font-weight:700; color:#1e293b; }
.comp-marks { color:#4f46e5; }
.comp-pct { color:#94a3b8; font-size:7pt; }

.no-res { border:1.5pt solid #e2e8f0; border-top:none; padding:10pt; text-align:center; font-size:9pt; color:#94a3b8; font-style:italic; }

/* ─ Footer ─ */
table.ftr { width:100%; border-collapse:collapse; margin-top:16pt; border-top:1.5pt solid #e2e8f0; padding-top:8pt; }
.ftr td { padding-top:8pt; font-size:8pt; }
.fn { color:#94a3b8; font-weight:500; }
.fb { color:#4f46e5; font-weight:700; text-align:right; }
</style>
</head>
<body>

{{-- HEADER --}}
<table class="hdr">
    <tr>
        <td style="padding-bottom:8pt;vertical-align:bottom;width:65%">
            <div class="hdr-school">{{ $schoolName }}</div>
            <div class="hdr-sub">Cambridge Academic Report Card &mdash; Student Journey</div>
        </td>
        <td style="padding-bottom:8pt;vertical-align:bottom;width:35%">
            <div class="hdr-date">Generated: {{ now()->format('d M Y, H:i') }}<br>Academic Record Document</div>
        </td>
    </tr>
</table>

{{-- CANDIDATE BANNER --}}
<table class="banner">
    <tr>
        <td style="width:75%">
            <div class="cand-name">{{ $student->candidate_name }}</div>
            <div class="cand-meta">
                Candidate No: {{ implode(', ', $all_candidate_numbers) }}
                &nbsp;|&nbsp; School: {{ $student->school?->school_name ?? 'N/A' }}
                @if($student->date_of_birth)
                    &nbsp;|&nbsp; DOB: {{ $student->date_of_birth->format('d M Y') }}
                @endif
            </div>
        </td>
        <td style="width:25%;text-align:right">
            <span class="badge">Official Record</span>
        </td>
    </tr>
</table>

{{-- STATS --}}
<table class="stats">
    <tr>
        <td><span class="s-lbl">Total Papers</span><span class="s-val">{{ $total_results_count }}</span></td>
        <td><span class="s-lbl">Average PUM</span><span class="s-val s-indigo">{{ $avg_pum_overall }}%</span></td>
        <td><span class="s-lbl">Best Grade</span><span class="s-val s-emerald">{{ $best_grade }}</span></td>
        <td><span class="s-lbl">Pass Rate</span><span class="s-val s-amber">{{ $pass_rate_overall }}%</span></td>
        <td><span class="s-lbl">Exam Series</span><span class="s-val">{{ count($journey) }}</span></td>
    </tr>
</table>

{{-- SERIES --}}
<div class="sec">Examination Series Results</div>

@foreach($journey as $index => $stage)
<div class="series-block" style="@if($index > 0) page-break-before: always; @endif">
    <table class="ser-hdr">
        <tr>
            <td style="width:60%">
                <div class="ser-nm">{{ $stage['series_name'] }}</div>
                <div class="ser-sub">
                    {{ $stage['month'] }} {{ $stage['year'] }}
                    &nbsp;&middot;&nbsp;
                    {{ $stage['total_subjects'] }} {{ $stage['total_subjects'] == 1 ? 'Subject' : 'Subjects' }} Attempted
                    &nbsp;&middot;&nbsp;
                    <span style="font-family:monospace;">Cand. No: {{ $stage['candidate_number'] }}</span>
                </div>
            </td>
            <td style="width:40%;text-align:right;vertical-align:middle">
                <span class="pill pi">Avg PUM: {{ $stage['avg_pum'] }}%</span>
                <span class="pill pe">Best: {{ $stage['best_grade'] }}</span>
                @if($stage['pum_delta'] !== null)
                    @if($stage['pum_delta'] > 0)<span class="pill pu">+{{ $stage['pum_delta'] }}</span>
                    @elseif($stage['pum_delta'] < 0)<span class="pill pd">{{ $stage['pum_delta'] }}</span>
                    @else<span class="pill pf">0.0</span>@endif
                @endif
            </td>
        </tr>
    </table>

    @if($stage['results']->isNotEmpty())
    <table class="res">
        <thead>
            <tr>
                <th style="width:42%">Subject</th>
                <th style="width:20%">Qualification</th>
                <th style="width:10%;text-align:center">Grade</th>
                <th style="width:12%;text-align:center">PUM</th>
                <th style="width:16%;text-align:center">Status</th>
            </tr>
        </thead>
        <tbody>
        @foreach($stage['results'] as $ri => $res)
            @php
                $g = strtoupper($res->grade ?? '');
                $gc = 'gm';
                if (in_array($g,['A*','A*A*','A','AA'])) $gc='gt';
                elseif (in_array($g,['B','BB','C','CC'])) $gc='gg';
                elseif (in_array($g,['U','UU'])) $gc='gl';
                $rowClass = ($ri % 2 === 1) ? 'even' : '';
                $hasMarks = $res->componentMarks->isNotEmpty();
            @endphp
            {{-- Subject row --}}
            <tr class="{{ $rowClass }} {{ $hasMarks ? 'subj-row' : '' }}">
                <td><span class="sn">{{ $res->subject->subject_name }}</span><span class="sc">{{ $res->subject->subject_code }}</span></td>
                <td><span class="qt">{{ $res->subject->qualification->qualification_name ?? '—' }}</span></td>
                <td style="text-align:center"><span class="gc {{ $gc }}">{{ $res->grade }}</span></td>
                <td style="text-align:center"><span class="pv">{{ $res->pum }}%</span></td>
                <td style="text-align:center;font-size:8pt;font-weight:600;color:{{ $res->is_passed ? '#059669' : '#dc2626' }}">
                    {{ $res->is_passed ? 'PASS' : 'FAIL' }}
                </td>
            </tr>
            {{-- Component marks sub-row: each paper on its own line --}}
            @if($hasMarks)
            <tr class="{{ $rowClass }} comp-row">
                <td colspan="5" style="padding-left:14pt;">
                    <div class="comp-wrap">
                        @foreach($res->componentMarks as $m)
                            <span class="comp-tag">
                                <span class="comp-code">{{ $m->component->component_label ?? $m->component->component_name }} ({{ $m->component->component_code }})</span>
                                &nbsp;
                                <span class="comp-marks">{{ number_format($m->obtained_marks, 0) }}/{{ $m->component->total_marks }}</span>
                                <span class="comp-pct">&nbsp;({{ number_format($m->percentage, 1) }}%)</span>
                            </span>
                        @endforeach
                        <span class="comp-tag" style="background:#f1f5f9; border-color:#cbd5e1; font-weight:bold;">
                            <span class="comp-code">Total Component Marks</span>
                            &nbsp;
                            <span class="comp-marks" style="color:#1e1b4b;">{{ number_format($res->componentMarks->sum('obtained_marks'), 0) }}/{{ $res->componentMarks->sum('total_marks') }}</span>
                            <span class="comp-pct" style="color:#475569; font-weight:bold;">&nbsp;({{ number_format(($res->componentMarks->sum('obtained_marks') / max(1, $res->componentMarks->sum('total_marks'))) * 100, 1) }}%)</span>
                        </span>
                    </div>
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
@endforeach

{{-- FOOTER --}}
<table class="ftr">
    <tr>
        <td class="fn">This report is generated from BoardLens academic management system. For official use only.</td>
        <td class="fb">BoardLens &middot; {{ $schoolName }}</td>
    </tr>
</table>

</body>
</html>
