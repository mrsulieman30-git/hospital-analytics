@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-mono font-bold px-2.5 py-0.5 rounded-full bg-brand-primary/10 text-brand-primary border border-brand-primary/20">
                    RBAC & Staff Control
                </span>
                <span class="text-xs text-slate-500 font-medium">Role-Based Access Control</span>
            </div>
            <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight mt-1">
                Staff Accounts & Department Permissions
            </h2>
        </div>

        <a href="{{ route('settings.users.create') }}" 
           class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-brand-primary text-white shadow-xs hover:opacity-90 transition self-start sm:self-auto">
            <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
            <span>Add New Staff Account</span>
        </a>
    </div>

    <!-- Users Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-mono text-[11px] text-slate-500 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3">Staff Name</th>
                        <th class="px-4 py-3">Email Address</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Assigned Department</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                    @foreach($users as $u)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">
                                {{ $u->name }}
                            </td>
                            <td class="px-4 py-3 font-mono text-slate-500">
                                {{ $u->email }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ $u->visible_role_badge }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">
                                {{ $u->department ? ucfirst($u->department) : 'Full Platform' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-emerald-500 font-bold">&bull; Active</span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('settings.users.edit', $u) }}" class="font-bold text-brand-primary hover:underline">
                                    Edit
                                </a>
                                @if($u->id !== auth()->id() && !$u->is_stealth)
                                    <form action="{{ route('settings.users.destroy', $u) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to deactivate this account?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-500 hover:text-rose-700 font-semibold">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-800">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
