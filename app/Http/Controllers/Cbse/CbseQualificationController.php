<?php

namespace App\Http\Controllers\Cbse;

use App\Http\Controllers\Controller;
use App\Models\Cbse\CbseQualification;
use Illuminate\Http\Request;

class CbseQualificationController extends Controller
{
    public function index()
    {
        $qualifications = CbseQualification::withCount('subjects')->get();
        return view('cbse.qualifications.index', compact('qualifications'));
    }

    public function show(CbseQualification $qualification)
    {
        $qualification->load(['subjects' => function ($q) {
            $q->orderBy('subject_code');
        }]);
        return view('cbse.qualifications.show', compact('qualification'));
    }
}
