<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{
    /**
     * Display a listing of the groups.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $groups = Group::with(['students', 'courses'])->get();

        return response()->json([
            'status' => 'success',
            'data' => $groups
        ]);
    }

    /**
     * Store a newly created group in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $group = Group::create([
            'name' => $request->name,
            'description' => $request->description,
            'active' => $request->active ?? true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Group created successfully',
            'data' => $group
        ], 201);
    }

    /**
     * Display the specified group.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Group $group)
    {
        $user = Auth::user();
        
        // Check if user is Admin or a Student in this group
        if (!$user->hasRole('Admin') && 
            (!$user->hasRole('Student') || $user->group_id != $group->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        $group->load(['students', 'courses', 'schedules.course']);
        
        return response()->json([
            'status' => 'success',
            'data' => $group
        ]);
    }

    /**
     * Update the specified group in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Group $group)
    {
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $group->update([
            'name' => $request->name,
            'description' => $request->description,
            'active' => $request->active ?? $group->active,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Group updated successfully',
            'data' => $group
        ]);
    }

    /**
     * Remove the specified group from storage.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Group $group)
    {
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Check if there are students in this group
        $studentsCount = User::where('group_id', $group->id)->count();
        
        if ($studentsCount > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete group because it contains students'
            ], 422);
        }
        
        $group->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Group deleted successfully'
        ]);
    }

    /**
     * Get students in a specific group.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudents(Group $group)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Admin') && 
            (!$user->hasRole('Teacher') || !$user->teacherCourses()->whereHas('groups', function($q) use ($group) {
                $q->where('id', $group->id);
            })->exists())) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $students = $group->students()->with('roles')->get();

        return response()->json([
            'status' => 'success',
            'data' => $students
        ]);
    }

    /**
     * Get courses assigned to a specific group.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCourses(Group $group)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Admin') && 
            (!$user->hasRole('Teacher') || !$user->teacherCourses()->whereHas('groups', function($q) use ($group) {
                $q->where('id', $group->id);
            })->exists())) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $courses = $group->courses()->with('teachers')->get();

        return response()->json([
            'status' => 'success',
            'data' => $courses
        ]);
    }
} 