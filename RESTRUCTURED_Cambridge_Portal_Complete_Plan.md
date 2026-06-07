# Cambridge Exam Portal - RESTRUCTURED PLAN
## Complete New Information Architecture & UI Flow

**Updated:** May 24, 2026  
**Change Type:** Major UI/UX Restructure  
**Focus:** Menu-driven navigation with qualification-subject tabs, tile summaries, and detailed analysis modules

---

## EXECUTIVE SUMMARY

The portal is now structured around a **left-sidebar navigation** with:
1. **Qualifications Module** - Manage qualifications with subject tabs showing results summaries
2. **Results Module** - Upload & view results
3. **Analysis Module** - 6 different analysis views
4. **Fixed Lists** - Series (March, June, November) and Years (2018-2020) are system-wide

---

## 1. NEW MENU STRUCTURE (LEFT SIDEBAR)

```
┌─────────────────────────────────────────┐
│    CAMBRIDGE EXAM PORTAL                │
│    [School Name] [Year/Series Filter]   │
├─────────────────────────────────────────┤
│                                         │
│  📋 QUALIFICATIONS                      │
│  ├─ Create/Manage Qualifications        │
│  ├─ IDCC                                │
│  │  ├─ English (Tab)                    │
│  │  ├─ Mathematics (Tab)                │
│  │  └─ Science (Tab)                    │
│  ├─ AS Level                            │
│  │  ├─ Subject 1 (Tab)                  │
│  │  └─ Subject 2 (Tab)                  │
│  └─ A Level                             │
│     ├─ Subject 1 (Tab)                  │
│     └─ Subject 2 (Tab)                  │
│                                         │
│  📊 RESULTS                             │
│  ├─ Upload Result                       │
│  └─ View Result                         │
│                                         │
│  📈 ANALYSE RESULTS                     │
│  ├─ Student-wise Analysis               │
│  ├─ Subject-wise Analysis               │
│  ├─ Component Marks Analysis            │
│  ├─ Grade Threshold Analysis            │
│  ├─ Trends Analysis                     │
│  └─ Student Journey                     │
│                                         │
│  ⚙️ SETTINGS                            │
│  ├─ Series (Fixed: Mar, Jun, Nov)       │
│  ├─ Years (Fixed: 2018-2020)            │
│  ├─ Grade Thresholds                    │
│  └─ User Management                     │
│                                         │
└─────────────────────────────────────────┘
```

---

## 2. DETAILED PAGE LAYOUTS

### 2.1 QUALIFICATIONS PAGE

#### Layout Structure:

```
┌──────────────────────────────────────────────────────────────┐
│ QUALIFICATIONS                                               │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│ [+ Create Qualification]  [Edit]  [Delete]                 │
│                                                              │
│ ┌─ IDCC ───────────────────────────────────────────────┐   │
│ │                                                       │   │
│ │  Tabs:                                               │   │
│ │  [English] [Mathematics] [Science] [History] [+New]  │   │
│ │                                                       │   │
│ │  ┌─ ENGLISH TAB ──────────────────────────────────┐  │   │
│ │  │                                                │  │   │
│ │  │ SUBJECT INFO                                   │  │   │
│ │  │ ├─ Subject Code: ENG-001                       │  │   │
│ │  │ ├─ Subject Name: English Language              │  │   │
│ │  │ └─ Total Marks: 200                            │  │   │
│ │  │                                                │  │   │
│ │  │ PAPERS/COMPONENTS:                             │  │   │
│ │  │ ├─ Paper 1: Reading & Writing (100 marks)     │  │   │
│ │  │ ├─ Paper 2: Listening (50 marks)              │  │   │
│ │  │ └─ Paper 3: Speaking (50 marks)               │  │   │
│ │  │                                                │  │   │
│ │  │ RESULTS SUMMARY (Tile View)                    │  │   │
│ │  │ ┌──────────────────────────────────┐           │  │   │
│ │  │ │ Total Students: 150              │           │  │   │
│ │  │ ├──────────────────────────────────┤           │  │   │
│ │  │ │ A*: 25  │ A: 35  │ B: 40        │           │  │   │
│ │  │ │ C: 30   │ D: 15  │ E: 5         │           │  │   │
│ │  │ │ U: 0                             │           │  │   │
│ │  │ ├──────────────────────────────────┤           │  │   │
│ │  │ │ Avg: 72%  │ Pass Rate: 95%      │           │  │   │
│ │  │ │ Highest: 98  │ Lowest: 45      │           │  │   │
│ │  │ └──────────────────────────────────┘           │  │   │
│ │  │                                                │  │   │
│ │  │ [Edit Subject] [Edit Papers] [Upload Results] │  │   │
│ │  │                                                │  │   │
│ │  └─────────────────────────────────────────────────┘  │   │
│ │                                                       │   │
│ │  ┌─ MATHEMATICS TAB ─────────────────────────────┐   │   │
│ │  │ (Same structure as English tab)               │   │   │
│ │  └───────────────────────────────────────────────┘   │   │
│ │                                                       │   │
│ └───────────────────────────────────────────────────────┘   │
│                                                              │
│ ┌─ AS LEVEL ────────────────────────────────────────────┐   │
│ │ [Similar tab structure]                              │   │
│ └──────────────────────────────────────────────────────┘   │
│                                                              │
│ ┌─ A LEVEL ─────────────────────────────────────────────┐   │
│ │ [Similar tab structure]                              │   │
│ └──────────────────────────────────────────────────────┘   │
└──────────────────────────────────────────────────────────────┘
```

#### Tile Components:

```
Each Subject Tab Shows:

┌─────────────────────────────────────┐
│ SUBJECT INFORMATION CARD             │
├─────────────────────────────────────┤
│ English Language                     │
│ Code: ENG-001 | Total: 200 marks    │
│ [Edit] [Delete]                     │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│ PAPERS SECTION                      │
├─────────────────────────────────────┤
│ Paper 1: Reading (100)              │
│ Paper 2: Listening (50)             │
│ Paper 3: Speaking (50)              │
│ [+ Add Paper] [Edit] [Delete]       │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│ GRADE DISTRIBUTION TILES             │
├─────────────────────────────────────┤
│  A*   │  A    │  B    │  C    │  D  │
│ 25    │ 35    │ 40    │ 30    │ 15  │
│ 16.7% │ 23.3% │ 26.7% │ 20%   │ 10% │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│ STATISTICS TILES                    │
├─────────────────────────────────────┤
│ Students: 150                       │
│ Pass Rate: 95%                      │
│ Avg: 72%                            │
│ Highest: 98 | Lowest: 45           │
└─────────────────────────────────────┘
```

