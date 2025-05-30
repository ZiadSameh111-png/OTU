<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use App\Models\Group;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MessageController extends Controller
{
    /**
     * Display a listing of messages.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();
        $messages = Message::where('receiver_id', $user->id)
            ->orWhere('sender_id', $user->id)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $messages
        ]);
    }

    /**
     * Store a newly created message in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'recipient_type' => 'required|in:user,role,group',
            'recipient_id' => 'required_if:recipient_type,user|exists:users,id',
            'role' => 'required_if:recipient_type,role|string|in:Admin,Teacher,Student',
            'group_id' => 'required_if:recipient_type,group|exists:groups,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $sender = Auth::user();
        $messages = [];

        if ($request->recipient_type == 'user') {
            // Send to specific user
            $message = Message::create([
                'subject' => $request->subject,
                'content' => $request->content,
                'sender_id' => $sender->id,
                'receiver_id' => $request->recipient_id,
                'receiver_type' => 'user',
                'is_read' => false,
            ]);
            
            $this->createMessageNotification($message);
            $messages[] = $message;
            
        } elseif ($request->recipient_type == 'role') {
            // Send to all users with specific role
            $recipients = User::whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            })->get();
            
            foreach ($recipients as $recipient) {
                if ($recipient->id != $sender->id) {
                    $message = Message::create([
                        'subject' => $request->subject,
                        'content' => $request->content,
                        'sender_id' => $sender->id,
                        'receiver_id' => $recipient->id,
                        'receiver_type' => 'role',
                        'role' => $request->role,
                        'is_read' => false,
                    ]);
                    
                    $this->createMessageNotification($message);
                    $messages[] = $message;
                }
            }
            
        } elseif ($request->recipient_type == 'group') {
            // Send to all students in a group
            $group = Group::with('students')->findOrFail($request->group_id);
            
            if ($group->students->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No students found in this group'
                ], 422);
            }
            
            foreach ($group->students as $student) {
                $message = Message::create([
                    'subject' => $request->subject,
                    'content' => $request->content,
                    'sender_id' => $sender->id,
                    'receiver_id' => $student->id,
                    'receiver_type' => 'group',
                    'group_id' => $group->id,
                    'is_read' => false,
                ]);
                
                $this->createMessageNotification($message);
                $messages[] = $message;
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Message(s) sent successfully',
            'data' => [
                'messages_sent' => count($messages),
                'messages' => $messages
            ]
        ], 201);
    }

    /**
     * Display the specified message.
     *
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Message $message)
    {
        $user = Auth::user();
        
        if ($message->sender_id !== $user->id && $message->receiver_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Mark as read if user is receiver
        if ($message->receiver_id === $user->id && !$message->is_read) {
            $message->update(['is_read' => true]);
        }

        $message->load(['sender', 'receiver']);

        return response()->json([
            'status' => 'success',
            'data' => $message
        ]);
    }

    /**
     * Remove the specified message from storage.
     *
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Message $message)
    {
        $user = Auth::user();
        
        if ($message->sender_id !== $user->id && $message->receiver_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $message->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Message deleted successfully'
        ]);
    }

    /**
     * Get unread messages count.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadCount()
    {
        $user = Auth::user();
        $count = Message::where('receiver_id', $user->id)
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
     * Mark message as read.
     *
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Message $message)
    {
        $user = Auth::user();
        
        if ($message->receiver_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $message->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Message marked as read'
        ]);
    }

    /**
     * Get sent messages.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sent()
    {
        $user = Auth::user();
        $messages = Message::where('sender_id', $user->id)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $messages
        ]);
    }

    /**
     * Get received messages.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function received()
    {
        $user = Auth::user();
        $messages = Message::where('receiver_id', $user->id)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $messages
        ]);
    }

    /**
     * Create a notification for a message.
     *
     * @param  \App\Models\Message  $message
     * @return void
     */
    private function createMessageNotification(Message $message)
    {
        Notification::create([
            'title' => 'رسالة جديدة',
            'content' => 'لديك رسالة جديدة من ' . $message->sender->name,
            'receiver_id' => $message->receiver_id,
            'receiver_type' => 'user',
            'type' => 'message',
            'reference_id' => $message->id,
        ]);
    }
} 