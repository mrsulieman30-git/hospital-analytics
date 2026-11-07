@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-center justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
                Edit Staff Account: {{ $user->name }}
            </h2>
            <p class="text-xs text-slate-500 font-mono">{{ $user->email }}</p>
        </div>
        <a href="{{ route('settings.users.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-white">
            &larr; Back to Users
        </a>
    </div>

    <form action="{{ route('settings.users.update', $user) }}" method="POST" 
          class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-4 text-xs">
        @csrf
        @method('PUT')

        <div>
            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Full Name & Professional Title</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                   class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-semibold">
        </div>

        <div>
            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Work Email Address</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                   class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-mono">
        </div>

        <div>
            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Reset Password (leave empty to keep current)</label>
            <input type="password" name="password" placeholder="••••••••"
                   class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-mono">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">System Role</label>
                <select name="role" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-semibold">
                    <option value="data_entry" {{ $user->role === 'data_entry' ? 'selected' : '' }}>Shift Data Entry Staff</option>
                    <option value="department_supervisor" {{ $user->role === 'department_supervisor' ? 'selected' : '' }}>Department Supervisor</option>
                    <option value="analytics_officer" {{ $user->role === 'analytics_officer' ? 'selected' : '' }}>Analytics &amp; Performance Officer</option>
                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Hospital Admin / Management</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Assigned Department</label>
                <select name="department" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-semibold">
                    <option value="">None / All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->slug }}" {{ $user->department === $dept->slug ? 'selected' : '' }}>{{ $dept->name_en }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="pt-4 flex justify-end">
            <button type="submit" 
                    class="px-5 py-2.5 rounded-xl font-bold text-xs bg-brand-primary text-white shadow-md hover:opacity-90 transition">
                Update Account
            </button>
        </div>
    </form>

</div>
@endsection
