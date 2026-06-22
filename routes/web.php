<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\QualificationController;
use App\Http\Controllers\ResultsController;
use App\Http\Controllers\StudentAnalysisController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ResultUploadController;
use App\Http\Controllers\ExamSeriesController;
use App\Http\Controllers\StudentEntryController;
use App\Http\Controllers\ManualResultsController;
use App\Http\Controllers\AiImportController;

// Login/logout routes redirect to dashboard (no auth required)
Route::get('/login', function () {
    return redirect('/dashboard');
})->name('login');
Route::post('/login', function () {
    return redirect('/dashboard');
})->name('login.store');
Route::post('/logout', function () {
    return redirect('/dashboard');
})->name('logout');

// All application routes (no auth middleware)
// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// ============== QUALIFICATIONS ==============
Route::get('/qualifications', [QualificationController::class, 'index'])
    ->name('qualifications.index');
Route::get('/qualifications/create', [QualificationController::class, 'create'])
    ->name('qualifications.create');
Route::post('/qualifications', [QualificationController::class, 'store'])
    ->name('qualifications.store');
Route::get('/qualifications/{qualification}', [QualificationController::class, 'show'])
    ->name('qualifications.show');
Route::get('/qualifications/{qualification}/edit', [QualificationController::class, 'edit'])
    ->name('qualifications.edit');
Route::put('/qualifications/{qualification}', [QualificationController::class, 'update'])
    ->name('qualifications.update');
Route::delete('/qualifications/{qualification}', [QualificationController::class, 'destroy'])
    ->name('qualifications.destroy');

// Get subject details (AJAX)
Route::get('/api/subject/{subject}', [QualificationController::class, 'getSubjectDetails'])
    ->name('api.subject-details');

// ============== SUBJECTS CRUD ==============
Route::get('/subjects', [QualificationController::class, 'subjectsIndex'])
    ->name('subjects.index');
Route::get('/subjects/create', [QualificationController::class, 'createSubject'])
    ->name('subjects.create');
Route::post('/subjects', [QualificationController::class, 'storeSubjectAndComponents'])
    ->name('subjects.store');
Route::get('/subjects/{subject}/edit', [QualificationController::class, 'editSubject'])
    ->name('subjects.edit');
Route::put('/subjects/{subject}', [QualificationController::class, 'updateSubjectAndComponents'])
    ->name('subjects.update');
Route::delete('/subjects/{subject}', [QualificationController::class, 'destroySubject'])
    ->name('subjects.destroy');
Route::post('/subjects/{subject}/component-sets', [QualificationController::class, 'storeComponentSet'])
    ->name('subjects.component-sets.store');
Route::put('/subjects/{subject}/component-sets/{componentSet}', [QualificationController::class, 'updateComponentSet'])
    ->name('subjects.component-sets.update');
Route::delete('/subjects/{subject}/component-sets/{componentSet}', [QualificationController::class, 'deleteComponentSet'])
    ->name('subjects.component-sets.destroy');

// ============== EXAM SERIES ==============
Route::get('/exam-series', [ExamSeriesController::class, 'index'])->name('exam-series.index');
Route::get('/exam-series/create', [ExamSeriesController::class, 'create'])->name('exam-series.create');
Route::post('/exam-series', [ExamSeriesController::class, 'store'])->name('exam-series.store');
Route::get('/exam-series/{examSeries}/edit', [ExamSeriesController::class, 'edit'])->name('exam-series.edit');
Route::put('/exam-series/{examSeries}', [ExamSeriesController::class, 'update'])->name('exam-series.update');
Route::delete('/exam-series/{examSeries}', [ExamSeriesController::class, 'destroy'])->name('exam-series.destroy');

// ============== STUDENT ENTRIES ==============
Route::get('/student-entries', function() {
    return redirect()->route('exam-series.index');
})->name('student-entries.index');
Route::get('/student-entries/{examSeries}', [StudentEntryController::class, 'show'])->name('student-entries.show');
Route::post('/student-entries/{examSeries}/upload', [StudentEntryController::class, 'upload'])->name('student-entries.upload');
Route::post('/student-entries/{examSeries}/unenroll/{candidate}', [StudentEntryController::class, 'unenroll'])->name('student-entries.unenroll');
Route::post('/student-entries/{examSeries}/toggle-subject', [StudentEntryController::class, 'toggleSubject'])->name('student-entries.toggle-subject');
Route::post('/student-entries/{examSeries}/bulk-update', [StudentEntryController::class, 'updateBulkEntries'])->name('student-entries.bulk-update');
Route::post('/student-entries/{examSeries}/add-candidate', [StudentEntryController::class, 'addCandidate'])->name('student-entries.add-candidate');

