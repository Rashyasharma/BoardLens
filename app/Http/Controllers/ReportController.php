<?php

namespace App\Http\Controllers;

use App\Models\UploadLog;
use App\Models\ExamSeries;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Show report logs index.
     */
    public function index()
    {
        $schoolId = auth()->user()->school_id;
        $query = UploadLog::query();
        
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        $reports = $query->with(['series', 'subject', 'user', 'school'])
            ->latest()
            ->paginate(15);

        return view('reports.index', compact('reports'));
    }
}
