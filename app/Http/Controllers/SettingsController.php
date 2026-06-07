<?php

namespace App\Http\Controllers;

use App\Models\ExamSeries;
use App\Models\GradeThreshold;
use App\Models\Qualification;
use App\Models\Subject;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $series = ExamSeries::with('qualification')->get();
        $qualifications = Qualification::all();
        $subjects = Subject::all();
        $thresholds = GradeThreshold::with(['series', 'subject'])->get();

        return view('settings.index', [
            'series' => $series,
            'qualifications' => $qualifications,
            'subjects' => $subjects,
            'thresholds' => $thresholds,
        ]);
    }
}
