<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\PermissionUserOverride;
use Illuminate\Http\Request;

class AccessMatrixController extends Controller
{
    public function index(Request $request)
    {
        $permissions = Permission::orderBy('group')->orderBy('name')->get();
        $roles       = Role::orderBy('name')->get();
        $users       = User::orderBy('name')->get();

        return view('access.matrix', compact('permissions','roles','users'));
    }

    public function updateRoles(Request $request)
    {
        $data = $request->validate([
            'role_permissions' => 'array',
        ]);

        $rolePermissions = $data['role_permissions'] ?? [];

        foreach ($rolePermissions as $roleId => $permissionIds) {
            $role = Role::find($roleId);
            if (! $role) continue;

            $role->permissions()->sync($permissionIds ?? []);
        }

        return back()->with('success', 'دسترسی نقش‌ها به‌روزرسانی شد.');
    }

    public function updateUserOverrides(Request $request)
    {
        $data = $request->validate([
            'user_id'        => 'required|exists:users,id',
            'overrides'      => 'array',
            'overrides.*'    => 'in:none,allow,deny',
        ]);

        $userId    = (int) $data['user_id'];
        $overrides = $data['overrides'] ?? [];

        foreach ($overrides as $permissionId => $value) {
            if ($value === 'none') {
                PermissionUserOverride::where('user_id', $userId)
                    ->where('permission_id', $permissionId)
                    ->delete();
                continue;
            }

            PermissionUserOverride::updateOrCreate(
                [
                    'user_id'       => $userId,
                    'permission_id' => $permissionId,
                ],
                [
                    'allowed' => $value === 'allow',
                ]
            );
        }

        return back()->with('success', 'استثناهای کاربر به‌روزرسانی شد.');
    }

    // Ajax: وضعیت permissionها برای یک کاربر
    public function userPermissions(User $user)
    {
        $permissions = Permission::orderBy('group')->orderBy('name')->get();

        $rolePermIds = $user->roles()
            ->with('permissions:id')
            ->get()
            ->pluck('permissions.*.id')
            ->flatten()
            ->unique()
            ->values()
            ->all();

        $overrides = PermissionUserOverride::where('user_id', $user->id)
            ->get()
            ->keyBy('permission_id');

        $result = [];


        foreach ($permissions as $perm) {
            $override = $overrides->get($perm->id);

            $result[] = [
                'id'             => $perm->id,
                'name'           => $perm->name,
                'label'          => $perm->label ?? $perm->name,
                'group'          => $perm->group ?? null,
                'inherited'      => in_array($perm->id, $rolePermIds),
                'override_value' => $override ? ($override->allowed ? 'allow' : 'deny') : 'none',
                'effective'      => $override
                    ? (bool) $override->allowed
                    : in_array($perm->id, $rolePermIds),
            ];
        }

        return response()->json([
            'user_id'     => $user->id,
            'permissions' => $result,
        ]);
    }
}
