<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();
        $notifications = Notification::where('receiver_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $notifications
        ]);
    }

    /**
     * Store a newly created notification in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'receiver_type' => 'required|in:user,role,group,all',
            'receiver_id' => 'required_if:receiver_type,user|exists:users,id',
            'role' => 'required_if:receiver_type,role|string|in:Admin,Teacher,Student',
            'group_id' => 'required_if:receiver_type,group|exists:groups,id',
            'type' => 'required|string|in:info,warning,success,error',
            'reference_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $notifications = [];

        if ($request->receiver_type == 'user') {
            // Send to specific user
            $notification = Notification::create([
                'title' => $request->title,
                'content' => $request->content,
                'receiver_id' => $request->receiver_id,
                'receiver_type' => 'user',
                'type' => $request->type,
                'reference_id' => $request->reference_id,
                'is_read' => false,
            ]);
            
            $notifications[] = $notification;
            
        } elseif ($request->receiver_type == 'role') {
            // Send to all users with specific role
            $recipients = User::whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            })->get();
            
            foreach ($recipients as $recipient) {
                $notification = Notification::create([
                    'title' => $request->title,
                    'content' => $request->content,
                    'receiver_id' => $recipient->id,
                    'receiver_type' => 'role',
                    'role' => $request->role,
                    'type' => $request->type,
                    'reference_id' => $request->reference_id,
                    'is_read' => false,
                ]);
                
                $notifications[] = $notification;
            }
            
        } elseif ($request->receiver_type == 'group') {
            // Send to all students in a group
            $group = Group::with('students')->findOrFail($request->group_id);
            
            foreach ($group->students as $student) {
                $notification = Notification::create([
                    'title' => $request->title,
                    'content' => $request->content,
                    'receiver_id' => $student->id,
                    'receiver_type' => 'group',
                    'group_id' => $group->id,
                    'type' => $request->type,
                    'reference_id' => $request->reference_id,
                    'is_read' => false,
                ]);
                
                $notifications[] = $notification;
            }
            
        } elseif ($request->receiver_type == 'all') {
            // Send to all users
            $recipients = User::all();
            
            foreach ($recipients as $recipient) {
                $notification = Notification::create([
                    'title' => $request->title,
                    'content' => $request->content,
                    'receiver_id' => $recipient->id,
                    'receiver_type' => 'all',
                    'type' => $request->type,
                    'reference_id' => $request->reference_id,
                    'is_read' => false,
                ]);
                
                $notifications[] = $notification;
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Notification(s) sent successfully',
            'data' => [
                'notifications_sent' => count($notifications),
                'notifications' => $notifications
            ]
        ], 201);
    }

    /**
     * Display the specified notification.
     *
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Notification $notification)
    {
        $user = Auth::user();
        
        if ($notification->receiver_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Mark as read if not already read
        if (!$notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $notification
        ]);
    }

    /**
     * Remove the specified notification from storage.
     *
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Notification $notification)
    {
        $user = Auth::user();
        
        if ($notification->receiver_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $notification->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification deleted successfully'
        ]);
    }

    /**
     * Get unread notifications count.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadCount()
    {
        $user = Auth::user();
        $count = Notification::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'unread_count' => $count
            ]
        ]);
    }

    /**
     * Mark notification as read.
     *
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Notification $notification)
    {
        $user = Auth::user();
        
        if ($notification->receiver_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $notification->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        Notification::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Delete all notifications.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAll()
    {
        $user = Auth::user();
        
        Notification::where('receiver_id', $user->id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications deleted successfully'
        ]);
    }
} 