@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header & Filter Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-brand-primary/10 text-brand-primary border border-brand-primary/20">
                    Governance & Cutoff Center
                </span>
                <span class="text-xs text-slate-500">Cutoff: <strong>{{ $hospital->submission_deadline ?? '08:30' }} AM</strong></span>
            </div>
            <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight mt-1">
                Departmental Daily Reports & Audit Queue
            </h2>
        </div>

        <!-- Filter Form -->
        <form action="{{ route('reports.index') }}" method="GET" class="flex flex-wrap items-center gap-2 text-xs">
            <!-- Department Filter -->
            @if($departments->count() > 1)
                <select name="department" onchange="this.form.submit()" class="px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-semibold focus:outline-hidden">
                    <option value="">All Departments ({{ $departments->count() }})</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->slug }}" {{ $departmentSlug === $dept->slug ? 'selected' : '' }}>
                            {{ $dept->name_en }} ({{ $dept->form_code }})
                        </option>
                    @endforeach
                </select>
            @else
                <span class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-xs">
                    <i data-lucide="lock" class="w-3 h-3 mr-1 text-slate-400"></i>
                    {{ $departments->first()->name_en ?? 'Assigned Department' }}
                </span>
            @endif

            <!-- Status Filter -->
            <select name="status" onchange="this.form.submit()" class="px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-semibold focus:outline-hidden">
                <option value="">All Statuses</option>
                <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="submitted" {{ $status === 'submitted' ? 'selected' : '' }}>Submitted (Pending Audit)</option>
                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Audited & Approved</option>
                <option value="returned" {{ $status === 'returned' ? 'selected' : '' }}>Returned for Revision</option>
            </select>

            <!-- Date Filter -->
            <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
                   class="px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-mono font-semibold focus:outline-hidden">

            <a href="{{ route('reports.create') }}" class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-brand-primary text-white shadow-xs hover:opacity-90 transition">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>New Form</span>
            </a>
        </form>
    </div>

    <!-- Reports Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-mono text-[11px] text-slate-500 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3">Form Code</th>
                        <th class="px-4 py-3">Department</th>
                        <th class="px-4 py-3">Report Date</th>
                        <th class="px-4 py-3">Shift</th>
                        <th class="px-4 py-3">Duty Lead</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Auditor</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                    @forelse($reports as $r)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <td class="px-4 py-3 font-mono font-bold text-slate-900 dark:text-white">
                                {{ $r->form_code }}
                            </td>
                            <td class="px-4 py-3 font-bold text-slate-800 dark:text-slate-200">
                                {{ ucfirst($r->department) }}
                            </td>
                            <td class="px-4 py-3 font-mono">
                                {{ $r->report_date->toDateString() }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $r->shift }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $r->lead_staff_name ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $r->status_badge_class }}">
                                    {{ ucfirst($r->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-500">
                                {{ $r->auditor->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('reports.show', $r) }}" class="font-bold text-brand-primary hover:underline" title="Inspect">
                                    View
                                </a>
                                @if($r->status !== 'approved' || auth()->user()->isAnalyticsOfficer())
                                    <a href="{{ route('reports.edit', $r) }}" class="text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 font-semibold" title="Edit">
                                        Edit
                                    </a>
                                @endif
                                <a href="{{ route('reports.download', $r) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800" title="Direct Download PDF">
                                    <i data-lucide="download" class="w-3.5 h-3.5 inline"></i>
                                </a>
                                <a href="{{ route('reports.print', $r) }}" target="_blank" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" title="A4 Print">
                                    <i data-lucide="printer" class="w-3.5 h-3.5 inline"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                No department reports found matching the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reports->hasPages())
            <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-800">
                {{ $reports->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