---

### 2.2 RESULTS PAGE

#### Two Sub-Tabs:

```
┌──────────────────────────────────────────────────────────────┐
│ RESULTS                                                      │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  [Upload Result] [View Result]  ← Tabs                     │
│                                                              │
│  ┌─ UPLOAD RESULT TAB ──────────────────────────────────┐  │
│  │                                                      │  │
│  │ SELECT DETAILS:                                     │  │
│  │ ┌──────────────────────────────────────────────┐   │  │
│  │ │ Qualification: [IDCC ▼]                      │   │  │
│  │ │ Subject: [English ▼]                         │   │  │
│  │ │ Year: [2024 ▼]                               │   │  │
│  │ │ Series: [March ▼] [June] [November]          │   │  │
│  │ └──────────────────────────────────────────────┘   │  │
│  │                                                      │  │
│  │ UPLOAD DATA:                                        │  │
│  │ ┌──────────────────────────────────────────────┐   │  │
│  │ │ [Drag & Drop CSV/Excel]                      │   │  │
│  │ │                                              │   │  │
│  │ │ Format:                                      │   │  │
│  │ │ Candidate #  │ Name │ Grade │ PUM            │   │  │
│  │ │ 12345       │ John │ A     │ 85              │   │  │
│  │ │ 12346       │ Jane │ B     │ 75              │   │  │
│  │ └──────────────────────────────────────────────┘   │  │
│  │                                                      │  │
│  │ [Upload Button]                                     │  │
│  │                                                      │  │
│  │ Upload Status:                                      │  │
│  │ ✓ 145 Successful                                    │  │
│  │ ✗ 5 Failed                                          │  │
│  │                                                      │  │
│  └──────────────────────────────────────────────────────┘  │
│                                                              │
│  ┌─ VIEW RESULT TAB ────────────────────────────────────┐  │
│  │                                                      │  │
│  │ FILTERS:                                            │  │
│  │ Year: [2024 ▼] | Series: [Mar ▼] | Subj: [Eng ▼] │  │
│  │                                                      │  │
│  │ RESULTS TABLE:                                      │  │
│  │ ┌─────────────────────────────────────────────┐    │  │
│  │ │ Cand # │ Name   │ Grade │ PUM │ Status     │    │  │
│  │ │ 12345  │ John   │ A     │ 85  │ Pending    │    │  │
│  │ │ 12346  │ Jane   │ B     │ 75  │ Complete   │    │  │
│  │ │ 12347  │ Bob    │ A*    │ 92  │ Pending    │    │  │
│  │ └─────────────────────────────────────────────┘    │  │
│  │                                                      │  │
│  │ [Upload Components] [Edit] [Delete]                │  │
│  │                                                      │  │
│  └──────────────────────────────────────────────────────┘  │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

---

### 2.3 ANALYSIS PAGES

#### A. Student-wise Analysis

```
┌──────────────────────────────────────────────────────────────┐
│ STUDENT-WISE ANALYSIS                                        │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│ Search: [John Smith ____] [Search]                         │
│                                                              │
│ STUDENT PROFILE:                                            │
│ ┌────────────────────────────────────────────────────────┐  │
│ │ Name: John Smith    │ Candidate #: 12345               │  │
│ │ Enrollment: IDCC    │ Status: Active                   │  │
│ │ DOB: 15-Jan-2005   │ Gender: M                         │  │
│ └────────────────────────────────────────────────────────┘  │
│                                                              │
│ PERFORMANCE ACROSS SUBJECTS (Tile View):                   │
│ ┌─────────────────┬──────────────────┬──────────────────┐  │
│ │ ENGLISH         │ MATHEMATICS      │ SCIENCE          │  │
│ ├─────────────────┼──────────────────┼──────────────────┤  │
│ │ Grade: A        │ Grade: B         │ Grade: A         │  │
│ │ PUM: 85         │ PUM: 75          │ PUM: 88          │  │
│ │ Series: Mar24   │ Series: Mar24    │ Series: Mar24    │  │
│ │ Status: Done    │ Status: Done     │ Status: Pending  │  │
│ └─────────────────┴──────────────────┴──────────────────┘  │
│                                                              │
│ SERIES-WISE RESULTS:                                        │
│ ┌────────────────────────────────────────────────────────┐  │
│ │ March 2024:                                            │  │
│ │  English (A, 85) → Mathematics (B, 75) → Science (A, 88)│  │
│ │ June 2024:                                             │  │
│ │  English (A*, 92) → Math (A, 82) → Science (A, 90)    │  │
│ │ November 2024:                                         │  │
│ │  (Data not yet uploaded)                              │  │
│ └────────────────────────────────────────────────────────┘  │
│                                                              │
│ TREND CHART (Line Graph):                                   │
│ [Chart showing grade progression across series]            │
│                                                              │
│ [Download Report] [Print]                                  │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

#### B. Subject-wise Analysis