// ============== MANUAL RESULTS ENTRY ==============
Route::get('/manual-results', [ManualResultsController::class, 'index'])->name('manual-results.index');
Route::get('/manual-results/{examSeries}/{subject}', [ManualResultsController::class, 'show'])->name('manual-results.show');
Route::post('/manual-results/{examSeries}/{subject}/save-row', [ManualResultsController::class, 'saveRow'])->name('manual-results.save-row');

// API helpers for cascading dropdowns
Route::get('/api/manual-results/months', [ManualResultsController::class, 'apiMonths'])->name('api.manual-results.months');
Route::get('/api/manual-results/subjects/{examSeries}', [ManualResultsController::class, 'apiSubjects'])->name('api.manual-results.subjects');

// ============== RESULTS ==============
Route::get('/results', [ResultsController::class, 'index'])
    ->name('results.index');
Route::get('/results/upload', [ResultsController::class, 'showUpload'])
    ->name('results.upload');
Route::post('/results/upload', [ResultsController::class, 'storeUpload'])
    ->name('results.store-upload');
Route::get('/results/view', [ResultsController::class, 'view'])
    ->name('results.view');
Route::get('/results/broadsheet', [ResultsController::class, 'broadsheet'])
    ->name('results.broadsheet');
Route::get('/results/broadsheet/{series}/{qualification}', [ResultsController::class, 'broadsheetDetail'])
    ->name('results.broadsheet.detail');
Route::get('/results/broadsheet/{series}/{qualification}/export', [ResultsController::class, 'broadsheetExport'])
    ->name('results.broadsheet.export');
Route::get('/results/{result}', [ResultsController::class, 'show'])
    ->name('results.show');
Route::get('/results/{result}/edit-components', [ResultsController::class, 'editComponents'])
    ->name('results.edit-components');
Route::post('/results/{result}/store-component', [ResultsController::class, 'storeComponent'])
    ->name('results.store-component');
Route::delete('/results/{result}', [ResultsController::class, 'destroy'])
    ->name('results.destroy');
Route::get('/results/subject/{examSeries}/{subject}', [ResultsController::class, 'subjectResults'])
    ->name('results.subject-results');
Route::delete('/results/subject/{examSeries}/{subject}', [ResultsController::class, 'destroySubjectResults'])
    ->name('results.destroy-subject');
Route::get('/results/series/{examSeries}', [ResultsController::class, 'seriesDetails'])
    ->name('results.series-details');

// Legacy / uploads endpoints for AJAX compatibility
Route::get('/api/years/{qualification_id}', [ResultUploadController::class, 'getYears'])->name('api.years');
Route::get('/api/months', [ResultUploadController::class, 'getMonths'])->name('api.months');
Route::get('/api/series', [ResultUploadController::class, 'getSeries'])->name('api.series');
Route::get('/api/subjects/{qualification_id}', [ResultUploadController::class, 'getSubjects'])->name('api.subjects');

// ============== ANALYSIS ==============
Route::get('/analysis/student-wise', [StudentAnalysisController::class, 'studentWise'])
    ->name('analysis.student-wise');
Route::get('/analysis/subject-wise', [AnalysisController::class, 'subjectWise'])
    ->name('analysis.subject-wise');
Route::get('/api/analysis/yearly-pum-trends', [AnalysisController::class, 'yearlyPumTrends'])
    ->name('api.analysis.yearly-pum-trends');
Route::get('/analysis/component-marks', [AnalysisController::class, 'componentMarks'])
    ->name('analysis.component-marks');
Route::get('/analysis/grade-threshold', [AnalysisController::class, 'gradeThreshold'])
    ->name('analysis.grade-threshold');
Route::get('/analysis/trends', [AnalysisController::class, 'trends'])
    ->name('analysis.trends');
Route::get('/analysis/student-journey', [AnalysisController::class, 'studentJourney'])
    ->name('analysis.student-journey');
