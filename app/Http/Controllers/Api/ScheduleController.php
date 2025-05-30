<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Course;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    /**
     * Display a listing of schedules.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            $schedules = Schedule::with(['course', 'group'])->get();
        } elseif ($user->hasRole('Teacher')) {
            $schedules = Schedule::whereHas('course', function($query) use ($user) {
                $query->whereHas('teachers', function($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            })->with(['course', 'group'])->get();
        } elseif ($user->hasRole('Student')) {
            if (!$user->group) {
                return response()->json([
                    'status' => 'success',
                    'data' => []
                ]);
            }
            $schedules = Schedule::where('group_id', $user->group_id)
                ->with(['course'])
                ->get();
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $schedules
        ]);
    }

    /**
     * Store a newly created schedule in storage.
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
            'course_id' => 'required|exists:courses,id',
            'group_id' => 'required|exists:groups,id',
            'day' => 'required|string|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if course is assigned to group
        $course = Course::find($request->course_id);
        if (!$course->groups->contains($request->group_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'This course is not assigned to the selected group'
            ], 422);
        }

        // Check for schedule conflicts
        $conflictingSchedule = Schedule::where('group_id', $request->group_id)
            ->where('day', $request->day)
            ->where(function($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                    ->orWhereBetween('end_time', [$request->start_time, $request->end_time]);
            })->first();

        if ($conflictingSchedule) {
            return response()->json([
                'status' => 'error',
                'message' => 'There is a scheduling conflict with another class'
            ], 422);
        }

        $schedule = Schedule::create([
            'course_id' => $request->course_id,
            'group_id' => $request->group_id,
            'day' => $request->day,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'room' => $request->room,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Schedule created successfully',
            'data' => $schedule
        ], 201);
    }

    /**
     * Display the specified schedule.
     *
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Schedule $schedule)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Admin') && 
            !($user->hasRole('Teacher') && $schedule->course->teachers->contains($user->id)) &&
            !($user->hasRole('Student') && $schedule->group_id === $user->group_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $schedule->load(['course', 'group']);

        return response()->json([
            'status' => 'success',
            'data' => $schedule
        ]);
    }

    /**
     * Update the specified schedule in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Schedule $schedule)
    {
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'group_id' => 'required|exists:groups,id',
            'day' => 'required|string|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if course is assigned to group
        $course = Course::find($request->course_id);
        if (!$course->groups->contains($request->group_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'This course is not assigned to the selected group'
            ], 422);
        }

        // Check for schedule conflicts (excluding current schedule)
        $conflictingSchedule = Schedule::where('id', '!=', $schedule->id)
            ->where('group_id', $request->group_id)
            ->where('day', $request->day)
            ->where(function($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                    ->orWhereBetween('end_time', [$request->start_time, $request->end_time]);
            })->first();

        if ($conflictingSchedule) {
            return response()->json([
                'status' => 'error',
                'message' => 'There is a scheduling conflict with another class'
            ], 422);
        }

        $schedule->update([
            'course_id' => $request->course_id,
            'group_id' => $request->group_id,
            'day' => $request->day,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'room' => $request->room,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Schedule updated successfully',
            'data' => $schedule
        ]);
    }

    /**
     * Remove the specified schedule from storage.
     *
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Schedule $schedule)
    {
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $schedule->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Schedule deleted successfully'
        ]);
    }

    /**
     * Get schedules for a specific group.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function groupSchedules(Group $group)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Admin') && 
            !($user->hasRole('Teacher') && $user->teacherCourses()->whereHas('groups', function($q) use ($group) {
                $q->where('id', $group->id);
            })->exists()) &&
            !($user->hasRole('Student') && $user->group_id === $group->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $schedules = Schedule::where('group_id', $group->id)
            ->with(['course'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $schedules
        ]);
    }

    /**
     * Get schedules for a specific course.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\JsonResponse
     */
    public function courseSchedules(Course $course)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Admin') && 
            !($user->hasRole('Teacher') && $course->teachers->contains($user->id))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $schedules = Schedule::where('course_id', $course->id)
            ->with(['group'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $schedules
        ]);
    }
} 