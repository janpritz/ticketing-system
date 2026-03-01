<?php

namespace App\Services\Admin;

use App\Models\{User, Department, Role};
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountVerificationMail;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function storeUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // 1. Create User with verification token
            $user = User::create([
                'name'               => $data['name'],
                'email'              => $data['email'],
                'verification_token' => bin2hex(random_bytes(32)),
            ]);

            // 2. Prepare Role Records (Primary + Additional)
            $roleRecords = $this->prepareRoleRecords($user->id, $data);

            // 3. Insert into user_roles pivot
            if (!empty($roleRecords)) {
                DB::table('user_roles')->insert($roleRecords);
            }

            // 4. Send Verification Email (Catch error so creation doesn't fail)
            $this->sendVerificationEmail($user);

            return $user;
        });
    }

    /**
     * Prepares the role-department association data for batch insertion.
     */
    private function prepareRoleRecords(int $userId, array $data): array
    {
        $records = [];
        $timestamp = now();

        // Add the Primary Role
        $records[] = [
            'user_id'         => $userId,
            'role_id'         => $data['role_id'],
            'department_id'   => $data['department_id'],
            'is_primary_role' => true,
            'created_at'      => $timestamp,
            'updated_at'      => $timestamp,
        ];

        // Add Additional Roles
        if (!empty($data['additional_roles'])) {
            foreach ($data['additional_roles'] as $roleId) {
                $records[] = [
                    'user_id'         => $userId,
                    'role_id'         => $roleId,
                    'department_id'   => $data['department_id'],
                    'is_primary_role' => false,
                    'created_at'      => $timestamp,
                    'updated_at'      => $timestamp,
                ];
            }
        }

        return $records;
    }

    /**
     * Dispatch the account verification email.
     */
    private function sendVerificationEmail(User $user): void
    {
        try {
            Mail::to($user->email)->send(
                new AccountVerificationMail($user->name, $user->verification_token)
            );
        } catch (\Throwable $e) {
            throw new \Exception("Failed to send verification email: " . $e->getMessage());
        }
    }

    /**
     * Get paginated users for the index view.
     */
    public function getUsersPaginated(string $search = '', bool $includeDeleted = false, int $perPage = 10): LengthAwarePaginator
    {
        $query = User::whereHas('roles', function ($q) {
            $q->where('roles.name', '!=', 'Primary Administrator');
        })->with(['roles']);

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

        if ($includeDeleted) $query->onlyTrashed();

        $users = $query->orderBy('name')->paginate($perPage);
        $users->appends(['q' => $search, 'include_deleted' => $includeDeleted ? '1' : '0']);

        $users->getCollection()->transform(fn($u) => $this->attachDepartmentData($u));

        return $users;
    }

    /**
     * Data for create/edit forms.
     */
    public function getFormDataForUserCreation(): array
    {
        return [
            'roles'       => Role::where('name', '!=', 'Primary Administrator')->orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
        ];
    }

    /**
     * Attach department name via pivot lookup.
     */
    private function attachDepartmentData(User $user): User
    {
        $pivot = DB::table('user_roles')->where('user_id', $user->id)->where('is_primary_role', true)->first();
        $deptId = $pivot ? $pivot->department_id : null;

        if ($deptId) {
            $dept = Department::find($deptId);
            $user->department = $dept ? $dept->name : null;
        } else {
            $user->department = null;
        }

        return $user;
    }

    public function updateUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            // 1. Update Basic Info
            $user->name = $data['name'];
            $user->email = $data['email'];

            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();

            // 2. Sync Roles (Delete old and insert new)
            DB::table('user_roles')->where('user_id', $user->id)->delete();

            $roleRecords = $this->prepareRoleRecords($user->id, $data);
            if (!empty($roleRecords)) {
                DB::table('user_roles')->insert($roleRecords);
            }

            return $user;
        });
    }

    public function deleteUser(User $user): bool
    {
        return $user->delete();
    }
}
