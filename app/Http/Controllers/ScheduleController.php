<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Course;
use App\Models\User;
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
            $schedules = Schedule::with(['course', 'student'])->get();
            return view('admin.schedules.index', compact('schedules'));
        } else if (Auth::user()->hasRole('Student')) {
            // Get the logged-in student's schedules
            $schedules = Auth::user()->schedules()->with('course')->get();
            return view('schedules.index', compact('schedules'));
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

        // Get all courses and students for the dropdown
        $courses = Course::all();
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'Student');
        })->get();
        
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        return view('schedules.create', compact('courses', 'students', 'days'));
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
            'student_id' => 'required|exists:users,id',
            'day' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'room' => 'nullable|string',
        ]);

        // Create the schedule
        $schedule = Schedule::create([
            'course_id' => $request->course_id,
            'student_id' => $request->student_id,
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

        // Get all courses and students for the dropdown
        $courses = Course::all();
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'Student');
        })->get();
        
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        return view('admin.schedules.edit', compact('schedule', 'courses', 'students', 'days'));
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
            'student_id' => 'required|exists:users,id',
            'day' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'room' => 'nullable|string',
        ]);

        // Update the schedule
        $schedule->update([
            'course_id' => $request->course_id,
            'student_id' => $request->student_id,
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
     * Display the schedules for the logged-in student.
     *
     * @return \Illuminate\Http\Response
     */
    public function studentSchedule()
    {
        // Check if user has Student role
        if (!Auth::user()->hasRole('Student')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view this page.');
        }

        // Get the logged-in student's schedules
        $schedules = Auth::user()->schedules()->with('course')->get();
        
        return view('student.schedule', compact('schedules'));
    }
}
