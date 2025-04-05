<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;

class AdminExamController extends Controller
{
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
} 