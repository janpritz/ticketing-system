<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\Department;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class UserService
{
    /**
     * Get a paginated list of users (excluding Primary Admins) with search and soft-delete filters.
     */
    public function getUsersPaginated(string $search = '', bool $includeDeleted = false, int $perPage = 10): LengthAwarePaginator
    {
        $query = User::whereHas('roles', function ($qRole) {
            $qRole->where('roles.name', '!=', 'Primary Administrator');
        })
        ->with(['roles']); // We will handle department via pivot or relation

        // Apply Search Filter
        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($qq) use ($like) {
                $qq->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhereHas('roles', function ($qr) use ($like) {
                        $qr->where('roles.name', 'like', $like);
                    });
            });
        }

        // Handle Soft Deletes
        if ($includeDeleted) {
            $query->onlyTrashed();
        }

        $users = $query->orderBy('name')->paginate($perPage);

        // Appends query parameters for pagination links
        $users->appends([
            'q' => $search,
            'include_deleted' => $includeDeleted ? '1' : '0'
        ]);

        // Transform collection to include department info efficiently
        $users->getCollection()->transform(function ($user) {
            return $this->attachDepartmentData($user);
        });

        return $users;
    }

    /**
     * Attaches department name to the user object.
     * Logic: Look at user_roles pivot for department_id.
     */
    private function attachDepartmentData(User $user): User
    {
        // Accessing the pivot table data directly from the loaded relationship
        // This assumes your User model roles relationship is defined with ->withPivot('department_id')
        $pivot = DB::table('user_roles')->where('user_id', $user->id)->first();
        $departmentId = $pivot ? $pivot->department_id : null;

        if ($departmentId) {
            // Optimization: In a production app, you might want to cache or eager load Departments
            $department = Department::find($departmentId);
            $user->department = $department ? $department->name : null;
        } else {
            $user->department = null;
        }

        return $user;
    }
}