Route::get('/analysis/student-journey/export-pdf', [AnalysisController::class, 'studentJourneyPdf'])
    ->name('analysis.student-journey.pdf');
Route::get('/analysis/student-journey/preview', [AnalysisController::class, 'studentJourneyPreview'])
    ->name('analysis.student-journey.preview');

// ============== SETTINGS ==============
Route::get('/settings', [SettingsController::class, 'index'])
    ->name('settings.index');

// Legacy Students Routes
Route::get('/students', [StudentController::class, 'index'])->name('students.index');
Route::get('/students/search', [StudentController::class, 'search'])->name('students.search');
Route::get('/students/{candidate}', [StudentController::class, 'show'])->name('students.show');
Route::get('/students/{candidate}/edit', [StudentController::class, 'edit'])->name('students.edit');
Route::put('/students/{candidate}', [StudentController::class, 'update'])->name('students.update');

// ============== LEGACY & ADMIN COMPATIBILITY ROUTES ==============
// Admin routes
Route::get('/admin', [App\Http\Controllers\AdminController::class, 'index'])->name('admin.index');
Route::post('/admin/qualifications', [App\Http\Controllers\AdminController::class, 'storeQualification'])->name('admin.qualifications.store');
Route::put('/admin/qualifications/{qualification}', [App\Http\Controllers\AdminController::class, 'updateQualification'])->name('admin.qualifications.update');
Route::delete('/admin/qualifications/{qualification}', [App\Http\Controllers\AdminController::class, 'destroyQualification'])->name('admin.qualifications.destroy');
Route::post('/admin/subjects', [App\Http\Controllers\AdminController::class, 'storeSubject'])->name('admin.subjects.store');
Route::put('/admin/subjects/{subject}', [App\Http\Controllers\AdminController::class, 'updateSubject'])->name('admin.subjects.update');
Route::delete('/admin/subjects/{subject}', [App\Http\Controllers\AdminController::class, 'destroySubject'])->name('admin.subjects.destroy');
Route::post('/admin/components', [App\Http\Controllers\AdminController::class, 'storeComponent'])->name('admin.components.store');
Route::put('/admin/components/{component}', [App\Http\Controllers\AdminController::class, 'updateComponent'])->name('admin.components.update');
Route::delete('/admin/components/{component}', [App\Http\Controllers\AdminController::class, 'destroyComponent'])->name('admin.components.destroy');

// Upload routes
Route::get('/uploads/history', [App\Http\Controllers\UploadController::class, 'uploadHistory'])->name('uploads.history');
Route::get('/uploads/candidates', [App\Http\Controllers\UploadController::class, 'showCandidatesUpload'])->name('uploads.candidates');
Route::post('/uploads/candidates', [App\Http\Controllers\UploadController::class, 'storeCandidatesUpload'])->name('uploads.candidates.store');
Route::post('/uploads/marks', [App\Http\Controllers\UploadController::class, 'storeMarksUpload'])->name('uploads.marks.store');
Route::post('/uploads/thresholds', [App\Http\Controllers\UploadController::class, 'storeThresholdsUpload'])->name('uploads.thresholds.store');
Route::post('/uploads/results', [App\Http\Controllers\ResultUploadController::class, 'storeUploadResult'])->name('uploads.results.store');

Route::get('/uploads/components', [App\Http\Controllers\ComponentMarksUploadController::class, 'show'])->name('uploads.components');
Route::post('/uploads/components', [App\Http\Controllers\ComponentMarksUploadController::class, 'store'])->name('uploads.components.store');

// AI Component Marks Importer
Route::get('/uploads/ai-components', [App\Http\Controllers\AiComponentImportController::class, 'showUploadForm'])->name('uploads.ai_components');
Route::post('/uploads/ai-components/preview', [App\Http\Controllers\AiComponentImportController::class, 'processUploadPreview'])->name('uploads.ai_components.preview');
Route::post('/uploads/ai-components/confirm', [App\Http\Controllers\AiComponentImportController::class, 'confirmImport'])->name('uploads.ai_components.confirm');

