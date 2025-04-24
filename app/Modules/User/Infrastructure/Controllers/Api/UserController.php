<?php

namespace App\Modules\User\Infrastructure\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\User\Application\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        return response()->json([
            'data' => $this->userService->getAllUsers()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin,teacher,student'
        ]);

        $user = $this->userService->createUser($validated);

        return response()->json([
            'message' => 'User created successfully',
            'data' => $user
        ], Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $user = $this->userService->getUser($id);
        
        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => $user
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,' . $id,
            'password' => 'string|min:8',
            'role' => 'string|in:admin,teacher,student'
        ]);

        $user = $this->userService->updateUser($id, $validated);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    public function destroy($id)
    {
        if ($this->userService->deleteUser($id)) {
            return response()->json([
                'message' => 'User deleted successfully'
            ]);
        }

        return response()->json([
            'message' => 'User not found'
        ], Response::HTTP_NOT_FOUND);
    }

    public function assignRole($userId, $roleId)
    {
        if ($this->userService->assignRole($userId, $roleId)) {
            return response()->json([
                'message' => 'Role assigned successfully'
            ]);
        }

        return response()->json([
            'message' => 'Failed to assign role'
        ], Response::HTTP_BAD_REQUEST);
    }

    public function removeRole($userId, $roleId)
    {
        if ($this->userService->removeRole($userId, $roleId)) {
            return response()->json([
                'message' => 'Role removed successfully'
            ]);
        }

        return response()->json([
            'message' => 'Failed to remove role'
        ], Response::HTTP_BAD_REQUEST);
    }
} 