<?php

namespace App\Modules\User\Infrastructure\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\User\Application\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $users = $this->userService->getAllUsers();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,teacher,student'
        ]);

        $this->userService->createUser($validated);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function show($id)
    {
        $user = $this->userService->getUser($id);
        
        if (!$user) {
            return redirect()->route('users.index')
                ->with('error', 'User not found.');
        }

        return view('users.show', compact('user'));
    }

    public function edit($id)
    {
        $user = $this->userService->getUser($id);
        
        if (!$user) {
            return redirect()->route('users.index')
                ->with('error', 'User not found.');
        }

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'string|in:admin,teacher,student'
        ]);

        if (!$this->userService->updateUser($id, $validated)) {
            return redirect()->route('users.index')
                ->with('error', 'User not found.');
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        if ($this->userService->deleteUser($id)) {
            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully.');
        }

        return redirect()->route('users.index')
            ->with('error', 'User not found.');
    }

    public function assignRole($userId, $roleId)
    {
        if ($this->userService->assignRole($userId, $roleId)) {
            return redirect()->back()
                ->with('success', 'Role assigned successfully.');
        }

        return redirect()->back()
            ->with('error', 'Failed to assign role.');
    }

    public function removeRole($userId, $roleId)
    {
        if ($this->userService->removeRole($userId, $roleId)) {
            return redirect()->back()
                ->with('success', 'Role removed successfully.');
        }

        return redirect()->back()
            ->with('error', 'Failed to remove role.');
    }
} 