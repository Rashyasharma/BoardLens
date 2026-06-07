<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cambridge Analytics Report</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #334155;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #6366f1;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header h1 {
            color: #0f172a;
            font-size: 24px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .header p {
            color: #64748b;
            font-size: 12px;
            margin: 4px 0 0 0;
        }
        .section-title {
            color: #0f172a;
            font-size: 16px;
            font-weight: 700;
            margin: 25px 0 12px 0;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f8fafc;
            border-bottom: 2px solid #cbd5e1;
            color: #475569;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            padding: 8px 10px;
            text-align: left;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .font-bold {
            font-weight: 700;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-align: center;
        }
        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-rose {
            background-color: #ffe4e6;
            color: #9f1239;
        }
        .stat-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        .stat-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            width: 23%;
            float: left;
            margin-right: 2%;
        }
        .stat-card-last {
            margin-right: 0;
        }
        .stat-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 700;
            display: block;
        }
        .stat-val {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            display: block;
            margin-top: 5px;
        }
        .clearfix {
            clear: both;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1>Cambridge Exam Portal</h1>
        <p>Yearly Performance Analytics Report &bull; Generated: {{ now()->format('M d, Y H:i') }}</p>
    </div>

    <!-- Filters Info -->
    <table style="width: 100%; margin-bottom: 25px; font-size: 12px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
        <tr>
            <td style="border: none; padding: 10px 15px; font-weight: 700; color: #475569; width: 120px;">Active Filters:</td>
            <td style="border: none; padding: 10px 15px;">
                Year: {{ $filters['year'] ?? 'All Years' }} &bull;
                Series: {{ $filters['series_id'] ? \App\Models\ExamSeries::find($filters['series_id'])->series_name ?? 'N/A' : 'All Series' }} &bull;
                Subject: {{ $filters['subject_id'] ? \App\Models\Subject::find($filters['subject_id'])->subject_name ?? 'N/A' : 'All Subjects' }}
            </td>
        </tr>
    </table>

    <!-- Key Statistics -->
    <div class="section-title">Key Performance Indicators</div>
    <div class="stat-grid">
        <div class="stat-card">
            <span class="stat-label">Total Candidates</span>
            <span class="stat-val">{{ $statisticalSummary['total_students'] }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Average Score</span>
            <span class="stat-val" style="color: #6366f1;">{{ $statisticalSummary['average_percentage'] }}%</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Overall Pass Rate</span>
            <span class="stat-val" style="color: #10b981;">{{ $passFailStats['pass_rate'] }}%</span>
        </div>
        <div class="stat-card stat-card-last">
            <span class="stat-label">Highest Score</span>
            <span class="stat-val" style="color: #a855f7;">{{ $statisticalSummary['highest_score'] }}%</span>
        </div>
    </div>
    <div class="clearfix"></div>

    <!-- Subject Performance Breakdown -->
    <div class="section-title" style="margin-top: 30px;">Subject Performance Breakdown</div>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Subject Name</th>
                <th class="text-center">Students</th>
                <th class="text-center">Average Score</th>
                <th class="text-center">Pass Rate</th>
                <th class="text-center">Min Score</th>
                <th class="text-center">Max Score</th>
            </tr>
        </thead>
        <tbody>
            @forelse($subjectPerformance as $row)
                <tr>
                    <td class="font-bold">{{ $row->subject->subject_code }}</td>
                    <td class="font-bold">{{ $row->subject->subject_name }}</td>
                    <td class="text-center">{{ $row->total_students }}</td>
                    <td class="text-center font-bold" style="color: #6366f1;">{{ $row->avg_percentage }}%</td>
                    <td class="text-center font-bold" style="color: #10b981;">{{ $row->pass_rate }}%</td>
                    <td class="text-center">{{ $row->min_percentage }}%</td>
                    <td class="text-center">{{ $row->max_percentage }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="color: #94a3b8;">No subject records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Grade Distributions -->
    <div class="section-title">Grade Distribution Summary</div>
    <table style="width: 50%;">
        <thead>
            <tr>
                <th>Grade</th>
                <th class="text-center">Candidates Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach($gradeDistribution as $grade => $count)
                <tr>
                    <td class="font-bold" style="padding: 8px 10px;">Grade {{ $grade }}</td>
                    <td class="text-center font-bold" style="padding: 8px 10px;">{{ $count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
