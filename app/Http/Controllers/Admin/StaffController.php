<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\{AdminService, DashboardService, UserService};
use App\Http\Requests\Admin\{StoreUserRequest, UpdateUserRequest};
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, UserService $userService)
    {

        // Sanitize search query and cast soft-delete toggle to boolean
        $q = trim((string) $request->query('q', ''));
        $isDeleted = (bool) $request->query('include_deleted', false);

        $users = $userService->getUsersPaginated($q, $isDeleted);

        return view('dashboards.admin.users.index', [
            'users'         => $users,
            'q'             => $q,
            'isDeletedView' => $isDeleted,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboards.admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request, UserService $userService)
    {
        // The service handles token generation, email, and pivot logic
        $userService->storeUser($request->validated());

        return redirect()->route('admin.users.index')
            ->with('status', 'Staff created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        if ($user->role === 'Primary Administrator') {
            abort(403, 'Cannot edit Primary Administrator.');
        }
        return view('dashboards.admin.users.edit', ['user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user, UserService $userService)
    {
        // Delegate update and role syncing to the service
        $userService->updateUser($user, $request->validated());

        return redirect()->route('admin.users.index')
            ->with('status', 'Staff updated.');
    }

    public function destroy(User $user, UserService $userService)
    {
        // 1. Protection: Check if the user is trying to delete themselves
        if ($user->id === Auth::id()) {
        return back()->withErrors(['delete' => 'Self-deletion is not permitted.']);
    }

        // 2. Protection: Check if target is a Primary Administrator
        $isAdmin = $user->roles()->where('roles.name', 'Primary Administrator')->exists();
        if ($isAdmin) {
            return back()->withErrors(['delete' => 'Cannot delete Primary Administrator.']);
        }

        // 3. Delegate the actual deletion to the service
        $userService->deleteUser($user);

        return redirect()->route('admin.users.index')->with('status', 'Staff deleted.');
    }
}
