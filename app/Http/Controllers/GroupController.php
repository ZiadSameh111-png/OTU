<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Admin')->except(['show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $groups = Group::all();
        return view('admin.groups.index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.groups.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);

        Group::create([
            'name' => $request->name,
            'description' => $request->description,
            'active' => $request->active ? true : false,
        ]);

        return redirect()->route('groups.index')->with('success', 'تم إنشاء المجموعة بنجاح');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function show(Group $group)
    {
        // Check if current user is Admin or a Student in this group
        $user = Auth::user();
        
        if (!$user->hasRole('Admin') && 
            (!$user->hasRole('Student') || $user->group_id != $group->id)) {
            return redirect()->route('dashboard')->with('error', 'ليس لديك صلاحية لعرض هذه المجموعة');
        }
        
        $students = User::where('group_id', $group->id)->get();
        $schedules = $group->schedules()->with('course')->get();
        
        return view('admin.groups.show', compact('group', 'students', 'schedules'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function edit(Group $group)
    {
        return view('admin.groups.edit', compact('group'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Group $group)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $group->update([
            'name' => $request->name,
            'description' => $request->description,
            'active' => $request->active ? true : false,
        ]);

        return redirect()->route('groups.index')->with('success', 'تم تحديث المجموعة بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function destroy(Group $group)
    {
        // Check if there are students in this group
        $studentsCount = User::where('group_id', $group->id)->count();
        
        if ($studentsCount > 0) {
            return redirect()->route('groups.index')->with('error', 'لا يمكن حذف المجموعة لأنها تحتوي على طلاب');
        }
        
        $group->delete();
        
        return redirect()->route('groups.index')->with('success', 'تم حذف المجموعة بنجاح');
    }
}
