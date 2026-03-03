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
        //No need to display show page for staff, only display edit page
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        if (Auth::user()->id === 1) {
            abort(403, 'Cannot edit Primary Administrator.');
        }
        return view('dashboards.admin.users.edit', ['user' => Auth::user()->name]);
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

    /**
     * Deleted users page (redirect)
     *
     * Redirects to the main users index with a flag so the index will load
     * the deleted-list view.
     */

    public function usersDeletedIndex(Request $request)
    {
        return redirect()->route('admin.users.index', ['include_deleted' => '1']);
    }

    public function usersRestore($userId, UserService $userService)
    {
        // The service handles finding the trashed user and the restoration logic
        $userService->restoreUser($userId);

        return redirect()->route('admin.users.index')
            ->with('status', 'User restored.');
    }

    /**
     * Get the roles and department data for a specific user.
     */
    public function usersGetRoles(User $user, UserService $userService)
    {
        // The service handles the complex mapping and returns the array
        $data = $userService->getUserRolesData($user);

        return response()->json($data);
    }

    public function usersCheckEmail(Request $request, UserService $userService)
    {
        $email = $request->input('email', '');
        $excludeUserId = $request->input('exclude_user_id');

        $result = $userService->validateEmailAvailability($email, $excludeUserId);

        return response()->json($result);
    }

    /**
     * Resend the account verification email.
     */
    public function usersResendVerification(Request $request, UserService $userService)
    {
        $email = $request->input('email', '');
        $result = $userService->resendVerification($email);

        return response()->json($result);
    }
}
