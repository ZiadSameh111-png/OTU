<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * إنشاء المتحكم
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * عرض قائمة الرسائل للمستخدم الحالي
     */
    public function index()
    {
        $messages = Message::where('receiver_id', Auth::id())
                         ->orWhere('sender_id', Auth::id())
                         ->with(['sender', 'receiver'])
                         ->orderBy('created_at', 'desc')
                         ->paginate(10);
                         
        return view('messages.index', compact('messages'));
    }

    /**
     * عرض نموذج إنشاء رسالة جديدة
     */
    public function create()
    {
        $users = User::where('id', '!=', Auth::id())->get();
        return view('messages.create', compact('users'));
    }

    /**
     * حفظ رسالة جديدة
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'receiver_id' => 'required|exists:users,id',
        ]);

        $message = new Message();
        $message->subject = $request->subject;
        $message->body = $request->body;
        $message->sender_id = Auth::id();
        $message->receiver_id = $request->receiver_id;
        $message->is_read = false;
        $message->save();

        return redirect()->back()->with('success', 'تم إرسال الرسالة بنجاح');
    }

    /**
     * عرض رسالة محددة
     */
    public function show($id)
    {
        $message = Message::with(['sender', 'receiver'])->findOrFail($id);
        
        // التحقق من أن المستخدم هو مرسل أو مستقبل الرسالة
        if ($message->sender_id != Auth::id() && $message->receiver_id != Auth::id()) {
            return redirect()->route('messages.index')->with('error', 'لا يمكنك الوصول إلى هذه الرسالة');
        }
        
        // تحديث حالة القراءة إذا كان المستخدم هو المستقبل
        if ($message->receiver_id == Auth::id() && !$message->is_read) {
            $message->markAsRead();
        }
        
        return view('messages.show', compact('message'));
    }

    /**
     * حذف رسالة محددة
     */
    public function destroy($id)
    {
        $message = Message::findOrFail($id);
        
        // التحقق من أن المستخدم هو مرسل أو مستقبل الرسالة
        if ($message->sender_id != Auth::id() && $message->receiver_id != Auth::id()) {
            return redirect()->route('messages.index')->with('error', 'لا يمكنك حذف هذه الرسالة');
        }
        
        $message->delete();
        
        return redirect()->route('messages.index')->with('success', 'تم حذف الرسالة بنجاح');
    }

    /**
     * تبديل حالة النجمة للرسالة
     */
    public function toggleStar($id)
    {
        $message = Message::findOrFail($id);
        
        // التحقق من أن المستخدم هو مرسل أو مستقبل الرسالة
        if ($message->sender_id != Auth::id() && $message->receiver_id != Auth::id()) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك بهذه العملية']);
        }
        
        $message->toggleStar();
        
        return response()->json(['success' => true, 'is_starred' => $message->is_starred]);
    }

    /**
     * تحديث مجموعة من الرسائل كمقروءة
     */
    public function markAsRead(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:messages,id',
        ]);
        
        $userId = Auth::id();
        
        $messages = Message::whereIn('id', $request->message_ids)
            ->where('receiver_id', $userId)
            ->update(['is_read' => true, 'read_at' => now()]);
        
        return response()->json(['success' => true]);
    }

    /**
     * وضع نجمة على مجموعة من الرسائل
     */
    public function markAsStar(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:messages,id',
        ]);
        
        $userId = Auth::id();
        
        $messages = Message::whereIn('id', $request->message_ids)
            ->where(function($query) use ($userId) {
                $query->where('receiver_id', $userId)
                    ->orWhere('sender_id', $userId);
            })
            ->update(['is_starred' => true]);
        
        return response()->json(['success' => true]);
    }

    /**
     * حذف مجموعة من الرسائل
     */
    public function batchDelete(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:messages,id',
        ]);
        
        $userId = Auth::id();
        
        $messages = Message::whereIn('id', $request->message_ids)
            ->where(function($query) use ($userId) {
                $query->where('receiver_id', $userId)
                    ->orWhere('sender_id', $userId);
            })
            ->delete();
        
        return response()->json(['success' => true]);
    }

    /**
     * قائمة رسائل المسؤول
     */
    public function adminIndex()
    {
        $messages = Message::where('sender_id', Auth::id())
                        ->with(['sender', 'receiver'])
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);
        
        // جلب قوائم المستخدمين للإضافة في نموذج إرسال الرسائل
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'Student');
        })->get();
        
        $teachers = User::whereHas('roles', function($query) {
            $query->where('name', 'Teacher');
        })->get();
        
        $groups = \App\Models\Group::where('active', true)->get();
        
        return view('admin.messages.index', compact('messages', 'students', 'teachers', 'groups'));
    }

    /**
     * نموذج إنشاء رسالة للمسؤول
     */
    public function adminCreate()
    {
        $users = User::where('id', '!=', Auth::id())->get();
        return view('admin.messages.create', compact('users'));
    }

    /**
     * عرض رسالة محددة للمسؤول
     */
    public function adminShow($id)
    {
        $message = Message::with(['sender', 'receiver'])->findOrFail($id);
        
        // تحديث حالة القراءة إذا كان المسؤول هو المستقبل
        if ($message->receiver_id == Auth::id() && !$message->is_read) {
            $message->markAsRead();
        }
        
        return view('admin.messages.show', compact('message'));
    }

    /**
     * حفظ رسالة جديدة من المسؤول
     */
    public function adminStore(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'recipient_type' => 'required|in:student,teacher,group',
        ]);

        // تحديد المستلم بناءً على النوع
        $receiverId = null;
        
        if ($request->recipient_type == 'student') {
            $request->validate(['student_id' => 'required|exists:users,id']);
            $receiverId = $request->student_id;
        } elseif ($request->recipient_type == 'teacher') {
            $request->validate(['teacher_id' => 'required|exists:users,id']);
            $receiverId = $request->teacher_id;
        } elseif ($request->recipient_type == 'group') {
            $request->validate(['group_id' => 'required|exists:groups,id']);
            
            // إرسال لكل طلاب المجموعة
            $group = \App\Models\Group::with('students')->findOrFail($request->group_id);
            
            // إذا كانت المجموعة فارغة
            if ($group->students->isEmpty()) {
                return redirect()->back()->with('error', 'لا يوجد طلاب في هذه المجموعة');
            }
            
            // إرسال الرسالة لكل طالب في المجموعة
            foreach ($group->students as $student) {
                $message = new Message();
                $message->subject = $request->subject;
                $message->body = $request->content;
                $message->sender_id = Auth::id();
                $message->receiver_id = $student->id;
                $message->is_read = false;
                $message->save();
            }
            
            return redirect()->back()->with('success', 'تم إرسال الرسالة لجميع طلاب المجموعة بنجاح');
        }
        
        // إرسال الرسالة للمستلم الفردي
        if ($receiverId) {
            $message = new Message();
            $message->subject = $request->subject;
            $message->body = $request->content;
            $message->sender_id = Auth::id();
            $message->receiver_id = $receiverId;
            $message->is_read = false;
            $message->save();
            
            return redirect()->back()->with('success', 'تم إرسال الرسالة بنجاح');
        }
        
        return redirect()->back()->with('error', 'حدث خطأ أثناء إرسال الرسالة');
    }

    /**
     * قائمة رسائل المعلم
     */
    public function teacherIndex()
    {
        $messages = Message::where('receiver_id', Auth::id())
                        ->orWhere('sender_id', Auth::id())
                        ->with(['sender', 'receiver'])
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);
        
        $unreadCount = Message::where('receiver_id', Auth::id())
                        ->where('is_read', false)
                        ->count();
                        
        return view('teacher.messages.index', compact('messages', 'unreadCount'));
    }

    /**
     * نموذج إنشاء رسالة للمعلم
     */
    public function teacherCreate()
    {
        // الحصول على قائمة الطلاب فقط
        $students = User::role('Student')->get();
        
        return view('teacher.messages.create', compact('students'));
    }

    /**
     * عرض رسالة محددة للمعلم
     */
    public function teacherShow($id)
    {
        $message = Message::with(['sender', 'receiver'])->findOrFail($id);
        
        // التحقق من أن المعلم هو مرسل أو مستقبل الرسالة
        if ($message->sender_id != Auth::id() && $message->receiver_id != Auth::id()) {
            return redirect()->route('teacher.messages')->with('error', 'لا يمكنك الوصول إلى هذه الرسالة');
        }
        
        // تحديث حالة القراءة إذا كان المعلم هو المستقبل
        if ($message->receiver_id == Auth::id() && !$message->is_read) {
            $message->markAsRead();
        }
        
        return view('teacher.messages.show', compact('message'));
    }

    /**
     * قائمة رسائل الطالب
     */
    public function studentIndex()
    {
        $messages = Message::where('receiver_id', Auth::id())
                        ->orWhere('sender_id', Auth::id())
                        ->with(['sender', 'receiver'])
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);
        
        return view('student.messages.index', compact('messages'));
    }

    /**
     * نموذج إنشاء رسالة للطالب
     */
    public function studentCreate()
    {
        $users = User::where('id', '!=', Auth::id())->get();
        return view('student.messages.create', compact('users'));
    }

    /**
     * عرض رسالة محددة للطالب
     */
    public function studentShow($id)
    {
        $message = Message::with(['sender', 'receiver'])->findOrFail($id);
        
        // التحقق من أن الطالب هو مرسل أو مستقبل الرسالة
        if ($message->sender_id != Auth::id() && $message->receiver_id != Auth::id()) {
            return redirect()->route('student.messages')->with('error', 'لا يمكنك الوصول إلى هذه الرسالة');
        }
        
        // تحديث حالة القراءة إذا كان الطالب هو المستقبل
        if ($message->receiver_id == Auth::id() && !$message->is_read) {
            $message->markAsRead();
        }
        
        return view('student.messages.show', compact('message'));
    }

    /**
     * حفظ رسالة جديدة من المعلم
     */
    public function teacherStore(Request $request)
    {
        // تسجيل بيانات الطلب للتصحيح
        \Log::info('Teacher message submission:', $request->all());
        
        $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'recipient_id' => 'required|exists:users,id',
        ]);
        
        try {
            // إنشاء رسالة جديدة
            $message = new Message();
            $message->subject = $request->subject;
            $message->body = $request->content; // تخزين المحتوى في حقل body
            $message->sender_id = Auth::id();
            $message->receiver_id = $request->recipient_id; // تعيين receiver_id من recipient_id
            $message->is_read = false;
            $message->save();
            
            \Log::info('Message saved successfully with ID: ' . $message->id);
            
            return redirect()->route('teacher.messages')
                ->with('success', 'تم إرسال الرسالة بنجاح');
        } catch (\Exception $e) {
            // تسجيل الخطأ
            \Log::error('Error saving teacher message: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إرسال الرسالة: ' . $e->getMessage())
                ->withInput();
        }
    }
}