// AI-Assisted Broadsheet Importer routes
Route::get('/uploads/ai-importer', [AiImportController::class, 'showUploadForm'])->name('uploads.ai_importer');
Route::post('/uploads/ai-importer/preview', [AiImportController::class, 'processUploadPreview'])->name('uploads.ai_importer.preview');
Route::post('/uploads/ai-importer/confirm', [AiImportController::class, 'confirmImport'])->name('uploads.ai_importer.confirm');

// Analytics routes
Route::get('/analytics/yearly', [App\Http\Controllers\AnalyticsController::class, 'yearly'])->name('analytics.yearly');
Route::get('/analytics/export', [App\Http\Controllers\AnalyticsController::class, 'export'])->name('analytics.export');
Route::get('/analytics/yoy', [App\Http\Controllers\AnalyticsController::class, 'yoyComparison'])->name('analytics.yoy');

// Dummy register route
Route::get('/register', function () {
    return redirect()->route('dashboard');
})->name('register');

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/cbse-insights', function () {
    return view('cbse-insights');
})->name('cbse-insights');

// ============== CBSE MODULE ==============
use App\Http\Controllers\Cbse\CbseDashboardController;
use App\Http\Controllers\Cbse\CbseQualificationController;
use App\Http\Controllers\Cbse\CbseSubjectController;
use App\Http\Controllers\Cbse\CbseStudentController;
use App\Http\Controllers\Cbse\CbseResultController;
use App\Http\Controllers\Cbse\CbseAnalysisController;

Route::prefix('cbse')->name('cbse.')->group(function () {
    Route::get('/dashboard', [CbseDashboardController::class, 'index'])->name('dashboard');
    
    // Qualifications
    Route::get('/qualifications', [CbseQualificationController::class, 'index'])->name('qualifications.index');
    Route::get('/qualifications/{qualification}', [CbseQualificationController::class, 'show'])->name('qualifications.show');

    // Subjects
    Route::resource('subjects', CbseSubjectController::class);

    // Academic Years
    Route::resource('academic-years', \App\Http\Controllers\Cbse\CbseAcademicYearController::class);

    // Student Entries (Manage Enrollments inside Academic Years)
    Route::get('/student-entries/{academicYear}', [\App\Http\Controllers\Cbse\CbseStudentEntryController::class, 'show'])->name('student-entries.show');
    Route::post('/student-entries/{academicYear}/add-student', [\App\Http\Controllers\Cbse\CbseStudentEntryController::class, 'addStudent'])->name('student-entries.add-student');
    Route::post('/student-entries/{academicYear}/toggle-subject', [\App\Http\Controllers\Cbse\CbseStudentEntryController::class, 'toggleSubject'])->name('student-entries.toggle-subject');
    Route::post('/student-entries/{academicYear}/update-roll-number', [\App\Http\Controllers\Cbse\CbseStudentEntryController::class, 'updateRollNumber'])->name('student-entries.update-roll-number');
    Route::post('/student-entries/{academicYear}/bulk-update', [\App\Http\Controllers\Cbse\CbseStudentEntryController::class, 'updateBulkEntries'])->name('student-entries.bulk-update');

    // Students (Show only, since managing is via entries grid)
    Route::get('/students/{student}', [CbseStudentController::class, 'show'])->name('students.show');

    // Results
    Route::get('/results/upload', [CbseResultController::class, 'showUpload'])->name('results.upload');
    Route::post('/results/upload', [CbseResultController::class, 'storeUpload'])->name('results.store-upload');
    Route::post('/results/save-row', [CbseResultController::class, 'saveRow'])->name('results.save-row');
    Route::get('/results/year/{academicYear}', [CbseResultController::class, 'academicYearDetails'])->name('results.year-details');
    Route::get('/results/subject/{academicYear}/{subject}', [CbseResultController::class, 'subjectDetails'])->name('results.subject-details');
    Route::delete('/results/subject/{academicYear}/{subject}', [CbseResultController::class, 'destroySubjectResults'])->name('results.destroy-subject');
    Route::resource('results', CbseResultController::class);

    // Analysis
    Route::get('/analysis/subject-wise', [CbseAnalysisController::class, 'subjectWise'])->name('analysis.subject-wise');
    Route::get('/analysis/student-journey', [CbseAnalysisController::class, 'studentJourney'])->name('analysis.student-journey');
    Route::get('/analysis/broadsheet', [CbseAnalysisController::class, 'broadsheet'])->name('analysis.broadsheet');
});


