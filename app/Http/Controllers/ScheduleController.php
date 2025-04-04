<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Course;
use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
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
     * Display a listing of the schedules for the logged-in student.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check user role
        if (Auth::user()->hasRole('Admin')) {
            // Get all schedules for admin view
            $schedules = Schedule::with(['course', 'group'])->get();
            return view('admin.schedules.index', compact('schedules'));
        } else if (Auth::user()->hasRole('Student')) {
            // Redirect student to studentSchedule method
            return redirect()->route('student.schedule');
        } else {
            // Redirect other roles
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view schedules.');
        }
    }

    /**
     * Show the form for creating a new schedule.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check if user has Admin role
        if (!Auth::user()->hasRole('Admin')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to create schedules.');
        }

        // Get all courses and groups for the dropdown
        $courses = Course::all();
        $groups = Group::where('active', true)->get();
        
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        return view('admin.schedules.create', compact('courses', 'groups', 'days'));
    }

    /**
     * Store a newly created schedule in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Check if user has Admin role
        if (!Auth::user()->hasRole('Admin')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to create schedules.');
        }

        // Validate the request data
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'group_id' => 'required|exists:groups,id',
            'day' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'room' => 'nullable|string',
        ]);

        // Create the schedule
        $schedule = Schedule::create([
            'course_id' => $request->course_id,
            'group_id' => $request->group_id,
            'day' => $request->day,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'room' => $request->room,
        ]);

        return redirect()->route('schedules.index')
            ->with('success', 'Schedule created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function show(Schedule $schedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function edit(Schedule $schedule)
    {
        // Check if user has Admin role
        if (!Auth::user()->hasRole('Admin')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to edit schedules.');
        }

        // Get all courses and groups for the dropdown
        $courses = Course::all();
        $groups = Group::where('active', true)->get();
        
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        return view('admin.schedules.edit', compact('schedule', 'courses', 'groups', 'days'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Schedule $schedule)
    {
        // Check if user has Admin role
        if (!Auth::user()->hasRole('Admin')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to update schedules.');
        }

        // Validate the request data
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'group_id' => 'required|exists:groups,id',
            'day' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'room' => 'nullable|string',
        ]);

        // Update the schedule
        $schedule->update([
            'course_id' => $request->course_id,
            'group_id' => $request->group_id,
            'day' => $request->day,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'room' => $request->room,
        ]);

        return redirect()->route('schedules.index')
            ->with('success', 'Schedule updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(Schedule $schedule)
    {
        // Check if user has Admin role
        if (!Auth::user()->hasRole('Admin')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to delete schedules.');
        }

        // Delete the schedule
        $schedule->delete();

        return redirect()->route('schedules.index')
            ->with('success', 'Schedule deleted successfully.');
    }

    /**
     * Display the schedules for the logged-in student based on their group.
     *
     * @return \Illuminate\Http\Response
     */
    public function studentSchedule()
    {
        // Check if user has Student role
        if (!Auth::user()->hasRole('Student')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view this page.');
        }

        // Get the student's group
        $group = Auth::user()->group;
        
        if (!$group) {
            return view('student.schedule', ['schedules' => collect(), 'groupName' => 'لا توجد مجموعة']);
        }

        // Get the group's schedules
        $schedules = $group->schedules()->with('course')->get();
        
        return view('student.schedule', [
            'schedules' => $schedules,
            'groupName' => $group->name
        ]);
    }
}
