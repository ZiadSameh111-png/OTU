<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
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
     * Redirect to the appropriate dashboard based on user role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->roles()->first();

        if (!$role) {
            return redirect()->route('login')
                ->with('error', 'لم يتم تعيين دور لحسابك. الرجاء التواصل مع مدير النظام.');
        }

        switch ($role->name) {
            case 'Admin':
                return view('dashboards.admin', [
                    'user' => $user,
                    'totalUsers' => \App\Models\User::count(),
                    'totalStudents' => \App\Models\User::whereHas('roles', function($q) {
                        $q->where('name', 'Student');
                    })->count(),
                    'totalTeachers' => \App\Models\User::whereHas('roles', function($q) {
                        $q->where('name', 'Teacher');
                    })->count(),
                ]);

            case 'Teacher':
                return view('dashboards.teacher', [
                    'user' => $user,
                    'students' => \App\Models\User::whereHas('roles', function($q) {
                        $q->where('name', 'Student');
                    })->paginate(10),
                ]);

            case 'Student':
                return view('dashboards.student', [
                    'user' => $user,
                ]);

            default:
                return redirect()->route('login')
                    ->with('error', 'عذراً، لا يمكن الوصول إلى لوحة التحكم.');
        }
    }
}