```
┌──────────────────────────────────────────────────────────────┐
│ SUBJECT-WISE ANALYSIS                                        │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│ FILTERS:                                                    │
│ Qualification: [IDCC ▼] | Subject: [English ▼]             │
│ Year: [2024 ▼] | Series: [Mar ▼]                          │
│                                                              │
│ SUBJECT OVERVIEW:                                           │
│ ┌────────────────────────────────────────────────────────┐  │
│ │ English Language (IDCC)                                │  │
│ │ Total Students: 150                                    │  │
│ │ Average PUM: 72%                                       │  │
│ │ Pass Rate: 95%                                         │  │
│ └────────────────────────────────────────────────────────┘  │
│                                                              │
│ GRADE DISTRIBUTION (Bar Chart):                            │
│ [Bar chart showing A*, A, B, C, D, E, U counts]           │
│                                                              │
│ DETAILED BREAKDOWN (Table):                                │
│ ┌──────────────────────────────────────────────────────┐   │
│ │ Grade │ Count │ Percentage │ Avg PUM │ Min-Max      │   │
│ │ A*    │ 25    │ 16.7%      │ 95      │ 90-100       │   │
│ │ A     │ 35    │ 23.3%      │ 85      │ 80-89        │   │
│ │ B     │ 40    │ 26.7%      │ 75      │ 70-79        │   │
│ │ C     │ 30    │ 20%        │ 60      │ 60-69        │   │
│ │ D     │ 15    │ 10%        │ 50      │ 50-59        │   │
│ │ E     │ 5     │ 3.3%       │ 40      │ 40-49        │   │
│ └──────────────────────────────────────────────────────┘   │
│                                                              │
│ TOP PERFORMERS:                                             │
│ 1. John Smith (A*, 98)                                     │
│ 2. Jane Doe (A*, 96)                                       │
│ 3. Bob Wilson (A*, 94)                                     │
│                                                              │
│ [Download Report] [Export to Excel]                        │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

#### C. Component Marks Analysis

```
┌──────────────────────────────────────────────────────────────┐
│ COMPONENT MARKS ANALYSIS                                     │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│ FILTERS:                                                    │
│ Qualification: [IDCC ▼] | Subject: [English ▼]             │
│ Year: [2024 ▼] | Series: [Mar ▼]                          │
│                                                              │
│ COMPONENT PERFORMANCE:                                      │
│ ┌────────────────────────────────────────────────────────┐  │
│ │ Paper 1: Reading & Writing (100 marks)                │  │
│ │ ├─ Avg Marks: 72/100                                  │  │
│ │ ├─ Avg %: 72%                                         │  │
│ │ ├─ Highest: 98 | Lowest: 35                           │  │
│ │ └─ Difficulty Index: Medium                           │  │
│ │                                                        │  │
│ │ Paper 2: Listening (50 marks)                         │  │
│ │ ├─ Avg Marks: 38/50                                   │  │
│ │ ├─ Avg %: 76%                                         │  │
│ │ ├─ Highest: 50 | Lowest: 20                           │  │
│ │ └─ Difficulty Index: Easy                             │  │
│ │                                                        │  │
│ │ Paper 3: Speaking (50 marks)                          │  │
│ │ ├─ Avg Marks: 35/50                                   │  │
│ │ ├─ Avg %: 70%                                         │  │
│ │ ├─ Highest: 48 | Lowest: 15                           │  │
│ │ └─ Difficulty Index: Hard                             │  │
│ └────────────────────────────────────────────────────────┘  │
│                                                              │
│ COMPONENT-WISE COMPARISON (Bar Chart):                     │
│ [Chart comparing average % across components]              │
│                                                              │
│ STUDENT PERFORMANCE IN COMPONENTS:                         │
│ ┌────────────────────────────────────────────────────────┐  │
│ │ Name         │ P1 (100) │ P2 (50) │ P3 (50) │ Total   │  │
│ │ John Smith   │ 85       │ 45      │ 42      │ 172/200 │  │
│ │ Jane Doe     │ 92       │ 48      │ 48      │ 188/200 │  │
│ │ Bob Wilson   │ 78       │ 40      │ 38      │ 156/200 │  │
│ └────────────────────────────────────────────────────────┘  │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

#### D. Grade Threshold Analysis (Series-wise)

```
┌──────────────────────────────────────────────────────────────┐
│ GRADE THRESHOLD ANALYSIS                                     │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│ FILTERS:                                                    │
│ Qualification: [IDCC ▼] | Subject: [English ▼]             │
│ Year: [2024 ▼]                                             │
│                                                              │
│ SERIES COMPARISON (March vs June vs November):             │
│                                                              │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ MARCH 2024                                              │ │
│ ├─────────────────────────────────────────────────────────┤ │
│ │ Grade A*: 90-100 PUM  │ 25 students (16.7%)            │ │
│ │ Grade A:  80-89 PUM   │ 35 students (23.3%)            │ │
│ │ Grade B:  70-79 PUM   │ 40 students (26.7%)            │ │
│ │ Grade C:  60-69 PUM   │ 30 students (20%)              │ │
│ │ Grade D:  50-59 PUM   │ 15 students (10%)              │ │
│ │ Grade E:  40-49 PUM   │ 5 students (3.3%)              │ │
│ │ Grade U:  0-39 PUM    │ 0 students (0%)                │ │
│ └─────────────────────────────────────────────────────────┘ │
│                                                              │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ JUNE 2024                                               │ │
│ ├─────────────────────────────────────────────────────────┤ │
│ │ [Similar breakdown]                                     │ │
│ └─────────────────────────────────────────────────────────┘ │
│                                                              │
│ THRESHOLD COMPARISON (Line Chart):                         │
│ [Shows how thresholds changed across series]               │
│                                                              │
│ [Edit Thresholds] [View History]                           │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

#### E. Trends Analysis

```
┌──────────────────────────────────────────────────────────────┐
│ TRENDS ANALYSIS                                              │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│ FILTERS:                                                    │
│ Qualification: [IDCC ▼] | Subject: [All ▼]                │
│ Years: [2022 - 2024]                                        │
│                                                              │
│ KEY METRICS OVER TIME:                                      │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ AVERAGE PUM TREND (Line Chart)                          │ │
│ │ 2022: 70% → 2023: 71% → 2024 Mar: 72% → 2024 Jun: 73% │ │
│ │ Trend: ↗ IMPROVING                                      │ │
│ └─────────────────────────────────────────────────────────┘ │
│                                                              │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ PASS RATE TREND (Line Chart)                            │ │
│ │ 2022: 88% → 2023: 90% → 2024 Mar: 95% → 2024 Jun: 96% │ │
│ │ Trend: ↗ IMPROVING                                      │ │
│ └─────────────────────────────────────────────────────────┘ │
│                                                              │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ GRADE DISTRIBUTION SHIFT (Stacked Bar Chart)            │ │
│ │ Shows how % of students getting each grade changes      │ │
│ │ 2022: More E's & D's → 2024: More A's & B's            │ │
│ └─────────────────────────────────────────────────────────┘ │
│                                                              │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ SUBJECT-WISE PERFORMANCE COMPARISON                     │ │
│ │ English:   70% → 72% → 73% (↗)                         │ │
│ │ Math:      65% → 68% → 72% (↗↗ Fast improving)         │ │
│ │ Science:   72% → 71% → 71% (→ Stable)                  │ │
│ │ History:   68% → 70% → 68% (↘ Declining)               │ │
│ └─────────────────────────────────────────────────────────┘ │
│                                                              │
│ KEY INSIGHTS:                                               │
│ • Overall performance improving by ~1% per series          │ │
│ • Math showing fastest improvement                         │ │
│ • History needs intervention                               │ │
│                                                              │
│ [Download Report] [Export Data]                            │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

