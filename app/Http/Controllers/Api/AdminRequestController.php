<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminRequest;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AdminRequestController extends Controller
{
    /**
     * Display a listing of admin requests.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            $requests = AdminRequest::with(['user'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } else {
            $requests = AdminRequest::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        return response()->json([
            'status' => 'success',
            'data' => $requests
        ]);
    }

    /**
     * Store a newly created admin request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:schedule_change,grade_review,attendance_correction,other',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'reference_id' => 'nullable|integer',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $adminRequest = AdminRequest::create([
            'user_id' => Auth::id(),
            'type' => $request->type,
            'subject' => $request->subject,
            'description' => $request->description,
            'reference_id' => $request->reference_id,
            'status' => 'pending',
            'attachments' => $this->handleAttachments($request),
        ]);

        // Notify admins
        $this->notifyAdmins($adminRequest);

        return response()->json([
            'status' => 'success',
            'message' => 'Request submitted successfully',
            'data' => $adminRequest
        ], 201);
    }

    /**
     * Display the specified admin request.
     *
     * @param  \App\Models\AdminRequest  $adminRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(AdminRequest $adminRequest)
    {
        $user = Auth::user();

        if (!$user->hasRole('Admin') && $adminRequest->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $adminRequest->load(['user', 'responses']);

        return response()->json([
            'status' => 'success',
            'data' => $adminRequest
        ]);
    }

    /**
     * Update the status of an admin request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AdminRequest  $adminRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, AdminRequest $adminRequest)
    {
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected,in_progress',
            'response' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $adminRequest->update([
            'status' => $request->status,
            'resolved_at' => $request->status !== 'in_progress' ? Carbon::now() : null,
            'resolved_by' => $request->status !== 'in_progress' ? Auth::id() : null
        ]);

        // Add response
        $adminRequest->responses()->create([
            'user_id' => Auth::id(),
            'content' => $request->response
        ]);

        // Notify the user
        $this->notifyUser($adminRequest);

        return response()->json([
            'status' => 'success',
            'message' => 'Request status updated successfully',
            'data' => $adminRequest
        ]);
    }

    /**
     * Add a response to an admin request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AdminRequest  $adminRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function addResponse(Request $request, AdminRequest $adminRequest)
    {
        $user = Auth::user();

        if (!$user->hasRole('Admin') && $adminRequest->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $response = $adminRequest->responses()->create([
            'user_id' => $user->id,
            'content' => $request->content
        ]);

        // Notify the other party
        if ($user->hasRole('Admin')) {
            $this->notifyUser($adminRequest, 'New response from admin');
        } else {
            $this->notifyAdmins($adminRequest, 'New response from user');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Response added successfully',
            'data' => $response
        ]);
    }

    /**
     * Handle file attachments.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    private function handleAttachments(Request $request)
    {
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('admin-requests', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'type' => $file->getClientMimeType()
                ];
            }
        }
        return $attachments;
    }

    /**
     * Notify admins about a request.
     *
     * @param  \App\Models\AdminRequest  $adminRequest
     * @param  string  $message
     * @return void
     */
    private function notifyAdmins($adminRequest, $message = 'New admin request received')
    {
        $admins = User::whereHas('roles', function($q) {
            $q->where('name', 'Admin');
        })->get();

        foreach ($admins as $admin) {
            Notification::create([
                'title' => $message,
                'content' => "Request from {$adminRequest->user->name}: {$adminRequest->subject}",
                'receiver_id' => $admin->id,
                'receiver_type' => 'user',
                'type' => 'admin_request',
                'reference_id' => $adminRequest->id
            ]);
        }
    }

    /**
     * Notify the user about their request.
     *
     * @param  \App\Models\AdminRequest  $adminRequest
     * @param  string  $message
     * @return void
     */
    private function notifyUser($adminRequest, $message = null)
    {
        if (!$message) {
            $message = "Your request has been {$adminRequest->status}";
        }

        Notification::create([
            'title' => $message,
            'content' => "Regarding your request: {$adminRequest->subject}",
            'receiver_id' => $adminRequest->user_id,
            'receiver_type' => 'user',
            'type' => 'admin_request',
            'reference_id' => $adminRequest->id
        ]);
    }
} 