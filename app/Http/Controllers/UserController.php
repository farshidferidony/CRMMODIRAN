<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // لیست ساده کاربران
    public function index()
    {
        $users = User::with('roles')->paginate(20);
        return view('users.index', compact('users'));
    }

    // فرم نقش‌ها
    // public function editRoles(User $user)
    // {
    //     $roles = Role::orderBy('title')->get();
    //     $userRoleIds = $user->roles()->pluck('roles.id')->toArray();

    //     return view('users.edit_roles', compact('user','roles','userRoleIds'));
    // }

    // ذخیره نقش‌ها
    // public function updateRoles(Request $request, User $user)
    // {
    //     $roleIds = $request->input('roles', []);
    //     $user->roles()->sync($roleIds);

    //     return redirect()->route('users.roles.edit', $user->id)
    //         ->with('success','نقش‌های کاربر به‌روزرسانی شد.');
    // }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:190',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        return redirect()->route('users.index')->with('success','کاربر با موفقیت ایجاد شد.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:190',
            'email'    => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $user->update([
            'name'  => $data['name'],
            'email' => $data['email'],
        ]);

        // اگر پسورد جدید وارد شده، آپدیت کن
        if (!empty($data['password'])) {
            $user->update(['password' => bcrypt($data['password'])]);
        }

        return redirect()->route('users.index')->with('success','کاربر به‌روزرسانی شد.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success','کاربر حذف شد.');
    }

    
    public function editRoles(User $user)
    {
        $roles = Role::orderBy('title')->get();
        $userRoleIds = $user->roles()->pluck('roles.id')->toArray();

        // فقط سر‌دسته‌ها (بدون والد)
        $categories = ProductCategory::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        $categoryPivot = \DB::table('category_role_user')
            ->where('user_id', $user->id)
            ->pluck('role_id', 'product_category_id')
            ->toArray();

        return view('users.edit_roles', compact(
            'user','roles','userRoleIds','categories','categoryPivot'
        ));
    }

    public function updateRoles(Request $request, User $user)
    {
        $roleIds = $request->input('roles', []);
        $user->roles()->sync($roleIds);

        // دسته‌های تحت سرپرستی
        $categoryRoles = $request->input('category_roles', []); // [category_id => role_id]

        // پاک‌کردن قبلی
        \DB::table('category_role_user')->where('user_id',$user->id)->delete();

        // فقط آن‌هایی که role_id معتبر دارند را درج می‌کنیم
        foreach ($categoryRoles as $categoryId => $roleId) {
            if (!$roleId) continue;

            \DB::table('category_role_user')->insert([
                'user_id'            => $user->id,
                'role_id'            => $roleId,
                'product_category_id'=> $categoryId,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }

        return redirect()->route('users.roles.edit', $user->id)
            ->with('success','نقش‌ها و سرپرستی دسته‌ها به‌روزرسانی شد.');
    }


}