#### F. Student Journey

```
┌──────────────────────────────────────────────────────────────┐
│ STUDENT JOURNEY ANALYSIS                                     │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│ Search: [John Smith ____] [Track]                          │
│                                                              │
│ PROGRESSION PATH: IDCC → AS Level → A Level               │
│                                                              │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ LEVEL 1: IDCC                           Status: PASSED   │ │
│ ├─────────────────────────────────────────────────────────┤ │
│ │ Mar 2024  │ English (A, 85) │ Math (B, 75) │ Sci (A, 88) │ │
│ │ Jun 2024  │ English (A*, 92) │ Math (A, 82) │ Sci (A, 90) │ │
│ │ Nov 2024  │ English (A, 88) │ Math (A, 85) │ Sci (A*, 94)│ │
│ │                                                         │ │
│ │ Summary: Strong performer, consistent grades          │ │
│ │ Recommendation: Progress to AS Level                  │ │
│ └─────────────────────────────────────────────────────────┘ │
│                                                              │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ LEVEL 2: AS Level                   Status: IN PROGRESS  │ │
│ ├─────────────────────────────────────────────────────────┤ │
│ │ Mar 2024  │ Physics (a, 82) │ Chem (b, 72)             │ │
│ │ Jun 2024  │ Physics (a, 85) │ Chem (a, 78)             │ │
│ │ Nov 2024  │ (Data not yet uploaded)                     │ │
│ │                                                         │ │
│ │ Trend: Improving in Chemistry, consistent in Physics  │ │
│ └─────────────────────────────────────────────────────────┘ │
│                                                              │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ LEVEL 3: A Level                     Status: NOT STARTED │ │
│ ├─────────────────────────────────────────────────────────┤ │
│ │ Not yet enrolled                                        │ │
│ └─────────────────────────────────────────────────────────┘ │
│                                                              │
│ PERFORMANCE TRAJECTORY (Line Chart):                       │
│ [Overall grade trend across all levels]                    │
│ Trend: Consistent high performer (A's & B's)              │
│                                                              │
│ [View Full History] [Print Report]                         │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

---

## 3. UPDATED DATABASE MODELS

### 3.1 Qualification Model (Enhanced)

```php
// app/Models/Qualification.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Qualification extends Model
{
    use HasUuids;

    protected $fillable = [
        'qualification_type',
        'qualification_name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relations
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function examSeries(): HasMany
    {
        return $this->hasMany(ExamSeries::class);
    }

    // Get subjects with their summary stats
    public function subjectsWithStats()
    {
        return $this->subjects()
            ->with(['components', 'results' => function ($query) {
                $query->latest();
            }])
            ->get()
            ->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->subject_name,
                    'code' => $subject->subject_code,
                    'components' => $subject->components,
                    'total_students' => $subject->results->count(),
                    'grade_distribution' => $this->getGradeDistribution($subject),
                    'statistics' => $this->getSubjectStats($subject),
                ];
            });
    }

    private function getGradeDistribution($subject)
    {
        return $subject->results()
            ->groupBy('grade')
            ->selectRaw('grade, COUNT(*) as count')
            ->pluck('count', 'grade')
            ->toArray();
    }

    private function getSubjectStats($subject)
    {
        $results = $subject->results;

        if ($results->isEmpty()) {
            return null;
        }

        return [
            'pass_rate' => ($results->where('is_passed', true)->count() / $results->count()) * 100,
            'avg_pum' => $results->avg('pum'),
            'highest' => $results->max('pum'),
            'lowest' => $results->min('pum'),
        ];
    }
}
```

### 3.2 Subject Model (Enhanced)

```php
// app/Models/Subject.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Subject extends Model
{
    use HasUuids;

    protected $fillable = [
        'subject_code',
        'subject_name',
        'qualification_id',
        'total_marks',
        'passing_percentage',
        'description'
    ];

    protected $casts = [
        'total_marks' => 'integer',
        'passing_percentage' => 'decimal:2',
    ];

    public function qualification()
    {
        return $this->belongsTo(Qualification::class);
    }

    public function components()
    {
        return $this->hasMany(Component::class);
    }

    public function results()
    {
        return $this->hasMany(SubjectResult::class);
    }

    // Get results with detailed breakdown
    public function resultsWithComponents($seriesId = null, $year = null, $month = null)
    {
        $query = $this->results()
            ->with(['enrollment.candidate', 'componentMarks.component'])
            ->whereHas('series', function ($q) use ($year, $month) {
                if ($year) $q->where('year', $year);
                if ($month) $q->where('month', $month);
            });

        if ($seriesId) {
            $query->where('series_id', $seriesId);
        }

        return $query->get();
    }
}
```

### 3.3 Updated SubjectResult Model

```php
// app/Models/SubjectResult.php (Key additions)

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SubjectResult extends Model
{
    use HasUuids;

    protected $fillable = [
        'enrollment_id',
        'subject_id',
        'series_id',
        'grade',
        'pum',
        'total_obtained_marks',
        'total_marks',
        'overall_percentage',
        'status',
        'remarks',
        'uploaded_by',
    ];

    protected $casts = [
        'pum' => 'decimal:2',
        'total_obtained_marks' => 'decimal:2',
        'overall_percentage' => 'decimal:2',
    ];

    public function enrollment()
    {
        return $this->belongsTo(CandidateEnrollment::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function series()
    {
        return $this->belongsTo(ExamSeries::class);
    }

    public function componentMarks()
    {
        return $this->hasMany(ComponentMarks::class);
    }

    public function candidate()
    {
        return $this->enrollment->candidate();
    }

    // Scope for analysis queries
    public static function scopeForAnalysis($query, $subjectId = null, $year = null, $month = null, $seriesId = null)
    {
        return $query->when($subjectId, function ($q) use ($subjectId) {
            $q->where('subject_id', $subjectId);
        })
        ->when($seriesId, function ($q) use ($seriesId) {
            $q->where('series_id', $seriesId);
        })
        ->when($year || $month, function ($q) use ($year, $month) {
            $q->whereHas('series', function ($sq) use ($year, $month) {
                if ($year) $sq->where('year', $year);
                if ($month) $sq->where('month', $month);
            });
        });
    }

    // Mark as passed/failed
    public function updatePassStatus()
    {
        $this->is_passed = $this->pum >= $this->subject->passing_percentage;
        $this->save();
    }
}
```

---

## 4. UPDATED CONTROLLERS

### 4.1 QualificationController (NEW)

```php
// app/Http/Controllers/QualificationController.php

<?php

namespace App\Http\Controllers;

use App\Models\Qualification;
use App\Models\Subject;
use Illuminate\Http\Request;

class QualificationController extends Controller
{
    /**
     * Show all qualifications with subjects
     */
    public function index()
    {
        $qualifications = Qualification::with(['subjects.components', 'subjects.results'])
            ->get()
            ->map(function ($qual) {
                return [
                    'id' => $qual->id,
                    'name' => $qual->qualification_name,
                    'type' => $qual->qualification_type,
                    'subjects_with_stats' => $qual->subjectsWithStats(),
                ];
            });

        return view('qualifications.index', [
            'qualifications' => $qualifications,
        ]);
    }

    /**
     * Show single qualification with tabs
     */
    public function show(Qualification $qualification)
    {
        $subjects_with_stats = $qualification->subjectsWithStats();

        return view('qualifications.show', [
            'qualification' => $qualification,
            'subjects_with_stats' => $subjects_with_stats,
        ]);
    }

    /**
     * Create new qualification
     */
    public function create()
    {
        return view('qualifications.create');
    }

    /**
     * Store qualification
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'qualification_type' => 'required|unique:qualifications|in:IDCC,AS_LEVEL,A_LEVEL',
            'qualification_name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        Qualification::create($validated);

        return redirect()->route('qualifications.index')
            ->with('success', 'Qualification created successfully');
    }

    /**
     * Edit qualification
     */
    public function edit(Qualification $qualification)
    {
        return view('qualifications.edit', ['qualification' => $qualification]);
    }

    /**
     * Update qualification
     */
    public function update(Request $request, Qualification $qualification)
    {
        $validated = $request->validate([
            'qualification_name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $qualification->update($validated);

        return redirect()->route('qualifications.show', $qualification)
            ->with('success', 'Qualification updated successfully');
    }

    /**
     * Delete qualification
     */
    public function destroy(Qualification $qualification)
    {
        $qualification->delete();

        return redirect()->route('qualifications.index')
            ->with('success', 'Qualification deleted successfully');
    }

    /**
     * Get subject details (AJAX)
     */
    public function getSubjectDetails(Request $request)
    {
        $subject = Subject::with('components', 'results')->findOrFail($request->subject_id);

        $stats = [
            'total_students' => $subject->results->count(),
            'grade_distribution' => $subject->results->groupBy('grade')->map->count(),
            'avg_pum' => $subject->results->avg('pum'),
            'pass_rate' => ($subject->results->where('is_passed', true)->count() / $subject->results->count()) * 100,
            'highest' => $subject->results->max('pum'),
            'lowest' => $subject->results->min('pum'),
        ];

        return response()->json([
            'subject' => $subject,
            'stats' => $stats,
        ]);
    }
}
```

### 4.2 StudentAnalysisController (NEW)

```php
// app/Http/Controllers/StudentAnalysisController.php

<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\SubjectResult;
use Illuminate\Http\Request;

class StudentAnalysisController extends Controller
{
    /**
     * Student-wise analysis
     */
    public function studentWise(Request $request)
    {
        $candidateNumber = $request->get('candidate_number');
        $candidateName = $request->get('candidate_name');

        if (!$candidateNumber && !$candidateName) {
            return view('analysis.student-wise', [
                'student' => null,
            ]);
        }

        $student = Candidate::where(function ($q) use ($candidateNumber, $candidateName) {
            if ($candidateNumber) {
                $q->where('candidate_number', $candidateNumber);
            }
            if ($candidateName) {
                $q->where('candidate_name', 'like', "%{$candidateName}%");
            }
        })
        ->with(['enrollments.results.subject', 'enrollments.results.series'])
        ->first();

        if (!$student) {
            return back()->with('error', 'Student not found');
        }

        // Group results by series
        $resultsByQualification = $student->enrollments
            ->groupBy('qualification_id')
            ->map(function ($enrollments) {
                return $enrollments->flatMap(function ($enrollment) {
                    return $enrollment->results;
                })->groupBy('series_id');
            });

        // Get performance trend
        $trend = $this->calculateTrend($student);

        return view('analysis.student-wise', [
            'student' => $student,
            'resultsByQualification' => $resultsByQualification,
            'trend' => $trend,
        ]);
    }

    private function calculateTrend(Candidate $student)
    {
        $results = $student->enrollments()
            ->with(['results.series'])
            ->get()
            ->flatMap->results
            ->sortBy('series.year')
            ->groupBy('series.year')
            ->map(function ($yearResults) {
                return $yearResults->avg('pum');
            });

        return $results->toArray();
    }
}
```

### 4.3 AnalysisController (NEW - Main Analysis)

```php
// app/Http/Controllers/AnalysisController.php

<?php

namespace App\Http\Controllers;

use App\Models\SubjectResult;
use App\Models\Subject;
use App\Models\ExamSeries;
use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    /**
     * Subject-wise analysis
     */
    public function subjectWise(Request $request)
    {
        $filters = $request->only(['qualification_id', 'subject_id', 'year', 'month', 'series_id']);

        $results = SubjectResult::forAnalysis(
            $filters['subject_id'] ?? null,
            $filters['year'] ?? null,
            $filters['month'] ?? null,
            $filters['series_id'] ?? null
        )
        ->with(['subject', 'series', 'enrollment.candidate'])
        ->get();

        $stats = $this->calculateSubjectStats($results);
        $gradeDistribution = $results->groupBy('grade')->map->count();

        return view('analysis.subject-wise', [
            'results' => $results,
            'stats' => $stats,
            'gradeDistribution' => $gradeDistribution,
            'subjects' => Subject::all(),
            'series' => ExamSeries::all(),
        ]);
    }

    /**
     * Component marks analysis
     */
    public function componentMarks(Request $request)
    {
        $filters = $request->only(['subject_id', 'year', 'month', 'series_id']);

        $results = SubjectResult::forAnalysis(
            $filters['subject_id'] ?? null,
            $filters['year'] ?? null,
            $filters['month'] ?? null,
            $filters['series_id'] ?? null
        )
        ->with(['subject.components', 'componentMarks.component'])
        ->get();

        $componentAnalysis = $this->analyzeComponents($results);

        return view('analysis.component-marks', [
            'componentAnalysis' => $componentAnalysis,
            'results' => $results,
        ]);
    }

    /**
     * Grade threshold analysis
     */
    public function gradeThreshold(Request $request)
    {
        $subjectId = $request->get('subject_id');
        $year = $request->get('year');

        $series = ExamSeries::where('year', $year)
            ->with('gradeThresholds')
            ->get();

        $thresholdComparison = [];
        foreach ($series as $s) {
            $results = SubjectResult::where('series_id', $s->id)
                ->when($subjectId, function ($q) use ($subjectId) {
                    $q->where('subject_id', $subjectId);
                })
                ->get()
                ->groupBy('grade')
                ->map->count();

            $thresholdComparison[$s->series_name] = $results;
        }

        return view('analysis.grade-threshold', [
            'thresholdComparison' => $thresholdComparison,
            'subjects' => Subject::all(),
        ]);
    }

    /**
     * Trends analysis
     */
    public function trends(Request $request)
    {
        $subjectId = $request->get('subject_id');
        $yearRange = $request->get('year_range', [2022, 2024]);

        $trends = $this->calculateTrends($subjectId, $yearRange);

        return view('analysis.trends', [
            'trends' => $trends,
            'subjects' => Subject::all(),
        ]);
    }

    /**
     * Student journey
     */
    public function studentJourney(Request $request)
    {
        $candidateNumber = $request->get('candidate_number');

        $student = \App\Models\Candidate::where('candidate_number', $candidateNumber)
            ->with(['enrollments.results.subject.qualification', 'enrollments.results.series'])
            ->first();

        if (!$student) {
            return back()->with('error', 'Student not found');
        }

        $journey = $this->buildStudentJourney($student);

        return view('analysis.student-journey', [
            'student' => $student,
            'journey' => $journey,
        ]);
    }

    private function calculateSubjectStats($results)
    {
        return [
            'total_students' => $results->count(),
            'avg_pum' => $results->avg('pum'),
            'highest' => $results->max('pum'),
            'lowest' => $results->min('pum'),
            'pass_rate' => ($results->where('is_passed', true)->count() / $results->count()) * 100,
            'grade_distribution' => $results->groupBy('grade')->map->count(),
        ];
    }

    private function analyzeComponents($results)
    {
        $componentAnalysis = [];

        foreach ($results as $result) {
            foreach ($result->componentMarks as $mark) {
                $componentName = $mark->component->component_name;
                
                if (!isset($componentAnalysis[$componentName])) {
                    $componentAnalysis[$componentName] = [
                        'total_marks' => $mark->component->total_marks,
                        'marks' => [],
                    ];
                }

                $componentAnalysis[$componentName]['marks'][] = [
                    'obtained' => $mark->obtained_marks,
                    'percentage' => $mark->percentage,
                ];
            }
        }

        // Calculate statistics for each component
        foreach ($componentAnalysis as &$comp) {
            $comp['avg_marks'] = array_sum(array_column($comp['marks'], 'obtained')) / count($comp['marks']);
            $comp['avg_percentage'] = array_sum(array_column($comp['marks'], 'percentage')) / count($comp['marks']);
            $comp['highest'] = max(array_column($comp['marks'], 'obtained'));
            $comp['lowest'] = min(array_column($comp['marks'], 'obtained'));
        }

        return $componentAnalysis;
    }

    private function calculateTrends($subjectId, $yearRange)
    {
        $query = SubjectResult::whereBetween('created_at', [
            now()->setYear($yearRange[0])->startOfYear(),
            now()->setYear($yearRange[1])->endOfYear(),
        ]);

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        return $query->with('series')
            ->get()
            ->groupBy('series.year')
            ->map(function ($yearResults) {
                return [
                    'avg_pum' => $yearResults->avg('pum'),
                    'pass_rate' => ($yearResults->where('is_passed', true)->count() / $yearResults->count()) * 100,
                    'total_students' => $yearResults->count(),
                ];
            })
            ->toArray();
    }

    private function buildStudentJourney($student)
    {
        $journey = [];

        $enrollments = $student->enrollments()
            ->with(['results.subject.qualification', 'results.series'])
            ->get();

        foreach ($enrollments->groupBy('qualification_id') as $qualId => $enrollmentsForQual) {
            $qualification = $enrollmentsForQual->first()->qualification;
            $results = $enrollmentsForQual->flatMap->results->sortBy('series.year');

            $journey[$qualification->qualification_name] = [
                'qualification' => $qualification,
                'results' => $results,
                'status' => $this->determineStatus($results),
                'trend' => $this->calculateTrendForJourney($results),
            ];
        }

        return $journey;
    }

    private function determineStatus($results)
    {
        if ($results->isEmpty()) {
            return 'NOT_STARTED';
        }

        $passCount = $results->where('is_passed', true)->count();
        if ($passCount === $results->count()) {
            return 'PASSED';
        } elseif ($passCount > 0) {
            return 'IN_PROGRESS';
        } else {
            return 'FAILED';
        }
    }

    private function calculateTrendForJourney($results)
    {
        return $results->pluck('pum')->toArray();
    }
}
```

---

## 5. UPDATED ROUTES

```php
// routes/web.php - NEW ROUTE STRUCTURE

Route::middleware('auth')->group(function () {
    
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

    // ============== RESULTS ==============
    
    Route::get('/results', [ResultsController::class, 'index'])
        ->name('results.index');
    Route::get('/results/upload', [ResultsController::class, 'showUpload'])
        ->name('results.upload');
    Route::post('/results/upload', [ResultsController::class, 'storeUpload'])
        ->name('results.store-upload');
    Route::get('/results/view', [ResultsController::class, 'view'])
        ->name('results.view');
    Route::get('/results/{result}', [ResultsController::class, 'show'])
        ->name('results.show');

    // ============== ANALYSIS ==============
    
    Route::get('/analysis/student-wise', [StudentAnalysisController::class, 'studentWise'])
        ->name('analysis.student-wise');
    Route::get('/analysis/subject-wise', [AnalysisController::class, 'subjectWise'])
        ->name('analysis.subject-wise');
    Route::get('/analysis/component-marks', [AnalysisController::class, 'componentMarks'])
        ->name('analysis.component-marks');
    Route::get('/analysis/grade-threshold', [AnalysisController::class, 'gradeThreshold'])
        ->name('analysis.grade-threshold');
    Route::get('/analysis/trends', [AnalysisController::class, 'trends'])
        ->name('analysis.trends');
    Route::get('/analysis/student-journey', [AnalysisController::class, 'studentJourney'])
        ->name('analysis.student-journey');

    // ============== SETTINGS ==============
    
    Route::get('/settings', [SettingsController::class, 'index'])
        ->name('settings.index');
    Route::resource('settings/series', SeriesController::class);
    Route::resource('settings/grades', GradeThresholdController::class);
});
```

---

## 6. BLADE TEMPLATES

### 6.1 Qualification Index Template

```blade
<!-- resources/views/qualifications/index.blade.php -->

@extends('layouts.app')

@section('title', 'Qualifications')
@section('page-title', 'Manage Qualifications')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Qualifications</h1>
        <a href="{{ route('qualifications.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
            + Create Qualification
        </a>
    </div>

    <!-- Qualifications List -->
    @foreach($qualifications as $qual)
    <div class="bg-white rounded-lg shadow mb-6">
        <!-- Qualification Header -->
        <div class="border-b p-6 bg-gray-50 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold">{{ $qual['name'] }}</h2>
                <p class="text-gray-600">{{ $qual['type'] }}</p>
            </div>
            <div>
                <a href="{{ route('qualifications.show', $qual['id']) }}" class="text-blue-600 hover:underline">
                    View Details
                </a>
                <a href="{{ route('qualifications.edit', $qual['id']) }}" class="text-green-600 hover:underline ml-4">
                    Edit
                </a>
            </div>
        </div>

        <!-- Subjects as Tabs -->
        <div class="p-6">
            @if($qual['subjects_with_stats']->isEmpty())
                <p class="text-gray-500">No subjects yet</p>
            @else
                <div class="space-y-4">
                    @foreach($qual['subjects_with_stats'] as $subject)
                    <div class="border rounded-lg p-4 hover:shadow-md transition">
                        <!-- Subject Header -->
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-bold">{{ $subject['name'] }}</h3>
                                <p class="text-sm text-gray-600">Code: {{ $subject['code'] }}</p>
                            </div>
                            <button class="text-blue-600 hover:underline toggle-subject" data-subject-id="{{ $subject['id'] }}">
                                Toggle Details
                            </button>
                        </div>

                        <!-- Components Info -->
                        <div class="mb-4 text-sm text-gray-600">
                            <p>Papers: 
                                @foreach($subject['components'] as $comp)
                                    {{ $comp->component_name }} ({{ $comp->total_marks }}){{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            </p>
                        </div>

                        <!-- Stats Tiles -->
                        @if($subject['statistics'])
                        <div class="grid grid-cols-4 gap-4 subject-details" style="display: none;">
                            <div class="bg-blue-50 p-3 rounded">
                                <p class="text-sm text-gray-600">Total Students</p>
                                <p class="text-2xl font-bold">{{ $subject['total_students'] }}</p>
                            </div>
                            <div class="bg-green-50 p-3 rounded">
                                <p class="text-sm text-gray-600">Pass Rate</p>
                                <p class="text-2xl font-bold">{{ round($subject['statistics']['pass_rate'], 1) }}%</p>
                            </div>
                            <div class="bg-yellow-50 p-3 rounded">
                                <p class="text-sm text-gray-600">Avg PUM</p>
                                <p class="text-2xl font-bold">{{ round($subject['statistics']['avg_pum'], 1) }}</p>
                            </div>
                            <div class="bg-purple-50 p-3 rounded">
                                <p class="text-sm text-gray-600">Range</p>
                                <p class="text-sm font-bold">{{ round($subject['statistics']['lowest'], 1) }} - {{ round($subject['statistics']['highest'], 1) }}</p>
                            </div>
                        </div>

                        <!-- Grade Distribution -->
                        <div class="subject-details mt-4" style="display: none;">
                            <p class="font-semibold mb-2">Grade Distribution:</p>
                            <div class="grid grid-cols-7 gap-2">
                                @for ($grade = 0; $grade < 7; $grade++)
                                    @php
                                        $grades = ['A*', 'A', 'B', 'C', 'D', 'E', 'U'];
                                        $gradeLabel = $grades[$grade] ?? '';
                                        $count = $subject['grade_distribution'][$gradeLabel] ?? 0;
                                    @endphp
                                    <div class="text-center p-2 bg-gray-50 rounded">
                                        <p class="font-bold">{{ $gradeLabel }}</p>
                                        <p class="text-lg">{{ $count }}</p>
                                    </div>
                                @endfor
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    @endforeach
</div>

<script>
    document.querySelectorAll('.toggle-subject').forEach(button => {
        button.addEventListener('click', function() {
            const subjectDiv = this.closest('.border');
            const details = subjectDiv.querySelectorAll('.subject-details');
            details.forEach(detail => {
                detail.style.display = detail.style.display === 'none' ? 'block' : 'none';
            });
        });
    });
</script>
@endsection
```

### 6.2 Results Page Template

```blade
<!-- resources/views/results/index.blade.php -->

@extends('layouts.app')

@section('title', 'Results')
@section('page-title', 'Results Management')

@section('content')
<div class="max-w-7xl mx-auto bg-white rounded-lg shadow">
    <!-- Tabs -->
    <div class="flex border-b">
        <button class="tab-btn active px-6 py-4 border-b-2 border-blue-600 font-semibold" data-tab="upload">
            Upload Result
        </button>
        <button class="tab-btn px-6 py-4 font-semibold text-gray-600" data-tab="view">
            View Result
        </button>
    </div>

    <!-- Tab Content -->
    <div class="p-8">
        
        <!-- UPLOAD TAB -->
        <div class="tab-content" id="upload">
            @include('results.upload')
        </div>

        <!-- VIEW TAB -->
        <div class="tab-content hidden" id="view">
            @include('results.view')
        </div>

    </div>
</div>

<script>
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tc => tc.classList.add('hidden'));
            
            // Show selected tab
            document.getElementById(tabName).classList.remove('hidden');
            
            // Update button styles
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('border-blue-600', 'text-gray-600');
                b.classList.add('text-gray-600');
            });
            this.classList.remove('text-gray-600');
            this.classList.add('border-blue-600');
        });
    });
