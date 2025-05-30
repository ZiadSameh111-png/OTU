<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    /**
     * Display a listing of the courses.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            $courses = Course::with(['teachers', 'groups'])->get();
            return response()->json([
                'status' => 'success',
                'data' => $courses
            ]);
        } elseif ($user->hasRole('Teacher')) {
            $courses = $user->teacherCourses()->with('groups')->get();
            return response()->json([
                'status' => 'success',
                'data' => $courses
            ]);
        } elseif ($user->hasRole('Student')) {
            if (!$user->group) {
                return response()->json([
                    'status' => 'success',
                    'data' => []
                ]);
            }
            
            $courses = $user->studentCourses()->get();
            return response()->json([
                'status' => 'success',
                'data' => $courses
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized to view courses'
        ], 403);
    }

    /**
     * Store a newly created course in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:courses',
            'description' => 'nullable|string',
            'semester' => 'required|string|max:20',
            'credit_hours' => 'required|integer|min:1|max:6',
            'teacher_id' => 'nullable|exists:users,id',
            'groups' => 'nullable|array',
            'groups.*' => 'exists:groups,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $course = Course::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'semester' => $request->semester,
            'credit_hours' => $request->credit_hours,
            'active' => true,
        ]);

        // Assign teacher to course using course_teacher pivot table
        if ($request->teacher_id) {
            $course->teachers()->attach($request->teacher_id);
        }

        // Link course to selected groups
        if ($request->has('groups')) {
            $course->groups()->attach($request->groups);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Course created successfully',
            'data' => $course->load(['teachers', 'groups'])
        ], 201);
    }

    /**
     * Display the specified course.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Course $course)
    {
        $course->load(['teachers', 'groups']);
        
        $user = Auth::user();
        
        if ($user->hasRole('Admin') || 
            ($user->hasRole('Teacher') && $course->teachers->contains($user)) || 
            ($user->hasRole('Student') && $user->group && $course->groups->contains($user->group))) {
            
            return response()->json([
                'status' => 'success',
                'data' => $course
            ]);
        }
        
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized to view this course'
        ], 403);
    }

    /**
     * Update the specified course in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Course $course)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:courses,code,' . $course->id,
            'description' => 'nullable|string',
            'semester' => 'required|string|max:20',
            'credit_hours' => 'required|integer|min:1|max:6',
            'teacher_id' => 'nullable|exists:users,id',
            'groups' => 'nullable|array',
            'groups.*' => 'exists:groups,id',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $course->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'semester' => $request->semester,
            'credit_hours' => $request->credit_hours,
            'active' => $request->has('active') ? $request->active : $course->active,
        ]);

        // Update teacher assignments
        if ($request->has('teacher_id')) {
            $course->teachers()->sync([$request->teacher_id]);
        }

        // Update group assignments
        if ($request->has('groups')) {
            $course->groups()->sync($request->groups);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Course updated successfully',
            'data' => $course->load(['teachers', 'groups'])
        ]);
    }

    /**
     * Remove the specified course from storage.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Course $course)
    {
        // Check if course has related data
        if ($course->grades()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete course with existing grades'
            ], 400);
        }

        // Remove relationships
        $course->teachers()->detach();
        $course->groups()->detach();
        
        // Delete the course
        $course->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Course deleted successfully'
        ]);
    }

    /**
     * Get teachers available for course assignment.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTeachers()
    {
        $teachers = User::whereHas('roles', function($q) {
            $q->where('name', 'Teacher');
        })->get(['id', 'name', 'email']);
        
        return response()->json([
            'status' => 'success',
            'data' => $teachers
        ]);
    }

    /**
     * Get groups available for course assignment.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroups()
    {
        $groups = Group::where('active', true)->get(['id', 'name']);
        
        return response()->json([
            'status' => 'success',
            'data' => $groups
        ]);
    }
} 