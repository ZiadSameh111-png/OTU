<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Course;
use App\Models\Group;
use App\Models\User;
use App\Models\StudentExamAttempt;
use App\Models\StudentExamAnswer;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminExamController extends Controller
{
    /**
     * عرض قائمة الاختبارات للمشرف
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $exams = Exam::with(['course', 'group', 'teacher'])
            ->withCount(['attempts as total_attempts'])
            ->withCount(['attempts as submitted_count' => function($query) {
                $query->whereIn('status', ['submitted', 'graded']);
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('admin.exams.index', compact('exams'));
    }

    /**
     * حذف اختبار
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $exam = Exam::findOrFail($id);
        
        // التحقق من وجود محاولات للطلاب إذا كان الاختبار منشور
        if ($exam->is_published) {
            $attemptsCount = $exam->attempts()->count();
            if ($attemptsCount > 0) {
                return redirect()->back()->with('error', 'لا يمكن حذف الاختبار لأنه يحتوي على محاولات للطلاب. يرجى إلغاء نشر الاختبار أولاً.');
            }
        }
        
        // حذف أسئلة الاختبار
        $exam->questions()->detach();
        
        // حذف الاختبار
        $exam->delete();
        
        return redirect()->back()->with('success', 'تم حذف الاختبار بنجاح.');
    }

    /**
     * عرض تفاصيل الاختبار للمشرف
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $exam = Exam::with(['course', 'group', 'teacher', 'questions'])
            ->withCount(['attempts as total_attempts'])
            ->withCount(['attempts as submitted_count' => function($query) {
                $query->whereIn('status', ['submitted', 'graded']);
            }])
            ->findOrFail($id);
        
        return view('admin.exams.show', compact('exam'));
    }
    
    /**
     * عرض تقارير الاختبارات للمشرف
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function reports(Request $request)
    {
        // الحصول على المقررات والمجموعات للفلتر
        $courses = Course::all();
        $groups = Group::all();

        // بناء الاستعلام
        $query = Exam::with(['course', 'group', 'teacher'])
            ->withCount(['attempts as participants_count' => function($query) {
                $query->whereIn('status', ['submitted', 'graded']);
            }])
            ->whereHas('attempts', function($query) {
                $query->whereIn('status', ['submitted', 'graded']);
            });

        // تطبيق الفلاتر
        if ($request->has('course_id') && $request->course_id) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->has('group_id') && $request->group_id) {
            $query->where('group_id', $request->group_id);
        }

        // حساب متوسط وأعلى وأدنى الدرجات
        $exams = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends($request->query());

        // إضافة الإحصائيات لكل اختبار
        foreach ($exams as $exam) {
            $attempts = StudentExamAttempt::where('exam_id', $exam->id)
                ->whereIn('status', ['submitted', 'graded'])
                ->whereNotNull('total_marks_obtained')
                ->whereNotNull('total_possible_marks')
                ->where('total_possible_marks', '>', 0)
                ->get();

            if ($attempts->count() > 0) {
                $scores = $attempts->map(function($attempt) {
                    return ($attempt->total_marks_obtained / $attempt->total_possible_marks) * 100;
                });

                $exam->average_score = $scores->avg();
                $exam->highest_score = $scores->max();
                $exam->lowest_score = $scores->min();
            } else {
                $exam->average_score = 0;
                $exam->highest_score = 0;
                $exam->lowest_score = 0;
            }
        }

        // الإحصائيات العامة
        $totalExams = Exam::count();
        $activeExams = Exam::where('is_published', true)->where('is_open', true)->count();
        $totalStudents = StudentExamAttempt::whereIn('status', ['submitted', 'graded'])->distinct('student_id')->count();

        // متوسط الدرجات العام
        $avgScore = 0;
        $allAttempts = StudentExamAttempt::whereIn('status', ['submitted', 'graded'])
            ->whereNotNull('total_marks_obtained')
            ->whereNotNull('total_possible_marks')
            ->where('total_possible_marks', '>', 0)
            ->get();

        if ($allAttempts->count() > 0) {
            $avgScore = $allAttempts->sum(function($attempt) {
                return ($attempt->total_marks_obtained / $attempt->total_possible_marks) * 100;
            }) / $allAttempts->count();
        }

        // توزيع الدرجات
        $gradeDistribution = [0, 0, 0, 0, 0]; // 0-20%, 21-40%, 41-60%, 61-80%, 81-100%

        foreach ($allAttempts as $attempt) {
            $percentage = ($attempt->total_marks_obtained / $attempt->total_possible_marks) * 100;
            if ($percentage <= 20) {
                $gradeDistribution[0]++;
            } elseif ($percentage <= 40) {
                $gradeDistribution[1]++;
            } elseif ($percentage <= 60) {
                $gradeDistribution[2]++;
            } elseif ($percentage <= 80) {
                $gradeDistribution[3]++;
            } else {
                $gradeDistribution[4]++;
            }
        }

        // متوسط الدرجات حسب المقرر
        $courseData = DB::table('student_exam_attempts')
            ->join('exams', 'student_exam_attempts.exam_id', '=', 'exams.id')
            ->join('courses', 'exams.course_id', '=', 'courses.id')
            ->whereIn('student_exam_attempts.status', ['submitted', 'graded'])
            ->whereNotNull('student_exam_attempts.total_marks_obtained')
            ->whereNotNull('student_exam_attempts.total_possible_marks')
            ->where('student_exam_attempts.total_possible_marks', '>', 0)
            ->select(
                'courses.id',
                'courses.name',
                DB::raw('AVG(student_exam_attempts.total_marks_obtained / student_exam_attempts.total_possible_marks * 100) as avg_score')
            )
            ->groupBy('courses.id', 'courses.name')
            ->orderBy('avg_score', 'desc')
            ->limit(5)
            ->get();

        $courseNames = $courseData->pluck('name')->toArray();
        $courseAverages = $courseData->pluck('avg_score')->map(function($value) {
            return round($value, 1);
        })->toArray();

        // بيانات الجدول الزمني
        $timelineData = [];
        $timelineLabels = [];

        // الحصول على عدد الاختبارات شهرياً لآخر 6 أشهر
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $month = $date->format('Y-m');
            $timelineLabels[] = $date->format('M Y');
            
            $count = Exam::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
                
            $timelineData[] = $count;
        }

        // مصفوفة الإحصائيات
        $stats = [
            'total_exams' => $totalExams,
            'active_exams' => $activeExams,
            'total_students' => $totalStudents,
            'average_score' => $avgScore,
            'grade_distribution' => $gradeDistribution,
            'course_names' => $courseNames,
            'course_averages' => $courseAverages,
            'timeline_labels' => $timelineLabels,
            'timeline_data' => $timelineData
        ];

        return view('admin.exams.reports-index', compact('exams', 'courses', 'groups', 'stats'));
    }

    /**
     * عرض تقرير اختبار محدد
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function showReport($id)
    {
        $exam = Exam::with(['course', 'group', 'teacher', 'questions'])
            ->withCount(['attempts as total_attempts'])
            ->withCount(['attempts as submitted_count' => function($query) {
                $query->whereIn('status', ['submitted', 'graded']);
            }])
            ->withCount(['attempts as graded_count' => function($query) {
                $query->where('status', 'graded');
            }])
            ->findOrFail($id);
        
        // الحصول على محاولات الطلاب والنتائج
        $attempts = StudentExamAttempt::where('exam_id', $id)
            ->whereIn('status', ['submitted', 'graded'])
            ->with(['student', 'student.group'])
            ->orderBy('total_marks_obtained', 'desc')
            ->get();
        
        // حساب الإحصائيات
        $avgScore = 0;
        $maxScore = 0;
        $minScore = 0;
        $passRate = 0;
        
        if ($attempts->count() > 0) {
            $avgScore = $attempts->avg('total_marks_obtained');
            $maxScore = $attempts->max('total_marks_obtained');
            $minScore = $attempts->min('total_marks_obtained');
            $passRate = $attempts->filter(function($attempt) use ($exam) {
                // اعتبار النجاح كحصول على 60% على الأقل من مجموع الدرجات
                return ($attempt->total_marks_obtained / $attempt->total_possible_marks * 100) >= 60;
            })->count() / max(1, $attempts->count()) * 100;
        }
        
        $statistics = [
            'avg_score' => $avgScore,
            'max_score' => $maxScore,
            'min_score' => $minScore,
            'pass_rate' => $passRate,
        ];
        
        return view('admin.exams.report-show', compact('exam', 'attempts', 'statistics'));
    }

    /**
     * عرض تقرير تفصيلي للاختبار
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function reportDetail($id)
    {
        $exam = Exam::with(['course', 'group', 'teacher', 'questions', 'questions.options'])
            ->withCount(['attempts as total_attempts'])
            ->withCount(['attempts as submitted_count' => function($query) {
                $query->whereIn('status', ['submitted', 'graded']);
            }])
            ->findOrFail($id);
        
        // الحصول على إحصائيات الأسئلة
        $questionStats = [];
        foreach ($exam->questions as $question) {
            $answers = StudentExamAnswer::where('question_id', $question->id)
                ->whereHas('attempt', function($query) {
                    $query->whereIn('status', ['submitted', 'graded']);
                })
                ->get();
            
            $totalAnswers = $answers->count();
            $correctAnswers = $answers->where('is_correct', true)->count();
            $correctPercentage = $totalAnswers > 0 ? ($correctAnswers / $totalAnswers) * 100 : 0;
            
            $questionStats[$question->id] = [
                'total_answers' => $totalAnswers,
                'correct_answers' => $correctAnswers,
                'correct_percentage' => $correctPercentage,
                'difficulty_level' => $correctPercentage >= 70 ? 'سهل' : ($correctPercentage >= 40 ? 'متوسط' : 'صعب')
            ];
            
            // إحصائيات الخيارات للأسئلة متعددة الخيارات
            if ($question->type == 'multiple_choice') {
                $optionStats = [];
                foreach ($question->options as $option) {
                    $selectedCount = $answers->where('selected_option_id', $option->id)->count();
                    $selectedPercentage = $totalAnswers > 0 ? ($selectedCount / $totalAnswers) * 100 : 0;
                    
                    $optionStats[$option->id] = [
                        'selected_count' => $selectedCount,
                        'selected_percentage' => $selectedPercentage
                    ];
                }
                $questionStats[$question->id]['option_stats'] = $optionStats;
            }
        }
        
        // الحصول على توزيع الدرجات
        $attempts = StudentExamAttempt::where('exam_id', $id)
            ->whereIn('status', ['submitted', 'graded'])
            ->get();
        
        $scoreDistribution = [
            '0-20' => 0,
            '21-40' => 0,
            '41-60' => 0,
            '61-80' => 0,
            '81-100' => 0
        ];
        
        foreach ($attempts as $attempt) {
            $percentage = ($attempt->total_marks_obtained / $attempt->total_possible_marks) * 100;
            
            if ($percentage <= 20) {
                $scoreDistribution['0-20']++;
            } elseif ($percentage <= 40) {
                $scoreDistribution['21-40']++;
            } elseif ($percentage <= 60) {
                $scoreDistribution['41-60']++;
            } elseif ($percentage <= 80) {
                $scoreDistribution['61-80']++;
            } else {
                $scoreDistribution['81-100']++;
            }
        }
        
        return view('admin.exams.report-detail', compact('exam', 'questionStats', 'scoreDistribution'));
    }
} 