</script>
@endsection
```

### 6.3 Sidebar Navigation Template

```blade
<!-- resources/views/layouts/sidebar.blade.php -->

<aside class="w-64 bg-gray-900 text-white h-screen overflow-y-auto">
    <!-- Logo -->
    <div class="p-6 border-b border-gray-700">
        <h1 class="text-xl font-bold">Cambridge Exam Portal</h1>
        <p class="text-sm text-gray-400 mt-2">{{ auth()->user()->school->school_name ?? 'School' }}</p>
    </div>

    <!-- Navigation -->
    <nav class="p-6 space-y-2">
        
        <!-- Qualifications -->
        <div>
            <a href="{{ route('qualifications.index') }}" 
               class="flex items-center px-4 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('qualifications.*') ? 'bg-gray-800' : '' }}">
                <span class="mr-3">📋</span>
                <span>Qualifications</span>
            </a>
        </div>

        <!-- Results -->
        <div>
            <a href="{{ route('results.index') }}" 
               class="flex items-center px-4 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('results.*') ? 'bg-gray-800' : '' }}">
                <span class="mr-3">📊</span>
                <span>Results</span>
            </a>
            <div class="ml-8 mt-1 space-y-1 {{ request()->routeIs('results.*') ? '' : 'hidden' }}">
                <a href="{{ route('results.upload') }}" class="block px-4 py-1 text-sm text-gray-400 hover:text-white">
                    Upload Result
                </a>
                <a href="{{ route('results.view') }}" class="block px-4 py-1 text-sm text-gray-400 hover:text-white">
                    View Result
                </a>
            </div>
        </div>

        <!-- Analysis -->
        <div>
            <p class="px-4 py-2 text-sm font-semibold text-gray-400 uppercase">Analysis</p>
            <a href="{{ route('analysis.student-wise') }}" 
               class="flex items-center px-4 py-2 rounded hover:bg-gray-800 text-sm {{ request()->routeIs('analysis.student-wise') ? 'bg-gray-800' : '' }}">
                <span class="mr-3">👤</span>
                <span>Student-wise</span>
            </a>
            <a href="{{ route('analysis.subject-wise') }}" 
               class="flex items-center px-4 py-2 rounded hover:bg-gray-800 text-sm {{ request()->routeIs('analysis.subject-wise') ? 'bg-gray-800' : '' }}">
                <span class="mr-3">📚</span>
                <span>Subject-wise</span>
            </a>
            <a href="{{ route('analysis.component-marks') }}" 
               class="flex items-center px-4 py-2 rounded hover:bg-gray-800 text-sm {{ request()->routeIs('analysis.component-marks') ? 'bg-gray-800' : '' }}">
                <span class="mr-3">📄</span>
                <span>Component Marks</span>
            </a>
            <a href="{{ route('analysis.grade-threshold') }}" 
               class="flex items-center px-4 py-2 rounded hover:bg-gray-800 text-sm {{ request()->routeIs('analysis.grade-threshold') ? 'bg-gray-800' : '' }}">
                <span class="mr-3">📈</span>
                <span>Grade Threshold</span>
            </a>
            <a href="{{ route('analysis.trends') }}" 
               class="flex items-center px-4 py-2 rounded hover:bg-gray-800 text-sm {{ request()->routeIs('analysis.trends') ? 'bg-gray-800' : '' }}">
                <span class="mr-3">📊</span>
                <span>Trends</span>
            </a>
            <a href="{{ route('analysis.student-journey') }}" 
               class="flex items-center px-4 py-2 rounded hover:bg-gray-800 text-sm {{ request()->routeIs('analysis.student-journey') ? 'bg-gray-800' : '' }}">
                <span class="mr-3">🛤️</span>
                <span>Student Journey</span>
            </a>
        </div>

        <!-- Settings -->
        <div class="pt-4 border-t border-gray-700 mt-4">
            <a href="{{ route('settings.index') }}" 
               class="flex items-center px-4 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('settings.*') ? 'bg-gray-800' : '' }}">
                <span class="mr-3">⚙️</span>
                <span>Settings</span>
            </a>
        </div>
    </nav>
