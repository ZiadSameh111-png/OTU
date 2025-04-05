<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\User;
use App\Models\Group;
use App\Models\Course;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of notifications for the authenticated user.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $role = strtolower($user->role);
        
        // Get notifications for this user
        $notifications = Notification::forUser($user->id)
            ->orWhere(function($query) use ($role) {
                $query->where('receiver_type', 'role')
                      ->where('role', $role);
            });
        
        // Only include group notifications if the user has a groups relationship
        if (method_exists($user, 'groups') && $user->groups !== null) {
            $notifications = $notifications->orWhere(function($query) use ($user) {
                $query->where('receiver_type', 'group')
                      ->whereIn('group_id', $user->groups->pluck('id'));
            });
        } elseif ($user->group) {
            // If user has a single group relationship instead
            $notifications = $notifications->orWhere(function($query) use ($user) {
                $query->where('receiver_type', 'group')
                      ->where('group_id', $user->group->id);
            });
        }
        
        $notifications = $notifications->orderBy('created_at', 'desc')
            ->paginate(10);
            
        $unread_count = Notification::forUser($user->id)->unread()->count();
        
        if ($user->role === 'Admin') {
            return view('admin.notifications.index', compact('notifications', 'unread_count'));
        } elseif ($user->role === 'Teacher') {
            return view('teacher.notifications.index', compact('notifications', 'unread_count'));
        } else {
            return view('student.notifications.index', compact('notifications', 'unread_count'));
        }
    }

    /**
     * Store a newly created notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'receiver_type' => 'required|in:user,group,role',
            'notification_type' => 'required|in:general,academic,announcement,exam',
        ]);
        
        $user = Auth::user();
        
        // Create base notification data
        $notificationData = [
            'title' => $request->title,
            'description' => $request->description,
            'sender_id' => $user->id,
            'receiver_type' => $request->receiver_type,
            'notification_type' => $request->notification_type,
            'url' => $request->url,
        ];
        
        // Handle different receiver types
        if ($request->receiver_type === 'user') {
            $request->validate(['receiver_id' => 'required|exists:users,id']);
            $notificationData['receiver_id'] = $request->receiver_id;
            
            Notification::create($notificationData);
        } elseif ($request->receiver_type === 'group') {
            $request->validate(['group_id' => 'required|exists:groups,id']);
            $notificationData['group_id'] = $request->group_id;
            
            Notification::create($notificationData);
        } elseif ($request->receiver_type === 'role') {
            $request->validate(['role' => 'required|in:admin,teacher,student']);
            $notificationData['role'] = $request->role;
            
            Notification::create($notificationData);
        }
        
        return redirect()->back()->with('success', 'تم إرسال الإشعار بنجاح');
    }

    /**
     * Display the form to create a new notification.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = User::orderBy('name')->get();
        $groups = Group::orderBy('name')->get();
        
        return view('admin.notifications.create', compact('users', 'groups'));
    }

    /**
     * Mark a notification as read.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $user = Auth::user();
        
        // Check if the notification is for this user
        $isForUser = $notification->receiver_id === $user->id;
        
        // Check if the notification is for the user's role
        $isForRole = $notification->receiver_type === 'role' && 
                    $notification->role === strtolower($user->role);
        
        // Check if the notification is for the user's group
        $isForGroup = false;
        if ($notification->receiver_type === 'group') {
            if (method_exists($user, 'groups') && $user->groups !== null) {
                $isForGroup = $user->groups->contains('id', $notification->group_id);
            } elseif ($user->group) {
                $isForGroup = $user->group->id === $notification->group_id;
            }
        }
        
        // Mark as read if any condition is true
        if ($isForUser || $isForRole || $isForGroup) {
            $notification->markAsRead();
        }
        
        return redirect()->back()->with('success', 'تم تحديث حالة الإشعار');
    }

    /**
     * Mark all notifications as read for the current user.
     *
     * @return \Illuminate\Http\Response
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        $role = strtolower($user->role);
        
        // Get group IDs if available
        $userGroupIds = [];
        if (method_exists($user, 'groups') && $user->groups !== null) {
            $userGroupIds = $user->groups->pluck('id')->toArray();
        } elseif ($user->group) {
            $userGroupIds = [$user->group->id];
        }
        
        // Mark personal notifications as read
        Notification::where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
            
        // We can't mark role/group notifications read for everyone,
        // so we'll create a separate user notification record to track read status
        // This would require a more complex design in a real application
        
        return redirect()->back()->with('success', 'تم تحديث حالة جميع الإشعارات');
    }

    /**
     * Remove the specified notification.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        $user = Auth::user();
        
        // Only delete if user is admin or the notification's sender
        if ($user->role === 'Admin' || $notification->sender_id === $user->id) {
            $notification->delete();
            return redirect()->back()->with('success', 'تم حذف الإشعار بنجاح');
        }
        
        return redirect()->back()->with('error', 'غير مصرح بحذف هذا الإشعار');
    }
}
