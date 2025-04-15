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
        try {
            $user = Auth::user();
            $role = strtolower($user->role);
            
            \Log::info('Fetching notifications for user: ' . $user->name . ' (ID: ' . $user->id . ')');
            
            // Start with personal notifications
            $query = Notification::where('receiver_id', $user->id);
            
            // Add role-based notifications
            $query->orWhere(function($q) use ($role) {
                $q->where('receiver_type', 'role')
                  ->where('role', $role);
            });
            
            // Add group-based notifications if applicable
            if ($user->group_id) {
                $query->orWhere(function($q) use ($user) {
                    $q->where('receiver_type', 'group')
                      ->where('group_id', $user->group_id);
                });
            }
            
            // Get the notifications
            $notifications = $query->orderBy('created_at', 'desc')
                ->paginate(10);
                
            \Log::info('Found ' . $notifications->count() . ' notifications for user');
            
            // Count unread notifications
            $unread_count = Notification::where('receiver_id', $user->id)
                ->whereNull('read_at')
            ->count();
            
            // Return the appropriate view based on user role
            if ($user->role === 'Admin') {
                return view('admin.notifications.index', compact('notifications', 'unread_count'));
            } elseif ($user->role === 'Teacher') {
                return view('teacher.notifications.index', compact('notifications', 'unread_count'));
            } else {
                return view('student.notifications.index', compact('notifications', 'unread_count'));
            }
        } catch (\Exception $e) {
            \Log::error('Error retrieving notifications: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return back()->with('error', 'حدث خطأ أثناء تحميل الإشعارات');
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
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'receiver_type' => 'required|in:user,group,role',
                'notification_type' => 'required|in:general,academic,announcement,exam',
            ]);
            
        $user = Auth::user();
            \Log::info('Notification creation attempt by: ' . $user->name . ' (ID: ' . $user->id . ')');
            \Log::info('Notification data: ', $request->all());
            
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
                
                $notification = Notification::create($notificationData);
                \Log::info('Created user notification with ID: ' . $notification->id);
                
            } elseif ($request->receiver_type === 'group') {
                $request->validate(['group_id' => 'required|exists:groups,id']);
                $notificationData['group_id'] = $request->group_id;
                
                // Get all students in the group and create individual notifications
                $group = \App\Models\Group::findOrFail($request->group_id);
                
                if (method_exists($group, 'students') && $group->students->count() > 0) {
                    foreach ($group->students as $student) {
                        $studentNotification = $notificationData;
                        $studentNotification['receiver_id'] = $student->id;
                        Notification::create($studentNotification);
                    }
                    \Log::info('Created group notifications for group ID: ' . $request->group_id);
                } else {
                    // Still create the group notification even if no students
                    $notification = Notification::create($notificationData);
                    \Log::info('Created empty group notification with ID: ' . $notification->id);
                }
                
            } elseif ($request->receiver_type === 'role') {
                $request->validate(['role' => 'required|in:admin,teacher,student']);
                $notificationData['role'] = $request->role;
                
                // First create the role notification 
                $notification = Notification::create($notificationData);
                \Log::info('Created role notification with ID: ' . $notification->id);
                
                // Then create individual notifications for each user in that role
                $roleUsers = \App\Models\User::whereHas('roles', function($query) use ($request) {
                    $query->where('name', ucfirst($request->role));
                })->get();
                
                if ($roleUsers->count() > 0) {
                    foreach ($roleUsers as $roleUser) {
                        $userNotification = $notificationData;
                        $userNotification['receiver_id'] = $roleUser->id;
                        Notification::create($userNotification);
                    }
                    \Log::info('Created individual notifications for ' . $roleUsers->count() . ' users with role: ' . $request->role);
                }
            }
            
            \Log::info('Notification creation successful');
            return redirect()->back()->with('success', 'تم إرسال الإشعار بنجاح');
            
        } catch (\Exception $e) {
            \Log::error('Error creating notification: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إرسال الإشعار: ' . $e->getMessage())
                ->withInput();
        }
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
        
        // Only delete if user is admin or the notification's sender or receiver
        if ($user->role === 'Admin' || $notification->sender_id === $user->id || $notification->receiver_id === $user->id) {
            $notification->delete();
            return redirect()->back()->with('success', 'تم حذف الإشعار بنجاح');
        }
        
        return redirect()->back()->with('error', 'غير مصرح بحذف هذا الإشعار');
    }

    /**
     * Display a listing of the trashed notifications.
     *
     * @return \Illuminate\Http\Response
     */
    public function trash()
    {
        $user = Auth::user();
        $trashedNotifications = Notification::onlyTrashed()
            ->where('receiver_id', $user->id)
            ->orderBy('deleted_at', 'desc')
            ->get();
        
        return response()->json($trashedNotifications);
    }
    
    /**
     * Restore the specified notification from trash.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        $notification = Notification::onlyTrashed()->findOrFail($id);
        $user = Auth::user();
        
        // Only restore if user is the notification's receiver
        if ($notification->receiver_id === $user->id) {
            $notification->restore();
            return response()->json(['success' => true, 'message' => 'تم استعادة الإشعار بنجاح']);
        }
        
        return response()->json(['success' => false, 'message' => 'غير مصرح باستعادة هذا الإشعار']);
    }
    
    /**
     * Permanently delete the specified notification.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function forceDelete($id)
    {
        $notification = Notification::onlyTrashed()->findOrFail($id);
        $user = Auth::user();
        
        // Only force delete if user is the notification's receiver
        if ($notification->receiver_id === $user->id) {
            $notification->forceDelete();
            return response()->json(['success' => true, 'message' => 'تم حذف الإشعار نهائيًا']);
        }
        
        return response()->json(['success' => false, 'message' => 'غير مصرح بحذف هذا الإشعار نهائيًا']);
    }
}