</aside>
```

---

## 7. SYSTEM-WIDE FIXED VALUES

### Series (Always Available)
```
- March
- June
- November
```

### Years (Fixed Range)
```
- 2018
- 2019
- 2020
- 2021
- 2022
- 2023
- 2024
- 2025
```

### These are stored in `exam_series` table and dropdown populated from there

```php
// Get available years (AJAX)
Route::get('/api/years', function() {
    return response()->json([
        'years' => ExamSeries::distinct()->orderBy('year', 'desc')->pluck('year')->toArray()
    ]);
});

// Get available months (AJAX)
Route::get('/api/months', function() {
    return response()->json([
        'months' => ['March', 'June', 'November']
    ]);
});
```

---

## 8. QUICK START CHECKLIST

### Phase 1: Setup
- [ ] Create Qualifications (IDCC, AS Level, A Level)
- [ ] Create Subjects for each qualification
- [ ] Define Papers/Components for each subject
- [ ] Create Exam Series (year + month combinations)

### Phase 2: Results Entry
- [ ] Upload student results (Grade + PUM)
- [ ] View results with filters
- [ ] Add component marks (optional)

### Phase 3: Analysis
- [ ] View student-wise performance
- [ ] Analyze subject performance
- [ ] Check component marks breakdown
- [ ] Review grade thresholds
- [ ] Analyze trends
- [ ] Track student journey

---

## 9. DATABASE STRUCTURE SUMMARY

**Core Tables:**
- `qualifications` - IDCC, AS Level, A Level
- `subjects` - Subject details per qualification
- `components` - Papers/components per subject
- `exam_series` - Year + Month combinations
- `candidates` - Student information
- `candidate_enrollments` - Student enrollment
- `subject_results` - Grade + PUM results
- `component_marks` - Individual component marks
- `grade_thresholds` - Grade boundaries per series/subject

**All tables linked with proper foreign keys and indexes**

---

**This restructured plan is ready for immediate implementation by Antiggravity team.**

