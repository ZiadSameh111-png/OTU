<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Check if user has admin role
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        $users = User::with('roles')->paginate(10);
        
        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    /**
     * Get all roles for user management.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRoles()
    {
        // Check if user has admin role
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        $roles = Role::all();
        
        return response()->json([
            'status' => 'success',
            'data' => $roles
        ]);
    }

    /**
     * Get all groups for user management.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroups()
    {
        // Check if user has admin role
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        $groups = Group::where('active', true)->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $groups
        ]);
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Check if user has admin role
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id' => ['required', 'exists:roles,id'],
            'group_id' => ['nullable', 'exists:groups,id']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'group_id' => $request->group_id,
        ]);

        // Assign the selected role to the user
        $role = Role::findOrFail($request->role_id);
        $user->roles()->attach($role);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => $user->load('roles')
        ], 201);
    }

    /**
     * Display the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Check if user has admin role
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        $user = User::with(['roles', 'group'])->findOrFail($id);
        
        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Check if user has admin role
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        $user = User::findOrFail($id);
        
        $validationRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role_id' => ['required', 'exists:roles,id'],
            'group_id' => ['nullable', 'exists:groups,id']
        ];

        // Add password validation rules only if provided
        if ($request->filled('password')) {
            $validationRules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update basic data
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'group_id' => $request->group_id,
        ];

        // Update password only if provided
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        // Update user role
        $user->roles()->sync([$request->role_id]);

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => $user->load(['roles', 'group'])
        ]);
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Check if user has admin role
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        $user = User::findOrFail($id);
        
        // Check if trying to delete themselves
        if ($user->id === Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete your own account'
            ], 400);
        }
        
        $user->roles()->detach();
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully'
        ]);
    }
    
    /**
     * Get a list of students.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudents()
    {
        // Check if user has admin role
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'Student');
        })->with('group')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $students
        ]);
    }
    
    /**
     * Get a list of teachers.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTeachers()
    {
        // Check if user has admin role
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        $teachers = User::whereHas('roles', function($query) {
            $query->where('name', 'Teacher');
        })->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $teachers
        ]);
    }
} 