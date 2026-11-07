<?php

namespace App\Http\Controllers;

use App\Models\DepartmentForm;
use App\Models\HospitalSetting;
use App\Models\User;
use App\Services\AuditLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $viewer = Auth::user();
        if (! $viewer->isAdmin()) {
            abort(403);
        }

        // Stealth superadmin is strictly filtered out for non-stealth users
        $users = User::visibleTo($viewer)->orderBy('name')->paginate(20);
        $hospital = HospitalSetting::current();

        return view('settings.users.index', compact('users', 'hospital'));
    }

    public function create(): View
    {
        $user = Auth::user();
        if (! $user->isAdmin()) {
            abort(403);
        }

        $departments = DepartmentForm::where('is_active', true)->get();
        $hospital = HospitalSetting::current();

        return view('settings.users.create', compact('departments', 'hospital'));
    }

    public function store(Request $request): RedirectResponse
    {
        $viewer = Auth::user();
        if (! $viewer->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,analytics_officer,department_supervisor,data_entry'],
            'department' => ['nullable', 'string'],
        ]);

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'department' => in_array($validated['role'], ['department_supervisor', 'data_entry']) ? $validated['department'] : null,
            'is_stealth' => false,
            'is_active' => true,
        ]);

        AuditLoggerService::log(
            'CREATE',
            "{$viewer->name} created user account {$newUser->name} ({$newUser->email})",
            'User',
            $newUser->id,
            $newUser->department
        );

        return redirect()->route('settings.users.index')->with('success', "User '{$newUser->name}' created successfully.");
    }

    public function edit(User $user): View
    {
        $viewer = Auth::user();
        if (! $viewer->isAdmin()) {
            abort(403);
        }

        // Prevent non-stealth admin from touching stealth account
        if ($user->is_stealth && ! $viewer->isStealthSuperAdmin()) {
            abort(404);
        }

        $departments = DepartmentForm::where('is_active', true)->get();
        $hospital = HospitalSetting::current();

        return view('settings.users.edit', compact('user', 'departments', 'hospital'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $viewer = Auth::user();
        if (! $viewer->isAdmin()) {
            abort(403);
        }

        // Prevent non-stealth admin from touching stealth account
        if ($user->is_stealth && ! $viewer->isStealthSuperAdmin()) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', "unique:users,email,{$user->id}"],
            'role' => ['required', 'in:admin,analytics_officer,department_supervisor,data_entry'],
            'department' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'department' => in_array($validated['role'], ['department_supervisor', 'data_entry']) ? $validated['department'] : null,
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        AuditLoggerService::log(
            'UPDATE',
            "{$viewer->name} updated user account {$user->name}",
            'User',
            $user->id
        );

        return redirect()->route('settings.users.index')->with('success', "User '{$user->name}' updated successfully.");
    }

    public function destroy(User $user): RedirectResponse
    {
        $viewer = Auth::user();
        if (! $viewer->isAdmin()) {
            abort(403);
        }

        // Cannot delete stealth super admin or yourself
        if ($user->is_stealth || $user->id === $viewer->id) {
            abort(403, 'This account cannot be deleted.');
        }

        AuditLoggerService::log(
            'DELETE',
            "{$viewer->name} deleted user account {$user->name} ({$user->email})",
            'User',
            $user->id
        );

        $user->delete();

        return redirect()->route('settings.users.index')->with('success', 'User deleted successfully.');
    }
}
