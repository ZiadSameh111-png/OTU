<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Group;
use App\Models\StudentExamAnswer;
use App\Models\StudentExamAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

class ExamController extends Controller
{
    /**
     * Display a listing of exams for teachers to manage.
     *
     * @return \Illuminate\View\View
     */
    public function teacherIndex()
    {
        $teacher = Auth::user();
        $exams = Exam::where('teacher_id', $teacher->id)
            ->with(['course', 'group'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('teacher.exams.index', compact('exams'));
    }

    /**
     * Show the form for creating a new exam.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $teacher = Auth::user();
        $courses = $teacher->teacherCourses()->get();
        
        // Get all groups associated with the teacher's courses
        $groupIds = [];
        foreach ($courses as $course) {
            $courseGroups = $course->groups()->pluck('groups.id')->toArray();
            $groupIds = array_merge($groupIds, $courseGroups);
        }
        
        $groups = Group::whereIn('id', $groupIds)->get();
        
        return view('teacher.exams.create', compact('courses', 'groups'));
    }

    /**
     * Store a newly created exam in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'group_id' => 'required|exists:groups,id',
            'duration' => 'required|integer|min:1',
            'question_type' => 'required|in:multiple_choice,true_false,open_ended,mixed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if the teacher is associated with the course
        $course = Course::find($request->course_id);
        if ($course->teacher_id != Auth::id()) {
            return redirect()->back()
                ->with('error', 'غير مصرح لك بإنشاء اختبار لهذا المقرر')
                ->withInput();
        }

        // Check if the group is associated with the course
        $groupExists = $course->groups()->where('groups.id', $request->group_id)->exists();
        if (!$groupExists) {
            return redirect()->back()
                ->with('error', 'المجموعة المحددة غير مرتبطة بهذا المقرر')
                ->withInput();
        }

        // Create the exam
        $exam = new Exam();
        $exam->title = $request->title;
        $exam->course_id = $request->course_id;
        $exam->group_id = $request->group_id;
        $exam->teacher_id = Auth::id();
        $exam->duration = $request->duration;
        $exam->question_type = $request->question_type;
        $exam->status = 'pending';
        $exam->is_published = false;
        $exam->is_open = false;
        $exam->save();

        return redirect()->route('teacher.exams.edit', $exam->id)
            ->with('success', 'تم إنشاء الاختبار بنجاح. يمكنك الآن إضافة الأسئلة');
    }

    /**
     * Show the form for editing an exam and adding questions.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $exam = Exam::with('questions')->findOrFail($id);
        
        // Check if the teacher is authorized to edit this exam
        if ($exam->teacher_id != Auth::id()) {
            return redirect()->route('teacher.exams.index')
                ->with('error', 'غير مصرح لك بتعديل هذا الاختبار');
        }
        
        return view('teacher.exams.edit', compact('exam'));
    }

    /**
     * Add a question to an exam.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $examId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addQuestion(Request $request, $examId)
    {
        $exam = Exam::findOrFail($examId);
        
        // Check if the teacher is authorized to edit this exam
        if ($exam->teacher_id != Auth::id()) {
            return redirect()->route('teacher.exams.index')
                ->with('error', 'غير مصرح لك بتعديل هذا الاختبار');
        }

        // Validate that the exam is not published
        if ($exam->is_published) {
            return redirect()->back()
                ->with('error', 'لا يمكن تعديل اختبار تم نشره بالفعل');
        }

        $questionType = $request->question_type;
        
        $rules = [
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,open_ended',
            'marks' => 'required|integer|min:1',
        ];
        
        // Add additional validation based on question type
        if ($questionType === 'multiple_choice') {
            $rules['options'] = 'required|array';
            $rules['options.*'] = 'required|string';
            $rules['correct_answer'] = 'required|string|in:a,b,c,d';
        } elseif ($questionType === 'true_false') {
            $rules['correct_answer'] = 'required|in:true,false';
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Create the question
        $question = new ExamQuestion();
        $question->exam_id = $examId;
        $question->question_text = $request->question_text;
        $question->question_type = $questionType;
        $question->marks = $request->marks;
        
        // Set the highest order number
        $maxOrder = ExamQuestion::where('exam_id', $examId)->max('order') ?? 0;
        $question->order = $maxOrder + 1;
        
        // Handle options and correct answer based on question type
        if ($questionType === 'multiple_choice') {
            $question->options = $request->options;
            $question->correct_answer = $request->correct_answer;
        } elseif ($questionType === 'true_false') {
            $question->correct_answer = $request->correct_answer;
        }
        
        $question->save();
        
        // Update the total marks for the exam
        $exam->total_marks = ExamQuestion::where('exam_id', $examId)->sum('marks');
        $exam->save();
        
        return redirect()->back()->with('success', 'تم إضافة السؤال بنجاح');
    }

    /**
     * Get question data for editing via AJAX.
     *
     * @param  int  $questionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuestionData($questionId)
    {
        $question = ExamQuestion::findOrFail($questionId);
        
        // Check if the teacher is authorized to edit this question
        if ($question->exam->teacher_id != Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل هذا السؤال'
            ], 403);
        }
        
        // Check if the exam is published
        if ($question->exam->is_published) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تعديل أسئلة اختبار تم نشره بالفعل'
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'question' => $question
        ]);
    }

    /**
     * Update a specific question.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $questionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateQuestion(Request $request, $questionId)
    {
        $question = ExamQuestion::findOrFail($questionId);
        $exam = $question->exam;
        
        // Check if the teacher is authorized to edit this question
        if ($exam->teacher_id != Auth::id()) {
            return redirect()->route('teacher.exams.index')
                ->with('error', 'غير مصرح لك بتعديل هذا السؤال');
        }
        
        // Check if the exam is published
        if ($exam->is_published) {
            return redirect()->back()
                ->with('error', 'لا يمكن تعديل أسئلة اختبار تم نشره بالفعل');
        }
        
        $questionType = $question->question_type; // We don't allow changing the question type
        
        $rules = [
            'question_text' => 'required|string',
            'marks' => 'required|integer|min:1',
        ];
        
        // Add additional validation based on question type
        if ($questionType === 'multiple_choice') {
            $rules['options'] = 'required|array';
            $rules['options.*'] = 'required|string';
            $rules['correct_answer'] = 'required|string|in:a,b,c,d';
        } elseif ($questionType === 'true_false') {
            $rules['correct_answer'] = 'required|in:true,false';
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Update the question
        $question->question_text = $request->question_text;
        $question->marks = $request->marks;
        
        // Handle options and correct answer based on question type
        if ($questionType === 'multiple_choice') {
            $question->options = $request->options;
            $question->correct_answer = $request->correct_answer;
        } elseif ($questionType === 'true_false') {
            $question->correct_answer = $request->correct_answer;
        }
        
        $question->save();
        
        // Update the total marks for the exam
        $exam->total_marks = ExamQuestion::where('exam_id', $exam->id)->sum('marks');
        $exam->save();
        
        return redirect()->route('teacher.exams.edit', $exam->id)
            ->with('success', 'تم تحديث السؤال بنجاح');
    }

    /**
     * Remove a question from an exam.
     *
     * @param  int  $questionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeQuestion($questionId)
    {
        $question = ExamQuestion::findOrFail($questionId);
        $exam = $question->exam;
        
        // Check if the teacher is authorized to edit this exam
        if ($exam->teacher_id != Auth::id()) {
            return redirect()->route('teacher.exams.index')
                ->with('error', 'غير مصرح لك بحذف هذا السؤال');
        }
        
        // Check if the exam is published
        if ($exam->is_published) {
            return redirect()->back()
                ->with('error', 'لا يمكن حذف أسئلة من اختبار تم نشره بالفعل');
        }
        
        // Delete the question
        $question->delete();
        
        // Update the total marks for the exam
        $exam->total_marks = ExamQuestion::where('exam_id', $exam->id)->sum('marks');
        $exam->save();
        
        // Reorder remaining questions
        $remainingQuestions = ExamQuestion::where('exam_id', $exam->id)
            ->orderBy('order')
            ->get();
            
        foreach ($remainingQuestions as $index => $q) {
            $q->order = $index + 1;
            $q->save();
        }
        
        return redirect()->back()
            ->with('success', 'تم حذف السؤال بنجاح');
    }
    
    /**
     * Clear all questions from an exam.
     *
     * @param  int  $examId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearQuestions($examId)
    {
        $exam = Exam::findOrFail($examId);
        
        // Check if the teacher is authorized to edit this exam
        if ($exam->teacher_id != Auth::id()) {
            return redirect()->route('teacher.exams.index')
                ->with('error', 'غير مصرح لك بحذف أسئلة هذا الاختبار');
        }
        
        // Check if the exam is published
        if ($exam->is_published) {
            return redirect()->back()
                ->with('error', 'لا يمكن حذف أسئلة من اختبار تم نشره بالفعل');
        }
        
        // Delete all questions
        ExamQuestion::where('exam_id', $examId)->delete();
        
        // Reset total marks
        $exam->total_marks = 0;
        $exam->save();
        
        return redirect()->back()
            ->with('success', 'تم حذف جميع الأسئلة بنجاح');
    }
    
    /**
     * Reorder questions within an exam.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $examId
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorderQuestions(Request $request, $examId)
    {
        $exam = Exam::findOrFail($examId);
        
        // Check if the teacher is authorized to edit this exam
        if ($exam->teacher_id != Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل هذا الاختبار'
            ], 403);
        }
        
        // Check if the exam is published
        if ($exam->is_published) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تعديل اختبار تم نشره بالفعل'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'questions' => 'required|array',
            'questions.*' => 'required|integer|exists:exam_questions,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'البيانات المرسلة غير صحيحة'
            ], 400);
        }
        
        // Update the order of each question
        foreach ($request->questions as $index => $questionId) {
            $question = ExamQuestion::findOrFail($questionId);
            
            // Make sure the question belongs to this exam
            if ($question->exam_id != $examId) {
                return response()->json([
                    'success' => false,
                    'message' => 'تم اكتشاف سؤال لا ينتمي لهذا الاختبار'
                ], 400);
            }
            
            $question->order = $index + 1;
            $question->save();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'تم إعادة ترتيب الأسئلة بنجاح'
        ]);
    }

    /**
     * Publish an exam to make it available to students.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function publish($id)
    {
        $exam = Exam::findOrFail($id);
        
        // Check if the teacher is authorized to publish this exam
        if ($exam->teacher_id != Auth::id()) {
            return redirect()->route('teacher.exams.index')
                ->with('error', 'غير مصرح لك بنشر هذا الاختبار');
        }
        
        // Check if the exam has questions
        if ($exam->questions()->count() == 0) {
            return redirect()->route('teacher.exams.edit', $id)
                ->with('error', 'لا يمكن نشر اختبار بدون أسئلة');
        }
        
        // Publish the exam
        $exam->is_published = true;
        $exam->save();
        
        return redirect()->route('teacher.exams.index')
            ->with('success', 'تم نشر الاختبار بنجاح. يمكنك الآن فتح الاختبار للطلاب.');
    }

    /**
     * Unpublish an exam.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unpublish($id)
    {
        $exam = Exam::findOrFail($id);
        
        // Check if the teacher is authorized to unpublish this exam
        if ($exam->teacher_id != Auth::id()) {
            return redirect()->route('teacher.exams.index')
                ->with('error', 'غير مصرح لك بإلغاء نشر هذا الاختبار');
        }
        
        // Check if any students have already attempted the exam
        if ($exam->attempts()->count() > 0) {
            return redirect()->route('teacher.exams.index')
                ->with('error', 'لا يمكن إلغاء نشر اختبار تم محاولته من قبل الطلاب');
        }
        
        // Unpublish the exam and close it
        $exam->is_published = false;
        $exam->is_open = false;
        $exam->save();
        
        return redirect()->route('teacher.exams.index')
            ->with('success', 'تم إلغاء نشر الاختبار بنجاح');
    }

    /**
     * Display a listing of exams for students.
     *
     * @return \Illuminate\View\View
     */
    public function studentIndex()
    {
        $student = Auth::user();
        $studentGroup = $student->group_id;
        
        if (!$studentGroup) {
            return view('student.exams.index', ['exams' => collect()]);
        }
        
        \Log::debug('Student Group ID: ' . $studentGroup);
        
        // Get all exams available for the student's group
        $exams = Exam::where('group_id', $studentGroup)
            ->where('is_published', true)
            ->with(['course', 'group'])
            ->orderBy('created_at', 'asc')
            ->get();
        
        \Log::debug('Found ' . $exams->count() . ' exams for student');
        
        // تحديث الحالة لكل اختبار
        foreach ($exams as $exam) {
            \Log::debug('Processing Exam ID: ' . $exam->id . ', Title: ' . $exam->title);
            
            // تخزين الحالة قبل التحديث
            $oldStatus = $exam->status;
            
            // تحديث الحالة بناءً على حالة النشر والفتح
            if (!$exam->is_published) {
                \Log::debug('  Exam is not published - setting status to pending');
                $exam->status = 'pending';
            } elseif ($exam->is_published && $exam->is_open) {
                \Log::debug('  Exam is published and open - setting status to active');
                $exam->status = 'active';
            } else {
                \Log::debug('  Exam is published but not open - setting status to completed');
                $exam->status = 'completed';
            }
            
            // حفظ الحالة المحدثة فقط إذا تغيرت
            if ($oldStatus !== $exam->status) {
                \Log::debug('  Status changed from ' . $oldStatus . ' to ' . $exam->status);
                $exam->save();
            } else {
                \Log::debug('  Status unchanged: ' . $exam->status);
            }
        }
        
        // Get the student's attempts for each exam
        $attempts = StudentExamAttempt::where('student_id', $student->id)
            ->pluck('status', 'exam_id')
            ->toArray();
        
        return view('student.exams.index', compact('exams', 'attempts'));
    }

    /**
     * Start an exam for a student.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function startExam($id)
    {
        $student = Auth::user();
        $exam = Exam::findOrFail($id);
        
        // Check if the exam is available for the student's group
        if ($exam->group_id != $student->group_id) {
            return redirect()->route('student.exams.index')
                ->with('error', 'غير مصرح لك بتقديم هذا الاختبار');
        }
        
        // Check if the exam is published and open
        if (!$exam->is_published) {
            return redirect()->route('student.exams.index')
                ->with('error', 'الاختبار غير متاح حالياً');
        }
        
        // التحقق من أن الاختبار مفتوح للطلاب
        if (!$exam->is_open) {
            return redirect()->route('student.exams.index')
                ->with('error', 'الاختبار غير متاح حالياً. يرجى الانتظار حتى يتم فتح الاختبار من قبل المدرس');
        }

        // Check if the student has already attempted the exam
        $existingAttempt = StudentExamAttempt::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->first();
        
        if ($existingAttempt) {
            // If the attempt is already submitted or graded, don't allow another attempt
            if (in_array($existingAttempt->status, ['submitted', 'graded'])) {
                return redirect()->route('student.exams.index')
                    ->with('error', 'لقد قمت بتقديم هذا الاختبار مسبقاً');
            }
            
            // If there's an expired attempt but exam is still open, update it
            if ($existingAttempt->timeRemaining() <= 0 && $exam->is_open) {
                // Reset attempt to continue
                $existingAttempt->status = 'in_progress';
                $existingAttempt->save();
            }
            
            // Continue the existing attempt
            return redirect()->route('student.exams.take', $exam->id);
        }
        
        // Create a new attempt
        $attempt = new StudentExamAttempt();
        $attempt->student_id = $student->id;
        $attempt->exam_id = $exam->id;
        $attempt->start_time = Carbon::now();
        $attempt->status = 'started';
        $attempt->save();
        
        return redirect()->route('student.exams.take', $exam->id);
    }

    /**
     * Take an exam.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function takeExam($id)
    {
        $student = Auth::user();
        $exam = Exam::with('questions')->findOrFail($id);
        
        // Check if the exam is available for the student's group
        if ($exam->group_id != $student->group_id) {
            return redirect()->route('student.exams.index')
                ->with('error', 'غير مصرح لك بتقديم هذا الاختبار');
        }
        
        // Check if the exam is published and open
        if (!$exam->is_published || !$exam->is_open) {
            return redirect()->route('student.exams.index')
                ->with('error', 'الاختبار غير متاح حالياً');
        }
        
        // Get the student's attempt
        $attempt = StudentExamAttempt::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->first();
        
        // If no attempt exists or it's already submitted/graded, redirect back
        if (!$attempt || in_array($attempt->status, ['submitted', 'graded'])) {
            return redirect()->route('student.exams.index')
                ->with('error', 'لا يمكنك تقديم هذا الاختبار');
        }
        
        // Update attempt status to in_progress
        if ($attempt->status === 'started') {
            $attempt->status = 'in_progress';
            $attempt->save();
        }
        
        // Check if the exam has ended
        if ($exam->hasEnded()) {
            $this->submitExam($id); // Auto-submit the exam
            return redirect()->route('student.exams.index')
                ->with('warning', 'انتهى وقت الاختبار وتم تقديم إجاباتك تلقائياً');
        }
        
        // Check only if the *exam duration* has run out from the student's attempt, 
        // but the exam end time hasn't been reached
        if ($attempt->timeRemaining() <= 0) {
            // تم انتهاء وقت المحاولة، نقوم بتسليم الاختبار
            $this->submitExam($id);
            return redirect()->route('student.exams.index')
                ->with('warning', 'انتهى وقت الاختبار وتم تقديم إجاباتك تلقائياً');
        } else {
            $timeRemaining = $attempt->timeRemaining();
        }
        
        // Get the student's answers for this exam
        $answers = StudentExamAnswer::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->pluck('answer', 'question_id')
            ->toArray();
            
        // مرر وقت الاختبار المتبقي إلى العرض
        $questions = $exam->questions;
        return view('student.exams.take', compact('exam', 'attempt', 'answers', 'questions', 'timeRemaining'));
    }

    /**
     * Save an answer for a question.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveAnswer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|exists:exams,id',
            'question_id' => 'required|exists:exam_questions,id',
            'answer' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        
        $student = Auth::user();
        $examId = $request->exam_id;
        $questionId = $request->question_id;
        $answer = $request->answer;
        
        // Get the student's attempt
        $attempt = StudentExamAttempt::where('student_id', $student->id)
            ->where('exam_id', $examId)
            ->first();
        
        // If no attempt exists or it's already submitted/graded, return error
        if (!$attempt || in_array($attempt->status, ['submitted', 'graded'])) {
            return response()->json(['error' => 'لا يمكنك تقديم إجابات لهذا الاختبار'], 400);
        }
        
        // Save or update the answer
        $examAnswer = StudentExamAnswer::updateOrCreate(
            [
                'student_id' => $student->id,
                'exam_id' => $examId,
                'question_id' => $questionId,
            ],
            [
                'answer' => $answer,
                'submitted_at' => Carbon::now(),
            ]
        );
        
        // Auto-evaluate the answer if applicable
        $examAnswer->evaluateAnswer();
        
        return response()->json(['success' => true]);
    }

    /**
     * Submit an exam.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitExam($id)
    {
        $student = Auth::user();
        $exam = Exam::findOrFail($id);
        
        // Get the student's attempt
        $attempt = StudentExamAttempt::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->first();
        
        // If no attempt exists or it's already submitted/graded, redirect back
        if (!$attempt || in_array($attempt->status, ['submitted', 'graded'])) {
            return redirect()->route('student.exams.index')
                ->with('error', 'لا يمكنك تقديم هذا الاختبار');
        }
        
        // Mark the attempt as submitted
        $attempt->markAsSubmitted();
        
        return redirect()->route('student.exams.index')
            ->with('success', 'تم تقديم الاختبار بنجاح');
    }

    /**
     * View exam results.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function viewResults($id)
    {
        $student = Auth::user();
        $exam = Exam::with('questions')->findOrFail($id);
        
        // Get the student's attempt
        $attempt = StudentExamAttempt::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->first();
        
        // If no attempt exists or it's not submitted/graded, redirect back
        if (!$attempt || !in_array($attempt->status, ['submitted', 'graded'])) {
            return redirect()->route('student.exams.results')
                ->with('error', 'لا يمكنك عرض نتائج هذا الاختبار');
        }
        
        // Get the student's answers for this exam
        $answers = StudentExamAnswer::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->get()
            ->keyBy('question_id');
        
        return view('student.exams.results', compact('exam', 'attempt', 'answers'));
    }

    /**
     * Display the student's results.
     *
     * @return \Illuminate\View\View
     */
    public function studentResults()
    {
        $student = Auth::user();
        
        try {
            // Check if the required table and columns exist
            if (Schema::hasTable('student_exam_attempts') &&
                Schema::hasColumn('student_exam_attempts', 'student_id') &&
                Schema::hasColumn('student_exam_attempts', 'status') &&
                Schema::hasColumn('student_exam_attempts', 'submit_time')) {
                
                // Get all submitted attempts for the student
                $attempts = StudentExamAttempt::where('student_id', $student->id)
                    ->whereIn('status', ['submitted', 'graded'])
                    ->with(['exam', 'exam.course'])
                    ->orderBy('submit_time', 'desc')
                    ->get();
            } else {
                $attempts = collect();
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching student exam attempts: ' . $e->getMessage());
            $attempts = collect();
        }
        
        return view('student.exams.results-index', compact('attempts'));
    }

    /**
     * Display a listing of exams for grading by teachers.
     *
     * @return \Illuminate\View\View
     */
    public function teacherGradingIndex(Request $request)
    {
        $teacher = auth()->user();
        $status = $request->input('status', 'pending');
        $courseId = $request->input('course_id');
        
        // Get courses taught by this teacher
        $courses = Course::where('teacher_id', $teacher->id)->get();
        
        $query = StudentExamAttempt::with(['exam', 'student', 'exam.questions'])
            ->whereHas('exam', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->where('status', 'submitted');
            
        if ($courseId) {
            $query->whereHas('exam', function ($q) use ($courseId) {
                $q->where('course_id', $courseId);
            });
        }
            
        if ($status !== 'all') {
            if ($status === 'pending') {
                $query->where('is_graded', false)
                    ->whereHas('exam.questions', function ($q) {
                        $q->where('question_type', 'open_ended');
                    });
            } elseif ($status === 'in_progress') {
                $query->where('is_graded', false)
                    ->whereHas('answers', function ($q) {
                        $q->whereNotNull('marks_obtained');
                    });
            } elseif ($status === 'completed') {
                $query->where('is_graded', true);
            }
        }
        
        $attempts = $query->orderBy('submit_time', 'desc')->paginate(15);
        
        // Statistics
        $stats = [
            'pending' => StudentExamAttempt::whereHas('exam', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->where('status', 'submitted')
            ->where('is_graded', false)
            ->whereHas('exam.questions', function ($q) {
                $q->where('question_type', 'open_ended');
            })
            ->count(),
            
            'in_progress' => StudentExamAttempt::whereHas('exam', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->where('status', 'submitted')
            ->where('is_graded', false)
            ->whereHas('answers', function ($q) {
                $q->whereNotNull('marks_obtained');
            })
            ->count(),
            
            'completed' => StudentExamAttempt::whereHas('exam', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->where('status', 'submitted')
            ->where('is_graded', true)
            ->count(),
        ];
        
        return view('teacher.exams.grading-index', compact('attempts', 'stats', 'courses', 'status', 'courseId'));
    }

    /**
     * Show the list of students who have submitted the exam.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function teacherGradingShow($id)
    {
        $exam = Exam::findOrFail($id);
        
        // Check if the teacher is authorized to grade this exam
        if ($exam->teacher_id != Auth::id()) {
            return redirect()->route('teacher.exams.grading')
                ->with('error', 'غير مصرح لك بتصحيح هذا الاختبار');
        }
        
        // Get all submitted attempts for this exam
        $attempts = StudentExamAttempt::where('exam_id', $id)
            ->whereIn('status', ['submitted', 'graded'])
            ->with(['student', 'student.group'])
            ->orderBy('submit_time', 'asc')
            ->get();
        
        return view('teacher.exams.grading-show', compact('exam', 'attempts'));
    }

    /**
     * Grade open-ended questions for a student's exam attempt.
     *
     * @param  int  $examId
     * @param  int  $studentId
     * @return \Illuminate\View\View
     */
    public function gradeOpenEndedQuestions($examId, $studentId)
    {
        $exam = Exam::with(['questions' => function($query) {
            $query->where('question_type', 'open_ended')->orderBy('order');
        }])->findOrFail($examId);
        
        // Check if the teacher is authorized to grade this exam
        if ($exam->teacher_id != Auth::id()) {
            return redirect()->route('teacher.exams.grading')
                ->with('error', 'غير مصرح لك بتصحيح هذا الاختبار');
        }
        
        $student = User::findOrFail($studentId);
        
        // Get the student's attempt
        $attempt = StudentExamAttempt::where('student_id', $studentId)
            ->where('exam_id', $examId)
            ->whereIn('status', ['submitted', 'graded'])
            ->firstOrFail();
        
        // Get the student's answers for open-ended questions
        $answers = StudentExamAnswer::where('student_id', $studentId)
            ->where('exam_id', $examId)
            ->whereHas('question', function($query) {
                $query->where('question_type', 'open_ended');
            })
            ->with('question')
            ->get();
        
        return view('teacher.exams.grade-open-ended', compact('exam', 'student', 'attempt', 'answers'));
    }

    /**
     * Save grades for open-ended questions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $examId
     * @param  int  $studentId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveOpenEndedGrades(Request $request, $examId, $studentId)
    {
        $exam = Exam::findOrFail($examId);
        
        // Check if the teacher is authorized to grade this exam
        if ($exam->teacher_id != Auth::id()) {
            return redirect()->route('teacher.exams.grading')
                ->with('error', 'غير مصرح لك بتصحيح هذا الاختبار');
        }
        
        // Get the student's attempt
        $attempt = StudentExamAttempt::where('student_id', $studentId)
            ->where('exam_id', $examId)
            ->whereIn('status', ['submitted', 'graded'])
            ->firstOrFail();
        
        // Validate and save grades for each answer
        $answers = $request->input('answers', []);
        foreach ($answers as $answerId => $gradeData) {
            $answer = StudentExamAnswer::findOrFail($answerId);
            
            // Ensure the answer belongs to the correct student and exam
            if ($answer->student_id != $studentId || $answer->exam_id != $examId) {
                continue;
            }
            
            // Update the grade
            $answer->marks_obtained = $gradeData['marks'] ?? 0;
            $answer->feedback = $gradeData['feedback'] ?? null;
            $answer->save();
        }
        
        // Update the attempt's total marks
        $attempt->calculateTotalMarks();
        
        // Mark the attempt as graded
        $attempt->is_graded = true;
        $attempt->graded_at = Carbon::now();
        $attempt->graded_by = Auth::id();
        $attempt->status = 'graded';
        $attempt->save();
        
        return redirect()->route('teacher.exams.grading.show', $examId)
            ->with('success', 'تم حفظ الدرجات بنجاح');
    }

    /**
     * Display a listing of exam reports for admins.
     *
     * @return \Illuminate\View\View
     */
    public function adminReportsIndex()
    {
        $courses = Course::orderBy('name')->get();
        $groups = Group::orderBy('name')->get();
        
        // Get all exams with submission statistics
        $exams = Exam::query();
        
        // Apply filters
        if (request()->has('course_id') && request('course_id')) {
            $exams->where('course_id', request('course_id'));
        }
        
        if (request()->has('group_id') && request('group_id')) {
            $exams->where('group_id', request('group_id'));
        }
        
        if (request()->has('exam_id') && request('exam_id')) {
            $exams->where('id', request('exam_id'));
        }
        
        $exams = $exams->with(['course', 'group', 'teacher'])
            ->withCount(['attempts as total_attempts'])
            ->withCount(['attempts as submitted_count' => function($query) {
                $query->whereIn('status', ['submitted', 'graded']);
            }])
            ->withCount(['attempts as graded_count' => function($query) {
                $query->where('status', 'graded');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Calculate statistics for dashboard
        $totalExams = Exam::count();
        $activeExams = Exam::where('status', 'active')->count();
        $totalStudents = StudentExamAttempt::distinct('student_id')->count('student_id');
        
        // Calculate average score
        $averageScore = StudentExamAttempt::whereIn('status', ['submitted', 'graded'])
            ->whereNotNull('total_marks_obtained')
            ->whereNotNull('total_possible_marks')
            ->where('total_possible_marks', '>', 0)
            ->select(\DB::raw('AVG(total_marks_obtained / total_possible_marks * 100) as avg_score'))
            ->first();
        $avgScore = $averageScore ? round($averageScore->avg_score, 1) : 0;
        
        // Grade distribution
        $gradeDistribution = [0, 0, 0, 0, 0]; // 0-20%, 21-40%, etc.
        
        $attempts = StudentExamAttempt::whereIn('status', ['submitted', 'graded'])
            ->whereNotNull('total_marks_obtained')
            ->whereNotNull('total_possible_marks')
            ->where('total_possible_marks', '>', 0)
            ->select('total_marks_obtained', 'total_possible_marks')
            ->get();
            
        foreach ($attempts as $attempt) {
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
        
        // Course averages
        $courseData = \DB::table('student_exam_attempts')
            ->join('exams', 'student_exam_attempts.exam_id', '=', 'exams.id')
            ->join('courses', 'exams.course_id', '=', 'courses.id')
            ->whereIn('student_exam_attempts.status', ['submitted', 'graded'])
            ->whereNotNull('student_exam_attempts.total_marks_obtained')
            ->whereNotNull('student_exam_attempts.total_possible_marks')
            ->where('student_exam_attempts.total_possible_marks', '>', 0)
            ->select(
                'courses.id',
                'courses.name',
                \DB::raw('AVG(student_exam_attempts.total_marks_obtained / student_exam_attempts.total_possible_marks * 100) as avg_score')
            )
            ->groupBy('courses.id', 'courses.name')
            ->orderBy('avg_score', 'desc')
            ->limit(5)
            ->get();
            
        $courseNames = $courseData->pluck('name')->toArray();
        $courseAverages = $courseData->pluck('avg_score')->map(function($value) {
            return round($value, 1);
        })->toArray();
        
        // Timeline data
        $timelineData = [];
        $timelineLabels = [];
        
        // Get exams count per month for the last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $month = $date->format('Y-m');
            $timelineLabels[] = $date->format('M Y');
            
            $count = Exam::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
                
            $timelineData[] = $count;
        }
        
        // Stats array
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
     * Display a specific exam report for admin.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function adminReportShow($id)
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
        
        // Get student attempts and results
        $attempts = StudentExamAttempt::where('exam_id', $id)
            ->whereIn('status', ['submitted', 'graded'])
            ->with(['student', 'student.group'])
            ->orderBy('total_marks_obtained', 'desc')
            ->get();
        
        // Calculate statistics
        $avgScore = $attempts->avg('total_marks_obtained');
        $maxScore = $attempts->max('total_marks_obtained');
        $minScore = $attempts->min('total_marks_obtained');
        $passRate = $attempts->filter(function($attempt) use ($exam) {
            // Consider passing as getting at least 60% of the total marks
            return ($attempt->total_marks_obtained / $exam->total_marks * 100) >= 60;
        })->count() / max(1, $attempts->count()) * 100;
        
        $statistics = [
            'avg_score' => $avgScore,
            'max_score' => $maxScore,
            'min_score' => $minScore,
            'pass_rate' => $passRate,
        ];
        
        return view('admin.exams.report-show', compact('exam', 'attempts', 'statistics'));
    }

    /**
     * Export exam results to Excel or PDF.
     *
     * @param  int  $id
     * @param  string  $format (excel, pdf)
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportResults($id, $format = 'excel')
    {
        $exam = Exam::with(['course', 'group', 'teacher'])->findOrFail($id);
        
        // Get student attempts and results
        $attempts = StudentExamAttempt::where('exam_id', $id)
            ->whereIn('status', ['submitted', 'graded'])
            ->with(['student', 'student.group'])
            ->orderBy('total_marks_obtained', 'desc')
            ->get();
        
        // Prepare data for export
        $exportData = [];
        foreach ($attempts as $attempt) {
            $exportData[] = [
                'student_id' => $attempt->student_id,
                'student_name' => $attempt->student->name,
                'course' => $exam->course->name,
                'exam_title' => $exam->title,
                'submit_time' => $attempt->submit_time->format('Y-m-d H:i'),
                'marks_obtained' => $attempt->total_marks_obtained,
                'total_marks' => $exam->total_marks,
                'percentage' => round(($attempt->total_marks_obtained / $exam->total_marks) * 100, 1) . '%',
            ];
        }
        
        // Export based on format
        if ($format === 'excel') {
            // Excel export functionality would go here
            // For simplicity, we're returning a JSON response
            return response()->json($exportData);
        } elseif ($format === 'pdf') {
            // PDF export functionality would go here
            // For simplicity, we're returning a JSON response
            return response()->json($exportData);
        }
        
        return redirect()->back()->with('error', 'صيغة التصدير غير صحيحة');
    }

    /**
     * View details of a student's exam attempt.
     *
     * @param  int  $examId
     * @param  int  $studentId
     * @return \Illuminate\View\View
     */
    public function viewStudentAttempt($examId, $studentId)
    {
        $exam = Exam::with('questions')->findOrFail($examId);
        $student = User::findOrFail($studentId);
        
        // Get the student's attempt
        $attempt = StudentExamAttempt::where('student_id', $studentId)
            ->where('exam_id', $examId)
            ->whereIn('status', ['submitted', 'graded'])
            ->firstOrFail();
        
        // Get the student's answers
        $answers = StudentExamAnswer::where('student_id', $studentId)
            ->where('exam_id', $examId)
            ->with('question')
            ->get()
            ->keyBy('question_id');
        
        return view('admin.exams.view-student-attempt', compact('exam', 'student', 'attempt', 'answers'));
    }

    /**
     * Display the grading interface for teachers.
     */
    public function grading(Request $request)
    {
        return $this->teacherGradingIndex($request);
    }

    /**
     * Remove the specified exam from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $exam = Exam::findOrFail($id);
        
        // التحقق من أن المدرس هو صاحب الاختبار
        if ($exam->teacher_id != Auth::id()) {
            return redirect()->route('teacher.exams.index')
                ->with('error', 'غير مصرح لك بحذف هذا الاختبار');
        }
        
        // التحقق من عدم وجود محاولات للطلاب إذا كان الاختبار منشور
        if ($exam->is_published && $exam->attempts()->count() > 0) {
            return redirect()->route('teacher.exams.index')
                ->with('error', 'لا يمكن حذف اختبار تم محاولته من قبل الطلاب');
        }
        
        // حذف أسئلة الاختبار أولاً
        $exam->questions()->delete();
        
        // حذف الاختبار
        $exam->delete();
        
        return redirect()->route('teacher.exams.index')
            ->with('success', 'تم حذف الاختبار بنجاح');
    }

    /**
     * Open an exam for students.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function openExam($id)
    {
        $exam = Exam::findOrFail($id);
        
        // Check if the teacher is authorized
        if ($exam->teacher_id != Auth::id()) {
            return redirect()->route('teacher.exams.index')
                ->with('error', 'غير مصرح لك بفتح هذا الاختبار');
        }
        
        // Check if the exam is published
        if (!$exam->is_published) {
            return redirect()->route('teacher.exams.index')
                ->with('error', 'يجب نشر الاختبار أولاً قبل فتحه للطلاب');
        }
        
        // Open the exam
        $exam->openExam();
        
        return redirect()->route('teacher.exams.index')
            ->with('success', 'تم فتح الاختبار للطلاب بنجاح');
    }
    
    /**
     * Close an exam.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function closeExam($id)
    {
        $exam = Exam::findOrFail($id);
        
        // Check if the teacher is authorized
        if ($exam->teacher_id != Auth::id()) {
            return redirect()->route('teacher.exams.index')
                ->with('error', 'غير مصرح لك بإغلاق هذا الاختبار');
        }
        
        // Close the exam
        $exam->closeExam();
        
        return redirect()->route('teacher.exams.index')
            ->with('success', 'تم إغلاق الاختبار بنجاح');
    }
}
