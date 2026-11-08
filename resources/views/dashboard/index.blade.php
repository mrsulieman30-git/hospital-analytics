@extends('layouts.app')

@section('content')
@if($isRestricted)
{{-- ═══════════════════════════════════════════════════════════════════════════════
     DEPARTMENTAL OPERATIONAL WORKSPACE (Strictly scoped for supervisor / data entry)
     ═══════════════════════════════════════════════════════════════════════════════ --}}
<div class="space-y-6">

    <!-- 1. Department Operational Station Top Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 lg:p-5 shadow-xs">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            
            <!-- Department Identity & Role Badges -->
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-brand-primary text-white">
                        <i data-lucide="building-2" class="w-3.5 h-3.5 mr-1.5"></i>
                        {{ $userDepartment->name_en ?? ucfirst($userDepartmentSlug) }} <span x-text="$store.app.t('department_workspace', 'Workspace')">Workspace</span>
                    </span>

                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse mr-1.5"></span>
                        <span x-text="$store.app.t('active_shift', 'Active Shift')">Active Shift</span>: <strong class="ml-1">Morning (07:00–15:00)</strong>
                    </span>

                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-500/20">
                        <i data-lucide="clock" class="w-3 h-3 mr-1 text-amber-500"></i>
                        <span x-text="$store.app.t('governance_cutoff', 'Cutoff Deadline')">Cutoff</span>: <strong class="ml-1">{{ $hospital->submission_deadline ?? '08:30' }} AM</strong>
                    </span>
                </div>

                <div class="mt-2 flex flex-col sm:flex-row sm:items-baseline gap-2">
                    <h1 class="text-xl lg:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ $userDepartment->name_en ?? ucfirst($userDepartmentSlug) }} Operational Station
                    </h1>
                    @if(!empty($userDepartment->name_so))
                        <span class="text-xs font-semibold text-slate-400 dark:text-slate-500">({{ $userDepartment->name_so }})</span>
                    @endif
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Logged in as: <strong class="text-slate-800 dark:text-slate-200">{{ auth()->user()->name }}</strong> 
                    <span class="text-slate-400">({{ ucwords(str_replace('_', ' ', auth()->user()->role)) }})</span>
                    &bull; Period: <strong class="text-slate-800 dark:text-slate-200">{{ $filterLabel }}</strong> ({{ $startDate }} &rarr; {{ $endDate }})
                </p>
            </div>

            <!-- Controls: Period Tabs + Target Date Picker + CTA -->
            <div class="flex flex-wrap items-center gap-2 self-start lg:self-center">
                <!-- Period Selector -->
                <div class="flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1 text-xs font-semibold">
                    <a href="{{ route('dashboard', ['filter' => 'today', 'audit_date' => $targetAuditDate]) }}" 
                       class="px-2.5 py-1.5 rounded-lg transition {{ $filter === 'today' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        <span x-text="$store.app.t('period_today', 'Today')">Today</span>
                    </a>
                    <a href="{{ route('dashboard', ['filter' => 'yesterday', 'audit_date' => $targetAuditDate]) }}" 
                       class="px-2.5 py-1.5 rounded-lg transition {{ $filter === 'yesterday' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        <span x-text="$store.app.t('period_yesterday', 'Yesterday')">Yesterday</span>
                    </a>
                    <a href="{{ route('dashboard', ['filter' => '7_days', 'audit_date' => $targetAuditDate]) }}" 
                       class="px-2.5 py-1.5 rounded-lg transition {{ $filter === '7_days' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        <span x-text="$store.app.t('period_7_days', 'Past 7 Days')">7 Days</span>
                    </a>
                    <a href="{{ route('dashboard', ['filter' => 'this_month', 'audit_date' => $targetAuditDate]) }}" 
                       class="px-2.5 py-1.5 rounded-lg transition {{ $filter === 'this_month' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        <span x-text="$store.app.t('period_month', 'This Month')">Month</span>
                    </a>
                </div>

                <!-- Target Audit Date Picker -->
                <form action="{{ route('dashboard') }}" method="GET" class="flex items-center space-x-1.5">
                    <input type="hidden" name="filter" value="{{ $filter }}">
                    <input type="date" name="audit_date" value="{{ $targetAuditDate }}" 
                           onchange="this.form.submit()"
                           class="px-2.5 py-1.5 text-xs rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 font-semibold focus:outline-hidden cursor-pointer"
                           title="Select Specific Date to Review Submission">
                </form>

                @if($todayDeptReport)
                    <a href="{{ route('reports.edit', $todayDeptReport) }}" 
                       class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-brand-primary text-white border border-brand-primary hover:bg-slate-900 hover:text-white dark:hover:bg-white dark:hover:text-slate-900 transition">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        <span>Edit Today's Report</span>
                    </a>
                @else
                    <a href="{{ route('reports.create', ['department' => $userDepartmentSlug]) }}" 
                       class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-brand-primary text-white border border-brand-primary hover:bg-slate-900 hover:text-white dark:hover:bg-white dark:hover:text-slate-900 transition">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span x-text="$store.app.t('submit_daily_report', 'Submit Daily Report')">Submit Daily Report</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- 2. Today's Shift Report Live Status Card (Full Width Banner) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs">
        @if($todayDeptReport)
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-start space-x-3.5">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $todayDeptReport->status === 'approved' ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20' : ($todayDeptReport->status === 'submitted' ? 'bg-indigo-500/10 text-indigo-600 border border-indigo-500/20' : ($todayDeptReport->status === 'returned' ? 'bg-rose-500/10 text-rose-600 border border-rose-500/20' : 'bg-sky-500/10 text-sky-600 border border-sky-500/20')) }}">
                        @if($todayDeptReport->status === 'approved')
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                        @elseif($todayDeptReport->status === 'submitted')
                            <i data-lucide="clock" class="w-5 h-5"></i>
                        @elseif($todayDeptReport->status === 'returned')
                            <i data-lucide="alert-octagon" class="w-5 h-5"></i>
                        @else
                            <i data-lucide="file-edit" class="w-5 h-5"></i>
                        @endif
                    </div>

                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                                Today's {{ $userDepartment->name_en ?? ucfirst($userDepartmentSlug) }} Shift Report:
                            </h2>
                            <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full uppercase border {{ $todayDeptReport->status_badge_class }}">
                                {{ ucfirst($todayDeptReport->status) }}
                            </span>
                            <span class="text-xs text-slate-400 font-mono">Date: {{ $todayDeptReport->report_date->format('M d, Y') }}</span>
                        </div>

                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Duty Lead: <strong class="text-slate-700 dark:text-slate-300">{{ $todayDeptReport->lead_staff_name ?? auth()->user()->name }}</strong>
                            &bull; Shift: <span class="capitalize">{{ $todayDeptReport->shift }}</span>
                            @if($todayDeptReport->status === 'approved')
                                &bull; <span class="text-emerald-600 font-medium">Audited & Verified by {{ $todayDeptReport->auditor->name ?? 'Medical Directorate' }}</span>
                            @elseif($todayDeptReport->status === 'submitted')
                                &bull; <span class="text-indigo-600 font-medium">Submitted at {{ $todayDeptReport->submitted_at ? $todayDeptReport->submitted_at->format('H:i') : 'today' }} (Awaiting Directorate Audit)</span>
                            @elseif($todayDeptReport->status === 'returned')
                                &bull; <span class="text-rose-600 font-medium">Returned for Revision: {{ $todayDeptReport->audit_notes }}</span>
                            @else
                                &bull; <span class="text-sky-600 font-medium">Draft auto-saved and ready for final clinical review</span>
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Quick Action Buttons -->
                <div class="flex flex-wrap items-center gap-2 self-start md:self-center shrink-0">
                    <a href="{{ route('reports.show', $todayDeptReport) }}" 
                       class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 hover:bg-slate-900 hover:text-white dark:hover:bg-white dark:hover:text-slate-900 transition">
                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                        <span>Inspect Sheet</span>
                    </a>

                    @if($todayDeptReport->status !== 'approved' || auth()->user()->isAdmin())
                        <a href="{{ route('reports.edit', $todayDeptReport) }}" 
                           class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-brand-primary text-white border border-brand-primary hover:bg-slate-900 hover:text-white dark:hover:bg-white dark:hover:text-slate-900 transition">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                            <span>Edit / Complete Report</span>
                        </a>
                    @endif

                    <a href="{{ route('reports.print', $todayDeptReport) }}" target="_blank"
                       class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-300 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700 transition"
                       title="A4 Print Preview">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                        <span>A4 Print</span>
                    </a>
                </div>
            </div>
        @else
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-start space-x-3.5">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 border border-amber-500/20 flex items-center justify-center shrink-0">
                        <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center space-x-2">
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                                Today's {{ $userDepartment->name_en ?? ucfirst($userDepartmentSlug) }} Shift Report has not been created yet
                            </h2>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20">
                                Pending Submission
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Hospital governance cutoff deadline is <strong class="text-slate-700 dark:text-slate-300">{{ $hospital->submission_deadline ?? '08:30' }} AM</strong>. Please record your shift statistics to maintain hospital-wide operational tracking.
                        </p>
                    </div>
                </div>

                <a href="{{ route('reports.create', ['department' => $userDepartmentSlug]) }}" 
                   class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-brand-primary text-white border border-brand-primary hover:bg-slate-900 hover:text-white dark:hover:bg-white dark:hover:text-slate-900 transition self-start md:self-center shrink-0">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Start Shift Report Now</span>
                </a>
            </div>
        @endif
    </div>

    <!-- 3. Scoped Headline Operational KPI Cards (4 Cards Tailored to Department) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        @if($userDepartmentSlug === 'pharmacy')
            <!-- Pharmacy Card 1: Served Rx & Fill Rate -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                            Served Patients (Footfall)
                        </span>
                        <span class="block text-[10px] text-slate-400 font-medium">Dispensed &amp; Walk-in POS</span>
                    </div>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-500 flex items-center justify-center">
                        <i data-lucide="pill" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-2">
                    <div class="flex items-baseline space-x-2">
                        <span class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                            {{ number_format($metrics['pharmacy']['total_served_rx']) }}
                        </span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $metrics['pharmacy']['prescription_fill_rate'] >= 85 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-amber-500/10 text-amber-600' }}">
                            {{ $metrics['pharmacy']['prescription_fill_rate'] }}% Fill
                        </span>
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                        Total patients served at pharmacy counter (including walk-in POS)
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>Unserved Patients</span>
                    <strong class="text-rose-500 font-bold font-mono">{{ number_format($metrics['pharmacy']['total_unserved_patients']) }} pts</strong>
                </div>
            </div>

            <!-- Pharmacy Card 2: Dispensed Value -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Total Dispensed Value
                    </span>
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-500 flex items-center justify-center">
                        <i data-lucide="dollar-sign" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-2">
                    <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight font-mono">
                        ${{ number_format($metrics['pharmacy']['total_dispensed_value'], 2) }}
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                        Gross medication value dispensed in period
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>Period Filter</span>
                    <strong class="text-slate-700 dark:text-slate-300 font-semibold">{{ $filterLabel }}</strong>
                </div>
            </div>

            <!-- Pharmacy Card 3: Stockout Lost Patients -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Stockout Incidents
                    </span>
                    <div class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-500 flex items-center justify-center">
                        <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-2">
                    <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ $metrics['pharmacy']['root_causes']['stockout'] ?? 0 }}
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                        Price barriers: {{ $metrics['pharmacy']['root_causes']['price'] ?? 0 }} &bull; Brand unavail: {{ $metrics['pharmacy']['root_causes']['brand_unavailable'] ?? 0 }}
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>Loss Root Cause</span>
                    <strong class="text-rose-500 font-bold">Formulary Stockout</strong>
                </div>
            </div>

            <!-- Pharmacy Card 4: Net Debt Exposure -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Net Debt Exposure
                    </span>
                    <div class="w-8 h-8 rounded-lg bg-cyan-50 dark:bg-cyan-950/50 text-cyan-500 flex items-center justify-center">
                        <i data-lucide="wallet" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-2">
                    <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight font-mono">
                        ${{ number_format($metrics['pharmacy']['net_credit_exposure'], 2) }}
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                        Taken: ${{ number_format($metrics['pharmacy']['debt_taken'] ?? 0, 2) }} &bull; Paid: ${{ number_format($metrics['pharmacy']['debt_paid'] ?? 0, 2) }}
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>Credit Reconciliation</span>
                    <strong class="text-slate-700 dark:text-slate-300 font-bold font-mono">Active Flow</strong>
                </div>
            </div>

        @elseif($userDepartmentSlug === 'emergency')
            <!-- ER Card 1: Total Cases -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Total Emergency Cases
                    </span>
                    <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/50 text-amber-500 flex items-center justify-center">
                        <i data-lucide="siren" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-2">
                    <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ number_format($metrics['emergency']['total_er_cases']) }}
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                        Total emergency triage throughput in period
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>Active Period</span>
                    <strong class="text-slate-700 dark:text-slate-300 font-semibold">{{ $filterLabel }}</strong>
                </div>
            </div>

            <!-- ER Card 2: IPD Transfer Yield -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Inpatient (IPD) Transfer Yield
                    </span>
                    <div class="w-8 h-8 rounded-lg bg-cyan-50 dark:bg-cyan-950/50 text-cyan-500 flex items-center justify-center">
                        <i data-lucide="bed" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-2">
                    <div class="flex items-baseline space-x-2">
                        <span class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                            {{ $metrics['emergency']['conversion_rate_percent'] }}%
                        </span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-cyan-500/10 text-cyan-600 dark:text-cyan-400">
                            {{ number_format($metrics['emergency']['admitted_to_ipd']) }} pts
                        </span>
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                        Direct admissions from emergency to inpatient wards
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>Cross-Dept Link</span>
                    <strong class="text-cyan-600 dark:text-cyan-400 font-bold">ER &rarr; IPD</strong>
                </div>
            </div>

            <!-- ER Card 3: Mortality Rate -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        True In-ER Mortality
                    </span>
                    <div class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-500 flex items-center justify-center">
                        <i data-lucide="heart-pulse" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-2">
                    <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ $metrics['emergency']['true_in_er_mortality_rate'] }}%
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                        Acute resuscitation mortality in department
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>Clinical Safety Target</span>
                    <strong class="text-emerald-600 font-bold">&lt; 2.5%</strong>
                </div>
            </div>

            <!-- ER Card 4: Brought Dead (DOA) -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Brought Dead (DOA)
                    </span>
                    <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 flex items-center justify-center">
                        <i data-lucide="ambulance" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-2">
                    <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ $metrics['emergency']['doa_count'] }}
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                        Dead-on-arrival ambulance arrivals
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>Registry Status</span>
                    <strong class="text-slate-700 dark:text-slate-300 font-mono">Logged & Documented</strong>
                </div>
            </div>

        @elseif($userDepartmentSlug === 'inpatient')
            <!-- Inpatient Card 1: BOR -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Bed Occupancy (BOR)
                    </span>
                    <div class="w-8 h-8 rounded-lg bg-cyan-50 dark:bg-cyan-950/50 text-cyan-500 flex items-center justify-center">
                        <i data-lucide="bed" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-2">
                    <div class="flex items-baseline space-x-2">
                        <span class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                            {{ $metrics['inpatient']['bed_occupancy_rate'] }}%
                        </span>
                        <span class="text-xs text-slate-400 font-medium">Cap: {{ $metrics['inpatient']['active_beds'] ?? 60 }} Beds</span>
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                        Optimal clinical range: 75% – 85%
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>Daily Census Formula</span>
                    <strong class="text-emerald-600 font-mono text-[10px]">Balanced</strong>
                </div>
            </div>

            <!-- Inpatient Card 2: ALOS -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Avg Length of Stay (ALOS)
                    </span>
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-500 flex items-center justify-center">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-2">
                    <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight font-mono">
                        {{ $metrics['inpatient']['average_length_of_stay'] }} <span class="text-base font-bold text-slate-400">Days</span>
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                        Average inpatient hospital bed stay per admission
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>Target Efficiency</span>
                    <strong class="text-slate-700 dark:text-slate-300 font-semibold">3.0 - 5.0 Days</strong>
                </div>
            </div>

            <!-- Inpatient Card 3: C-Section Rate -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Maternity C-Section Rate
                    </span>
                    <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-950/50 text-purple-500 flex items-center justify-center">
                        <i data-lucide="baby" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-2">
                    <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ $metrics['inpatient']['c_section_rate_percent'] }}%
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                        Caesarean sections as % of total deliveries
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>Clinical Threshold</span>
                    <strong class="text-slate-700 dark:text-slate-300 font-semibold">WHO &lt; 25%</strong>
                </div>
            </div>

            <!-- Inpatient Card 4: DAMA Discharges -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        DAMA Discharges
                    </span>
                    <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/50 text-amber-500 flex items-center justify-center">
                        <i data-lucide="user-x" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-2">
                    <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ $metrics['inpatient']['dama_discharges'] ?? 0 }}
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                        Discharged Against Medical Advice in period
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>Quality Indicator</span>
                    <strong class="text-slate-700 dark:text-slate-300 font-semibold">Monitored</strong>
                </div>
            </div>

        @elseif($userDepartmentSlug === 'laboratory')
            <!-- Lab Card 1: Total Tests -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Total Laboratory Tests
                    </span>
                    <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-950/50 text-purple-500 flex items-center justify-center">
                        <i data-lucide="flask-conical" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-2">
                    <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ number_format($metrics['laboratory']['total_tests']) }}
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                        Diagnostic throughput across all clinical benches
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>B2B Partner Share</span>
                    <strong class="text-purple-600 dark:text-purple-400 font-bold font-mono">{{ $metrics['laboratory']['b2b_partner_share_percent'] }}%</strong>
                </div>
            </div>

            <!-- Lab Card 2: Diagnostic Intensity -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Diagnostic Intensity
                    </span>
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-500 flex items-center justify-center">
                        <i data-lucide="activity" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-2">
                    <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight font-mono">
                        {{ $metrics['laboratory']['tests_per_patient_ratio'] }}x
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                        Average tests requested per patient
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>Clinical Benchmark</span>
                    <strong class="text-slate-700 dark:text-slate-300 font-semibold">2.0x - 3.5x</strong>
                </div>
            </div>

            <!-- Lab Card 3: QC & Calibrations -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Quality Control & QC
                    </span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-500 flex items-center justify-center">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-2">
                    <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ $metrics['laboratory']['qc_calibrations'] }}
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                        Equipment calibrations & QC runs verified
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>Sample Rejections</span>
                    <strong class="text-rose-500 font-bold font-mono">{{ $metrics['laboratory']['sample_rejections'] }} samples</strong>
                </div>
            </div>

            <!-- Lab Card 4: Critical Values -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Critical Values & Delays
                    </span>
                    <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/50 text-amber-500 flex items-center justify-center">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-2">
                    <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ $metrics['laboratory']['critical_values'] }}
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                        Immediate physician notifications & TAT delays: {{ $metrics['laboratory']['delayed_tat'] }}
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>Incomplete Tests</span>
                    <strong class="text-amber-600 font-bold font-mono">{{ $metrics['laboratory']['incomplete_tests'] }} items</strong>
                </div>
            </div>

        @else
            {{-- Default / Reception --}}
            <!-- Reception Card 1: Registered Patients Footfall -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                            Registered Patients (Footfall)
                        </span>
                        <span class="block text-[10px] text-slate-400 font-medium">Front Desk Intake</span>
                    </div>
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-500 flex items-center justify-center">
                        <i data-lucide="users" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-2">
                    <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ number_format($metrics['reception']['total_footfall']) }}
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                        Total patients registered at reception &amp; triage desk
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>Direct Walk-ins</span>
                    <strong class="text-slate-700 dark:text-slate-300 font-semibold">{{ $metrics['reception']['direct_lab_pharmacy_walkins'] ?? 0 }} pts</strong>
                </div>
            </div>

            <!-- Reception Card 2: Consultations -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Doctor Consultations
                    </span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-500 flex items-center justify-center">
                        <i data-lucide="stethoscope" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-2">
                    <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ number_format($metrics['reception']['total_consultations']) }}
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                        Completed physician consultations in clinic
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>Direct Walk-ins</span>
                    <strong class="text-slate-700 dark:text-slate-300 font-semibold">{{ $metrics['reception']['direct_lab_pharmacy_walkins'] ?? 0 }} pts</strong>
                </div>
            </div>

            <!-- Reception Card 3: Doctor Rx Capture -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Doctor Rx Capture Rate
                    </span>
                    <div class="w-8 h-8 rounded-lg bg-cyan-50 dark:bg-cyan-950/50 text-cyan-500 flex items-center justify-center">
                        <i data-lucide="pill" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-2">
                    <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ $metrics['reception']['doctor_capture_rate'] }}%
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                        Physician prescriptions captured in hospital pharmacy
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>Target Capture</span>
                    <strong class="text-emerald-600 font-bold">&gt; 75%</strong>
                </div>
            </div>

            <!-- Reception Card 4: Daily Shift Status -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Shift Operational Lead
                    </span>
                    <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 flex items-center justify-center">
                        <i data-lucide="user-check" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-2">
                    <div class="text-lg font-bold text-slate-900 dark:text-white truncate">
                        {{ auth()->user()->name }}
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                        Role: {{ ucwords(str_replace('_', ' ', auth()->user()->role)) }}
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>Reporting Station</span>
                    <strong class="text-brand-primary font-bold">Reception (OPD)</strong>
                </div>
            </div>
        @endif

    </div>

    <!-- 4. Department 7-Day Performance Trend Chart & Operational Quick Hub -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        
        <!-- Chart: 7-Day Department Activity -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="trending-up" class="w-4 h-4 text-brand-primary"></i>
                        <span>7-Day {{ $userDepartment->name_en ?? ucfirst($userDepartmentSlug) }} Activity Trend</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Historical 7-day throughput and performance for your operational station
                    </p>
                </div>
                <div class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                </div>
            </div>
            
            <div class="relative h-64 w-full">
                <canvas id="departmentWeeklyChart"></canvas>
            </div>
        </div>

        <!-- Operational Quick Hub & Shift Protocol -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between space-y-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="zap" class="w-4 h-4 text-amber-500"></i>
                    <span>Station Operations & Quick Links</span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Direct access to reporting forms, past submissions, and official printable A4 templates.
                </p>

                <div class="mt-4 space-y-2">
                    <a href="{{ route('reports.create', ['department' => $userDepartmentSlug]) }}" 
                       class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 text-xs font-bold text-slate-900 dark:text-white hover:bg-brand-primary hover:text-white dark:hover:bg-brand-primary transition group">
                        <span class="flex items-center space-x-2">
                            <i data-lucide="plus-circle" class="w-4 h-4 text-brand-primary group-hover:text-white"></i>
                            <span>Create New Shift Report</span>
                        </span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-white"></i>
                    </a>

                    <a href="{{ route('reports.index', ['department' => $userDepartmentSlug]) }}" 
                       class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 text-xs font-bold text-slate-900 dark:text-white hover:bg-slate-900 hover:text-white dark:hover:bg-slate-700 transition group">
                        <span class="flex items-center space-x-2">
                            <i data-lucide="archive" class="w-4 h-4 text-slate-500 group-hover:text-white"></i>
                            <span>View All Department Reports</span>
                        </span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-white"></i>
                    </a>

                    @if($userDepartmentSlug === 'laboratory')
                        <a href="{{ route('reports.create', ['department' => 'laboratory', 'section' => 'quality-control']) }}" 
                           class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 text-xs font-bold text-slate-900 dark:text-white hover:bg-slate-900 hover:text-white dark:hover:bg-slate-700 transition group">
                            <span class="flex items-center space-x-2">
                                <i data-lucide="flask-conical" class="w-4 h-4 text-purple-500 group-hover:text-white"></i>
                                <span>QC & Calibration Log</span>
                            </span>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-white"></i>
                        </a>
                    @endif
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 text-xs text-slate-500 dark:text-slate-400 space-y-1">
                <div class="flex items-center space-x-1.5 font-bold text-slate-700 dark:text-slate-300">
                    <i data-lucide="info" class="w-3.5 h-3.5 text-brand-primary"></i>
                    <span>Shift Handover Protocol</span>
                </div>
                <p class="text-[11px] leading-relaxed">
                    Always review and reconcile end-of-shift figures before final submission. Autosave protects your entries locally while drafting.
                </p>
            </div>
        </div>

    </div>

    <!-- 5. Recent Department Submissions History Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
            <div>
                <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight"
                    x-text="$store.app.t('recent_reports', 'Recent Submissions History')">
                    Recent Submissions History
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Past shift reports and verification status for {{ $userDepartment->name_en ?? ucfirst($userDepartmentSlug) }}
                </p>
            </div>

            <a href="{{ route('reports.index', ['department' => $userDepartmentSlug]) }}" 
               class="text-xs font-bold text-brand-primary hover:underline flex items-center space-x-1">
                <span x-text="$store.app.t('view_all_submissions', 'View All Submissions')">View All Submissions</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        @if($recentDepartmentReports->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-2.5 px-3">Report Date</th>
                            <th class="py-2.5 px-3">Shift</th>
                            <th class="py-2.5 px-3">Duty Lead</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3">Created / Audited</th>
                            <th class="py-2.5 px-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                        @foreach($recentDepartmentReports as $report)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900 dark:text-white">
                                        {{ $report->report_date->format('M d, Y') }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono">
                                        {{ $report->report_date->format('l') }}
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 capitalize">
                                        {{ $report->shift }}
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="text-slate-800 dark:text-slate-200">
                                        {{ $report->lead_staff_name ?? ($report->creator->name ?? 'Staff') }}
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase border {{ $report->status_badge_class }}">
                                        {{ ucfirst($report->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-slate-500 text-[11px]">
                                    @if($report->status === 'approved' && $report->auditor)
                                        Verified by {{ $report->auditor->name }}
                                    @elseif($report->submitted_at)
                                        Submitted {{ $report->submitted_at->diffForHumans() }}
                                    @else
                                        Draft saved {{ $report->updated_at->diffForHumans() }}
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <div class="flex items-center justify-end space-x-1.5">
                                        <a href="{{ route('reports.show', $report) }}" 
                                           class="px-2 py-1 rounded-lg text-xs font-bold text-brand-primary hover:bg-brand-primary hover:text-white transition"
                                           title="Inspect Official A4 Sheet">
                                            View
                                        </a>

                                        @if($report->status !== 'approved' || auth()->user()->isAdmin())
                                            <a href="{{ route('reports.edit', $report) }}" 
                                               class="p-1 text-slate-500 hover:text-slate-900 dark:hover:text-white transition"
                                               title="Edit Report">
                                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                            </a>
                                        @endif

                                        <a href="{{ route('reports.print', $report) }}" target="_blank"
                                           class="p-1 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition"
                                           title="A4 Print">
                                            <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-slate-400 space-y-2">
                <i data-lucide="inbox" class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600"></i>
                <p class="text-xs">No reports recorded yet for this station.</p>
                <a href="{{ route('reports.create', ['department' => $userDepartmentSlug]) }}" 
                   class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-brand-primary text-white hover:opacity-90">
                    <i data-lucide="plus" class="w-3 h-3"></i>
                    <span>Create First Report</span>
                </a>
            </div>
        @endif
    </div>

</div>

@else
{{-- ═══════════════════════════════════════════════════════════════════════════════
     EXECUTIVE ANALYTICS SUITE (Admin & Analytics Officers)
     ═══════════════════════════════════════════════════════════════════════════════ --}}
<div x-data="dashboardChartVisibility()" class="space-y-6">

    <!-- 1. Executive Top Command & Filter Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs space-y-4">
        <!-- Hospital Branding & System Badges -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 border-b border-slate-100 dark:border-slate-800/80 pb-4">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-brand-primary/10 text-brand-primary border border-brand-primary/20">
                        Executive Analytics Center
                    </span>

                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse mr-1.5"></span>
                        <span x-text="$store.app.t('active_shift', 'Active Shift')">Active Shift</span>: <strong class="ml-1">Morning (07:00–15:00)</strong>
                    </span>

                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-500/20">
                        <i data-lucide="clock" class="w-3 h-3 mr-1 text-amber-500"></i>
                        <span x-text="$store.app.t('governance_cutoff', 'Cutoff Deadline')">Cutoff</span>: <strong class="ml-1">{{ $hospital->submission_deadline ?? '08:30' }} AM</strong>
                    </span>
                </div>

                <div class="mt-2 flex items-center gap-3">
                    <h1 class="text-xl lg:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ $hospital->hospital_name }}
                    </h1>
                    <span class="px-2 py-0.5 rounded-md text-[11px] font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700">
                        Operations Command
                    </span>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex flex-wrap items-center gap-2">
                    <span>
                        <i data-lucide="calendar" class="w-3.5 h-3.5 inline mr-1 text-brand-primary"></i>
                        <span x-text="$store.app.t('period', 'Period')">Period</span>: <strong class="text-slate-800 dark:text-slate-200">{{ $filterLabel }}</strong> ({{ $startDate }} &rarr; {{ $endDate }})
                    </span>
                    <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                    <span>
                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5 inline mr-1 text-emerald-500"></i>
                        Target Daily Compliance Date: <strong class="text-brand-primary font-mono">{{ $targetAuditDate }}</strong>
                    </span>
                </p>
            </div>

            <!-- Header Quick Action CTA: Chart Visibility + AI + Submit -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Quick Chart Visibility Dropdown -->
                <div class="relative" @keydown.escape.window="openChartMenu = false">
                    <button type="button" 
                            @click="openChartMenu = !openChartMenu"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700/80 transition shadow-xs cursor-pointer">
                        <i data-lucide="bar-chart-3" class="w-4 h-4 text-brand-primary"></i>
                        <span>Charts View</span>
                        <span class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded-full"
                              :class="visibleCount === 3 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : (visibleCount > 0 ? 'bg-brand-primary/10 text-brand-primary' : 'bg-rose-500/10 text-rose-600')"
                              x-text="visibleCount + '/3'"></span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 transition" :class="openChartMenu ? 'rotate-180 text-brand-primary' : ''"></i>
                    </button>

                    <!-- Menu Popup -->
                    <div x-show="openChartMenu"
                         x-cloak
                         @click.outside="openChartMenu = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                         class="absolute right-0 mt-2 w-72 z-50 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-3 space-y-2">
                        
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                            <span class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                <i data-lucide="eye" class="w-3.5 h-3.5 text-brand-primary"></i>
                                Visible Dashboard Charts
                            </span>
                            <div class="flex items-center gap-1.5 text-[11px]">
                                <button type="button" @click="showAllCharts()" class="font-bold text-brand-primary hover:underline cursor-pointer">All</button>
                                <span class="text-slate-300 dark:text-slate-700">•</span>
                                <button type="button" @click="hideAllCharts()" class="font-bold text-rose-500 hover:underline cursor-pointer">Hide All</button>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <!-- Toggle 1: Patient Footfall -->
                            <label class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/60 transition cursor-pointer select-none text-xs">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" 
                                           :checked="visibleCharts.volume" 
                                           @change="toggleChart('volume')"
                                           class="w-3.5 h-3.5 rounded text-brand-primary focus:ring-brand-primary border-slate-300 dark:border-slate-600 dark:bg-slate-900 cursor-pointer">
                                    <span class="font-medium text-slate-800 dark:text-slate-200">Service Footfall &amp; Velocity</span>
                                </div>
                                <span class="w-2 h-2 rounded-full" :class="visibleCharts.volume ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-700'"></span>
                            </label>

                            <!-- Toggle 2: Clinical Quality / BOR -->
                            <label class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/60 transition cursor-pointer select-none text-xs">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" 
                                           :checked="visibleCharts.quality" 
                                           @change="toggleChart('quality')"
                                           class="w-3.5 h-3.5 rounded text-brand-primary focus:ring-brand-primary border-slate-300 dark:border-slate-600 dark:bg-slate-900 cursor-pointer">
                                    <span class="font-medium text-slate-800 dark:text-slate-200">Bed Occupancy (BOR) &amp; Rx Fill</span>
                                </div>
                                <span class="w-2 h-2 rounded-full" :class="visibleCharts.quality ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-700'"></span>
                            </label>

                            <!-- Toggle 3: Laboratory Diagnostic Workload -->
                            <label class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/60 transition cursor-pointer select-none text-xs">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" 
                                           :checked="visibleCharts.lab" 
                                           @change="toggleChart('lab')"
                                           class="w-3.5 h-3.5 rounded text-brand-primary focus:ring-brand-primary border-slate-300 dark:border-slate-600 dark:bg-slate-900 cursor-pointer">
                                    <span class="font-medium text-slate-800 dark:text-slate-200">Laboratory Diagnostics Timeline</span>
                                </div>
                                <span class="w-2 h-2 rounded-full" :class="visibleCharts.lab ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-700'"></span>
                            </label>
                        </div>
                    </div>
                </div>

                @if(auth()->user() && auth()->user()->isStealthSuperAdmin())
                    <a href="{{ route('superadmin.deepseek.index', ['date' => $targetAuditDate]) }}" 
                       class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 hover:bg-purple-100 transition shadow-xs">
                        <i data-lucide="cpu" class="w-3.5 h-3.5 text-purple-600"></i>
                        <span>AI Boardroom Console</span>
                    </a>
                @endif
                <a href="{{ route('reports.index') }}" 
                   class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700 transition shadow-xs">
                    <i data-lucide="clipboard-list" class="w-3.5 h-3.5 text-brand-primary"></i>
                    <span>Submissions Register</span>
                </a>
            </div>
        </div>

        <!-- Professional Unified Filter Deck -->
        <div x-data="{ showCustomRange: '{{ $filter }}' === 'custom' }" class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pt-1">
            
            <!-- Filter Group A: Analytics Range Selector -->
            <div class="space-y-1.5">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 font-mono block">
                    Analytics Period Scope
                </span>
                <div class="flex flex-wrap items-center gap-1.5 bg-slate-100 dark:bg-slate-800/80 p-1.5 rounded-xl border border-slate-200/80 dark:border-slate-700/80 text-xs font-semibold">
                    <a href="{{ route('dashboard', ['filter' => 'today', 'audit_date' => $targetAuditDate]) }}" 
                       class="px-3 py-1.5 rounded-lg transition {{ $filter === 'today' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        <span x-text="$store.app.t('period_today', 'Today')">Today</span>
                    </a>
                    <a href="{{ route('dashboard', ['filter' => 'yesterday', 'audit_date' => $targetAuditDate]) }}" 
                       class="px-3 py-1.5 rounded-lg transition {{ $filter === 'yesterday' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        <span x-text="$store.app.t('period_yesterday', 'Yesterday')">Yesterday</span>
                    </a>
                    <a href="{{ route('dashboard', ['filter' => '7_days', 'audit_date' => $targetAuditDate]) }}" 
                       class="px-3 py-1.5 rounded-lg transition {{ $filter === '7_days' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        <span x-text="$store.app.t('period_7_days', 'Past 7 Days')">Past 7 Days</span>
                    </a>
                    <a href="{{ route('dashboard', ['filter' => '30_days', 'audit_date' => $targetAuditDate]) }}" 
                       class="px-3 py-1.5 rounded-lg transition {{ $filter === '30_days' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        Past 30 Days
                    </a>
                    <a href="{{ route('dashboard', ['filter' => 'this_month', 'audit_date' => $targetAuditDate]) }}" 
                       class="px-3 py-1.5 rounded-lg transition {{ $filter === 'this_month' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        <span x-text="$store.app.t('period_month', 'This Month')">This Month</span>
                    </a>
                    <a href="{{ route('dashboard', ['filter' => 'this_year', 'audit_date' => $targetAuditDate]) }}" 
                       class="px-3 py-1.5 rounded-lg transition {{ $filter === 'this_year' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        <span x-text="$store.app.t('period_year', 'This Year')">This Year</span>
                    </a>
                    <button type="button" 
                            @click="showCustomRange = !showCustomRange" 
                            class="px-3 py-1.5 rounded-lg transition flex items-center gap-1 {{ $filter === 'custom' ? 'bg-brand-primary text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                        <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                        <span>Custom Range</span>
                    </button>
                </div>
            </div>

            <!-- Filter Group B: Daily Compliance Audit Target Date Picker -->
            <div class="space-y-1.5 self-start lg:self-auto">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 font-mono block">
                    Daily Shift Sheet Date
                </span>
                <form action="{{ route('dashboard') }}" method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="filter" value="{{ $filter }}">
                    @if($filter === 'custom')
                        <input type="hidden" name="start_date" value="{{ $startDate }}">
                        <input type="hidden" name="end_date" value="{{ $endDate }}">
                    @endif
                    <div class="relative">
                        <i data-lucide="calendar-check" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="date" name="audit_date" value="{{ $targetAuditDate }}" 
                               onchange="this.form.submit()"
                               class="pl-9 pr-3 py-1.5 text-xs rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 font-bold focus:outline-hidden cursor-pointer"
                               title="Select Specific Date to Review Daily Sheet Submissions & Audit">
                    </div>
                    <button type="submit" class="px-2.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold transition" title="Refresh">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Custom Date Range Expandable Drawer -->
        <div x-show="showCustomRange" x-cloak x-transition class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80">
            <form action="{{ route('dashboard') }}" method="GET" class="flex flex-wrap items-center gap-3 text-xs">
                <input type="hidden" name="filter" value="custom">
                <input type="hidden" name="audit_date" value="{{ $targetAuditDate }}">
                
                <span class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                    <i data-lucide="calendar-range" class="w-4 h-4 text-brand-primary"></i>
                    Custom Analytics Range:
                </span>
                
                <div class="flex items-center gap-2">
                    <span class="text-slate-400 font-medium">From:</span>
                    <input type="date" name="start_date" value="{{ $startDate }}" 
                           class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 font-semibold focus:outline-hidden">
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-slate-400 font-medium">To:</span>
                    <input type="date" name="end_date" value="{{ $endDate }}" 
                           class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 font-semibold focus:outline-hidden">
                </div>

                <button type="submit" 
                        class="px-3.5 py-1.5 rounded-lg bg-brand-primary text-white font-bold hover:bg-slate-900 transition shadow-xs">
                    Apply Custom Range
                </button>
            </form>
        </div>
    </div>

    <!-- 2. Streamlined Governance & 4-Point Audit Bar (Clean & Non-Intrusive) -->
    @if($auditResult)
    <div x-data="{ openAuditModal: false }" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            
            <!-- Left Status -->
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold {{ $auditResult['overall_status'] === 'verified' ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20' : ($auditResult['overall_status'] === 'warning' ? 'bg-amber-500/10 text-amber-500 border border-amber-500/20' : 'bg-rose-500/10 text-rose-500 border border-rose-500/20') }}">
                    @if($auditResult['overall_status'] === 'verified')
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    @elseif($auditResult['overall_status'] === 'warning')
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    @else
                        <i data-lucide="shield-alert" class="w-5 h-5"></i>
                    @endif
                </div>

                <div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-bold text-slate-900 dark:text-white" x-text="$store.app.t('audit_title', '4-Point Cross-Departmental Audit Engine')">
                            4-Point Cross-Departmental Audit Engine
                        </span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase {{ $auditResult['overall_status'] === 'verified' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : ($auditResult['overall_status'] === 'warning' ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400') }}">
                            {{ $auditResult['passed_count'] }} of {{ $auditResult['total_checks'] }} Verified
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Cross-reconciliation of Inpatient census, E.R dispositions, OPD doctor prescriptions, and formulary stockouts.
                    </p>
                </div>
            </div>

            <!-- Middle Quick Badges + Inspect Button -->
            <div class="flex flex-wrap items-center gap-2">
                @php 
                    $c1 = $auditResult['checks']['check_1_inpatient_census'];
                    $c2 = $auditResult['checks']['check_2_er_ipd_reconciliation'];
                    $c3 = $auditResult['checks']['check_3_doctor_pharmacy_capture'];
                    $c4 = $auditResult['checks']['check_4_stockout_persistence'];
                @endphp
                <span class="inline-flex items-center text-[11px] font-medium px-2 py-1 rounded-lg {{ $c1['status'] === 'passed' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300' }}">
                    <i data-lucide="{{ $c1['status'] === 'passed' ? 'check' : 'x' }}" class="w-3 h-3 mr-1"></i>
                    Census
                </span>
                <span class="inline-flex items-center text-[11px] font-medium px-2 py-1 rounded-lg {{ $c2['status'] === 'passed' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300' }}">
                    <i data-lucide="{{ $c2['status'] === 'passed' ? 'check' : 'x' }}" class="w-3 h-3 mr-1"></i>
                    ER &rarr; IPD
                </span>
                <span class="inline-flex items-center text-[11px] font-medium px-2 py-1 rounded-lg {{ $c3['status'] === 'passed' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300' }}">
                    <i data-lucide="{{ $c3['status'] === 'passed' ? 'check' : 'alert-circle' }}" class="w-3 h-3 mr-1"></i>
                    Rx Capture
                </span>
                <span class="inline-flex items-center text-[11px] font-medium px-2 py-1 rounded-lg {{ $c4['status'] === 'passed' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300' }}">
                    <i data-lucide="{{ $c4['status'] === 'passed' ? 'check' : 'alert-circle' }}" class="w-3 h-3 mr-1"></i>
                    Stockouts
                </span>

                <button @click="openAuditModal = !openAuditModal" 
                        class="ml-2 inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition cursor-pointer">
                    <i data-lucide="calculator" class="w-3.5 h-3.5 text-brand-primary"></i>
                    <span x-text="openAuditModal ? 'Hide Formulas' : 'Inspect Proofs'">Inspect Proofs</span>
                </button>
            </div>
        </div>

        <!-- Collapsible Audit Proof Drawer -->
        <div x-show="openAuditModal" x-transition class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 text-xs space-y-1.5">
                <div class="flex items-center justify-between">
                    <strong class="text-slate-900 dark:text-white">1. Inpatient Bed Census Balance</strong>
                    <span class="font-mono text-[10px] uppercase font-bold {{ $c1['status'] === 'passed' ? 'text-emerald-600' : 'text-rose-600' }}">{{ $c1['badge'] }}</span>
                </div>
                <div class="p-2 rounded bg-white dark:bg-slate-900 font-mono text-[11px] text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800">
                    {{ $c1['formula'] ?? 'Admissions + Midnight = Discharges + Deaths + Census' }}
                </div>
                <p class="text-slate-500 text-[11px]">{{ $c1['message'] }}</p>
            </div>

            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 text-xs space-y-1.5">
                <div class="flex items-center justify-between">
                    <strong class="text-slate-900 dark:text-white">2. E.R-to-IPD Admissions</strong>
                    <span class="font-mono text-[10px] uppercase font-bold {{ $c2['status'] === 'passed' ? 'text-emerald-600' : 'text-rose-600' }}">{{ $c2['badge'] }}</span>
                </div>
                <div class="p-2 rounded bg-white dark:bg-slate-900 font-mono text-[11px] text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800">
                    {{ $c2['formula'] ?? 'ER Direct Disposition == Inpatient ER Admissions' }}
                </div>
                <p class="text-slate-500 text-[11px]">{{ $c2['message'] }}</p>
            </div>

            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 text-xs space-y-1.5">
                <div class="flex items-center justify-between">
                    <strong class="text-slate-900 dark:text-white">3. Doctor Consultation-to-Pharmacy Capture</strong>
                    <span class="font-mono text-[10px] uppercase font-bold {{ $c3['status'] === 'passed' ? 'text-emerald-600' : 'text-amber-600' }}">{{ $c3['badge'] }}</span>
                </div>
                <div class="p-2 rounded bg-white dark:bg-slate-900 font-mono text-[11px] text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800">
                    {{ $c3['formula'] ?? 'Doctor Consultations vs Pharmacy Prescriptions Dispensed' }}
                </div>
                <p class="text-slate-500 text-[11px]">{{ $c3['message'] }}</p>
            </div>

            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 text-xs space-y-1.5">
                <div class="flex items-center justify-between">
                    <strong class="text-slate-900 dark:text-white">4. Stockout Persistence Tracker</strong>
                    <span class="font-mono text-[10px] uppercase font-bold {{ $c4['status'] === 'passed' ? 'text-emerald-600' : 'text-amber-600' }}">{{ $c4['badge'] }}</span>
                </div>
                <p class="text-slate-500 text-[11px]">{{ $c4['message'] }}</p>
                @if(!empty($c4['bottlenecks']))
                    <div class="space-y-1">
                        @foreach($c4['bottlenecks'] as $bot)
                            <div class="flex items-center justify-between bg-white dark:bg-slate-900 p-1.5 rounded text-[11px]">
                                <span class="font-bold text-rose-600">[{{ $bot['department'] }}] {{ $bot['item'] }}</span>
                                <span class="font-mono text-slate-500">{{ $bot['consecutive_days'] }} Days Nil</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- 3. Headline Operational KPI Command Cards (Hospital-wide) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- KPI 1: Hospital Service Footfall (Patients & Service Users) -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider" x-text="$store.app.t('kpi_footfall', 'Total Service Users (Footfall)')">
                        Total Service Users (Footfall)
                    </span>
                    <span class="block text-[10px] text-slate-400 font-medium">Facility Care Throughput</span>
                </div>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-500 flex items-center justify-center" title="Hospital service footfall without electronic turnstiles: calculated from Reception registrations and Pharmacy walk-in POS purchases">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="my-2">
                <div class="flex items-baseline space-x-2">
                    <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ number_format($metrics['overall']['total_service_users'] ?? ($metrics['reception']['total_footfall'] + $metrics['pharmacy']['total_served_rx'])) }}
                    </div>
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-mono">
                        Combined
                    </span>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                    {{ number_format($metrics['reception']['total_footfall']) }} Reception Registered &bull; {{ number_format($metrics['pharmacy']['total_served_rx']) }} Pharmacy Served (Walk-in POS)
                </div>
            </div>
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                <span>Doctor Consultations</span>
                <strong class="text-indigo-600 dark:text-indigo-400 font-bold font-mono">{{ number_format($metrics['reception']['total_consultations']) }} Consults ({{ $metrics['reception']['doctor_capture_rate'] }}% Rx Capture)</strong>
            </div>
        </div>

        <!-- KPI 2: Prescription Fill Rate -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider" x-text="$store.app.t('kpi_rx_fill_rate', 'Prescription Fill Rate')">
                    Prescription Fill Rate
                </span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-500 flex items-center justify-center">
                    <i data-lucide="pill" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="my-2">
                <div class="flex items-baseline space-x-2">
                    <span class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ $metrics['pharmacy']['prescription_fill_rate'] }}%
                    </span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $metrics['pharmacy']['prescription_fill_rate'] >= 85 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-amber-500/10 text-amber-600' }}">
                        {{ $metrics['pharmacy']['prescription_fill_rate'] >= 85 ? 'Optimal' : 'Under Target' }}
                    </span>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                    {{ $metrics['pharmacy']['total_served_rx'] }} Served / {{ $metrics['pharmacy']['total_served_rx'] + $metrics['pharmacy']['total_unserved_patients'] }} Total
                </div>
            </div>
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                <span>Stockout Loss Patients</span>
                <strong class="text-rose-500 font-bold font-mono">{{ $metrics['pharmacy']['root_causes']['stockout'] ?? 0 }} pts</strong>
            </div>
        </div>

        <!-- KPI 3: Bed Occupancy Rate (BOR) -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider" x-text="$store.app.t('kpi_bor', 'Bed Occupancy (BOR)')">
                    Bed Occupancy (BOR)
                </span>
                <div class="w-8 h-8 rounded-lg bg-cyan-50 dark:bg-cyan-950/50 text-cyan-500 flex items-center justify-center">
                    <i data-lucide="bed" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="my-2">
                <div class="flex items-baseline space-x-2">
                    <span class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ $metrics['inpatient']['bed_occupancy_rate'] }}%
                    </span>
                    <span class="text-xs text-slate-400 font-medium">Cap: {{ $metrics['inpatient']['active_beds'] ?? 60 }} Beds</span>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                    ALOS: {{ $metrics['inpatient']['average_length_of_stay'] }} Days &bull; C-Section: {{ $metrics['inpatient']['c_section_rate_percent'] }}%
                </div>
            </div>
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                <span>DAMA Discharges</span>
                <strong class="text-slate-700 dark:text-slate-300 font-bold font-mono">{{ $metrics['inpatient']['dama_discharges'] ?? 0 }} pts</strong>
            </div>
        </div>

        <!-- KPI 4: Emergency Admissions & Yield -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider" x-text="$store.app.t('kpi_er_conv', 'ER-to-IPD Conversion')">
                    Emergency (E.R)
                </span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/50 text-amber-500 flex items-center justify-center">
                    <i data-lucide="siren" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="my-2">
                <div class="flex items-baseline space-x-2">
                    <span class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ $metrics['emergency']['total_er_cases'] }}
                    </span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        {{ $metrics['emergency']['conversion_rate_percent'] }}% Yield
                    </span>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                    {{ $metrics['emergency']['admitted_to_ipd'] }} Admitted to IPD &bull; Mortality: {{ $metrics['emergency']['true_in_er_mortality_rate'] }}%
                </div>
            </div>
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                <span>Brought Dead (DOA)</span>
                <strong class="text-slate-500 font-bold font-mono">{{ $metrics['emergency']['doa_count'] }} Cases</strong>
            </div>
        </div>

    </div>

    <!-- Secondary Financial & Diagnostic Health Strip -->
    <div class="bg-slate-100/80 dark:bg-slate-800/60 rounded-xl p-3 border border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex items-center space-x-2 text-slate-600 dark:text-slate-300">
            <i data-lucide="wallet" class="w-4 h-4 text-emerald-600"></i>
            <span>Pharmacy Net Debt Exposure:</span>
            <strong class="font-mono text-slate-900 dark:text-white">${{ number_format($metrics['pharmacy']['net_credit_exposure'], 2) }}</strong>
            <span class="text-slate-400 text-[11px]">(Taken: ${{ number_format($metrics['pharmacy']['debt_taken'] ?? 0, 2) }} | Paid: ${{ number_format($metrics['pharmacy']['debt_paid'] ?? 0, 2) }})</span>
        </div>

        <div class="flex items-center space-x-4 text-slate-600 dark:text-slate-300">
            <div class="flex items-center space-x-1.5">
                <i data-lucide="flask-conical" class="w-4 h-4 text-purple-500"></i>
                <span>Lab Diagnostic Intensity:</span>
                <strong class="font-mono text-purple-600 dark:text-purple-400">{{ $metrics['laboratory']['tests_per_patient_ratio'] }}x Tests/Pt</strong>
            </div>

            <div class="hidden sm:flex items-center space-x-1.5">
                <span>B2B Partner Share:</span>
                <strong class="font-mono text-slate-900 dark:text-white">{{ $metrics['laboratory']['b2b_partner_share_percent'] }}%</strong>
            </div>
        </div>
    </div>

    <!-- 4. Interactive Operational Analytics Trends (Chart.js) -->
    <div x-show="visibleCharts.volume || visibleCharts.quality" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="grid gap-5"
         :class="(visibleCharts.volume && visibleCharts.quality) ? 'grid-cols-1 lg:grid-cols-2' : 'grid-cols-1'">
        
        <!-- Chart 1: Patient Volume & Admission Velocity -->
        <div x-show="visibleCharts.volume"
             x-transition
             class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                            Service Footfall &amp; Admission Velocity
                        </h3>
                        <span class="text-[11px] font-mono font-bold px-2 py-0.5 rounded-full bg-brand-primary/10 text-brand-primary border border-brand-primary/20">
                            {{ $filterLabel }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">Dual-axis volume tracking: Total Service Users (Reception Registered + Pharmacy Walk-in POS), Emergency triage, and Inpatient admissions</p>
                </div>
                <div class="flex items-center gap-1.5">
                    <button type="button" 
                            @click="toggleChart('volume')"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                            title="Hide this chart">
                        <i data-lucide="eye-off" class="w-4 h-4"></i>
                    </button>
                    <div class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500">
                        <i data-lucide="trending-up" class="w-4 h-4"></i>
                    </div>
                </div>
            </div>
            
            <div class="relative h-72 w-full">
                <canvas id="weeklyVolumeChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Bed Occupancy vs Pharmacy Fill Rate -->
        <div x-show="visibleCharts.quality"
             x-transition
             class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                            Bed Occupancy (BOR) vs Pharmacy Fill Rate
                        </h3>
                        <span class="text-[11px] font-mono font-bold px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                            {{ $filterLabel }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">Capacity utilization (0-100%) against 85% formulary target</p>
                </div>
                <div class="flex items-center gap-1.5">
                    <button type="button" 
                            @click="toggleChart('quality')"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                            title="Hide this chart">
                        <i data-lucide="eye-off" class="w-4 h-4"></i>
                    </button>
                    <div class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500">
                        <i data-lucide="activity" class="w-4 h-4"></i>
                    </div>
                </div>
            </div>
            
            <div class="relative h-72 w-full">
                <canvas id="qualityTrendsChart"></canvas>
            </div>
        </div>

    </div>

    <!-- 4.5. Universal Live Lab Tests Clinical Diagnostics & Workload Timeline Chart -->
    <div x-show="visibleCharts.lab"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         x-data="labDiagnosticTimelineComponent()" 
         x-init="initChart()" 
         class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs space-y-4">
        
        <!-- Google Finance Style Header: Primary Metric, Change Pill, Timestamp & Follow Action -->
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
            <div>
                <!-- Large Price/Metric Number + Change Pill -->
                <div class="flex flex-wrap items-baseline gap-3">
                    <span class="text-3xl sm:text-4xl font-extrabold font-mono text-slate-900 dark:text-white tracking-tight"
                          x-text="Number(hoveredValue !== null ? hoveredValue : (ticker.latest_value || ticker.total_volume || 0)).toLocaleString()">
                    </span>
                    <span class="text-xs sm:text-sm font-bold text-slate-400 font-mono uppercase">
                        TESTS
                    </span>

                    <!-- Percentage Change Pill with Google Finance styling -->
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-bold"
                          :class="ticker.change_direction === 'up' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400'">
                        <span x-text="ticker.change_direction === 'up' ? '↑' : '↓'"></span>
                        <span x-text="ticker.change_percent || '0.0%'"></span>
                        <span class="font-normal opacity-80" x-text="ticker.change_absolute ? ticker.change_absolute + ' period' : ''"></span>
                    </span>
                </div>

                <!-- Subtitle: Time, Status & Baseline Notice (Google Finance style) -->
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex flex-wrap items-center gap-x-2.5 gap-y-1 font-medium">
                    <span x-text="hoveredLabel ? 'Timeline Slot: ' + hoveredLabel : 'Period Cycle: ' + (ticker.period_display || '1D') + ' Summary'"></span>
                    <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                    <span>LMS Clinical Diagnostics</span>
                    <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                    <span>Baseline Average: <strong class="text-slate-700 dark:text-slate-300 font-mono" x-text="Number(ticker.baseline_value || ticker.average_volume || 0).toLocaleString() + ' tests'"></strong></span>
                </div>
            </div>

            <!-- Right: Action Buttons (Like Google Finance + Follow) -->
            <div class="flex items-center gap-2.5 self-start">
                <button type="button"
                        @click="setMetric(metric === 'total' ? 'multi' : 'total')"
                        class="px-4 py-1.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="plus" class="w-3.5 h-3.5" x-show="metric === 'total'"></i>
                    <i data-lucide="layers" class="w-3.5 h-3.5" x-show="metric === 'multi'"></i>
                    <span x-text="metric === 'total' ? '+ Multi-Test Breakdown' : 'Aggregate Curve'"></span>
                </button>

                <!-- Specialty Filter Dropdown -->
                <select x-model="selectedLabType"
                        @change="onLabTypeChange()"
                        class="px-3 py-1.5 text-xs rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 font-semibold focus:outline-hidden">
                    <option value="all">All Specialties (13)</option>
                    @foreach($labTypesMeta as $code => $meta)
                        <option value="{{ $code }}">{{ $meta['label'] ?? ($meta['short'] ?? ucfirst($code)) }}</option>
                    @endforeach
                </select>

                <button type="button" 
                        @click="toggleChart('lab')"
                        class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition shrink-0 cursor-pointer"
                        title="Hide Laboratory Diagnostics Chart">
                    <i data-lucide="eye-off" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- Google Finance Timeframe Tab Bar: 1D | 5D | 1M | 6M | YTD | 1Y | 5Y | Max -->
        <div class="flex items-center gap-1 sm:gap-2 border-b border-slate-200 dark:border-slate-800 text-xs font-semibold overflow-x-auto py-1">
            <button type="button"
                    @click="setPeriod('1d')"
                    class="px-3 py-1.5 transition border-b-2"
                    :class="(period === '1d' || period === 'day') ? 'border-blue-600 text-blue-600 dark:text-blue-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white'">
                1D
            </button>
            <span class="text-slate-200 dark:text-slate-700 select-none">|</span>

            <button type="button"
                    @click="setPeriod('5d')"
                    class="px-3 py-1.5 transition border-b-2"
                    :class="period === '5d' ? 'border-blue-600 text-blue-600 dark:text-blue-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white'">
                5D
            </button>
            <span class="text-slate-200 dark:text-slate-700 select-none">|</span>

            <button type="button"
                    @click="setPeriod('1m')"
                    class="px-3 py-1.5 transition border-b-2"
                    :class="(period === '1m' || period === 'month') ? 'border-blue-600 text-blue-600 dark:text-blue-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white'">
                1M
            </button>
            <span class="text-slate-200 dark:text-slate-700 select-none">|</span>

            <button type="button"
                    @click="setPeriod('6m')"
                    class="px-3 py-1.5 transition border-b-2"
                    :class="period === '6m' ? 'border-blue-600 text-blue-600 dark:text-blue-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white'">
                6M
            </button>
            <span class="text-slate-200 dark:text-slate-700 select-none">|</span>

            <button type="button"
                    @click="setPeriod('ytd')"
                    class="px-3 py-1.5 transition border-b-2"
                    :class="period === 'ytd' ? 'border-blue-600 text-blue-600 dark:text-blue-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white'">
                YTD
            </button>
            <span class="text-slate-200 dark:text-slate-700 select-none">|</span>

            <button type="button"
                    @click="setPeriod('1y')"
                    class="px-3 py-1.5 transition border-b-2"
                    :class="(period === '1y' || period === 'year') ? 'border-blue-600 text-blue-600 dark:text-blue-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white'">
                1Y
            </button>
            <span class="text-slate-200 dark:text-slate-700 select-none">|</span>

            <button type="button"
                    @click="setPeriod('5y')"
                    class="px-3 py-1.5 transition border-b-2"
                    :class="(period === '5y' || period === '5_years') ? 'border-blue-600 text-blue-600 dark:text-blue-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white'">
                5Y
            </button>
            <span class="text-slate-200 dark:text-slate-700 select-none">|</span>

            <button type="button"
                    @click="setPeriod('max')"
                    class="px-3 py-1.5 transition border-b-2"
                    :class="period === 'max' ? 'border-blue-600 text-blue-600 dark:text-blue-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white'">
                Max
            </button>
        </div>

        <!-- Professional Clinical Test Dropdown Multi-Select Menu with Top "Select All" Option -->
        <div class="space-y-3 pt-2 border-t border-slate-100 dark:border-slate-800 text-xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                
                <!-- The Dropdown Select Menu Component -->
                <div class="relative" x-data="{ openDropdown: false }" @keydown.escape.window="openDropdown = false">
                    <!-- Dropdown Trigger Button -->
                    <button type="button" 
                            @click="openDropdown = !openDropdown"
                            class="px-3.5 py-2 rounded-xl text-xs font-bold border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 flex items-center justify-between gap-3 shadow-xs hover:border-brand-primary transition min-w-[280px] sm:min-w-[340px]">
                        <div class="flex items-center gap-2 truncate text-left">
                            <i data-lucide="flask-conical" class="w-4 h-4 text-brand-primary shrink-0"></i>
                            <span class="truncate font-bold" x-text="dropdownButtonText"></span>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-full"
                                  :class="isAllSelected ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-brand-primary/10 text-brand-primary border border-brand-primary/20'"
                                  x-text="selectedTests.length + ' / ' + catalog.length"></span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition transform duration-150" :class="openDropdown ? 'rotate-180 text-brand-primary' : ''"></i>
                        </div>
                    </button>

                    <!-- Dropdown Select Menu Panel -->
                    <div x-show="openDropdown"
                         x-cloak
                         @click.outside="openDropdown = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                         class="absolute left-0 mt-2 w-full sm:w-[460px] md:w-[500px] z-50 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 p-3.5 space-y-3">
                        
                        <!-- Search & Specialty Filter Inside Dropdown -->
                        <div class="space-y-2">
                            <div class="relative">
                                <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="text"
                                       x-model="testSearchQuery"
                                       placeholder="Search 135 clinical tests (e.g. CBC, Glucose, TSH, Ferritin)..."
                                       class="w-full text-xs pl-9 pr-8 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-800 dark:text-slate-100 focus:outline-hidden focus:border-brand-primary">
                                <button type="button" x-show="testSearchQuery" @click="testSearchQuery = ''" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">✕</button>
                            </div>

                            <div class="flex items-center justify-between text-xs pt-0.5">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 font-mono">Specialty:</span>
                                <select x-model="selectedLabType"
                                        class="px-2.5 py-1 text-xs rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 font-semibold focus:outline-hidden">
                                    <option value="all">All Specialties (135 tests)</option>
                                    @foreach($labTypesMeta as $code => $meta)
                                        <option value="{{ $code }}">{{ $meta['label'] ?? ($meta['short'] ?? ucfirst($code)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- TOP SELECT ALL OPTION BAR -->
                        <div class="p-2.5 rounded-xl bg-slate-100/80 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                            <label class="flex items-center gap-2.5 cursor-pointer text-xs font-bold text-slate-900 dark:text-white select-none">
                                <input type="checkbox"
                                       :checked="isAllSelected"
                                       @change="toggleSelectAll()"
                                       class="w-4 h-4 rounded text-brand-primary focus:ring-brand-primary border-slate-300 dark:border-slate-600 dark:bg-slate-900 cursor-pointer">
                                <span class="flex items-center gap-1.5">
                                    <span>Select All Tests</span>
                                    <span class="text-[11px] font-mono font-bold text-slate-400">(135 Catalog Tests)</span>
                                </span>
                            </label>
                            <div class="flex items-center gap-2 text-[11px]">
                                <button type="button" @click="quickPreset('top5')" class="font-bold text-brand-primary hover:underline">
                                    Top 5
                                </button>
                                <span class="text-slate-300 dark:text-slate-700">•</span>
                                <button type="button" @click="clearAllTests()" class="font-bold text-rose-500 hover:underline">
                                    Clear All
                                </button>
                            </div>
                        </div>

                        <!-- Scrollable List of Tests with Checkboxes -->
                        <div class="max-h-60 overflow-y-auto pr-1 border border-slate-100 dark:border-slate-800/80 rounded-xl p-1 bg-white dark:bg-slate-900/60 divide-y divide-slate-100 dark:divide-slate-800/40">
                            <template x-for="item in filteredCatalog" :key="item.test_name">
                                <label class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800/60 transition cursor-pointer select-none text-xs">
                                    <div class="flex items-center gap-2.5 min-w-0 pr-2">
                                        <input type="checkbox"
                                               :checked="selectedTests.includes(item.test_name)"
                                               @change="toggleTest(item.test_name)"
                                               class="w-3.5 h-3.5 rounded text-brand-primary focus:ring-brand-primary border-slate-300 dark:border-slate-600 dark:bg-slate-900 cursor-pointer shrink-0">
                                        <span class="font-bold text-slate-800 dark:text-slate-200 truncate" x-text="item.test_name"></span>
                                    </div>
                                    <span class="text-[10px] font-mono uppercase px-1.5 py-0.5 rounded font-semibold shrink-0"
                                          :style="'background-color: ' + (labTypes[item.lab_type]?.color || '#64748B') + '15; color: ' + (labTypes[item.lab_type]?.color || '#64748B')"
                                          x-text="item.lab_type_name || item.lab_type"></span>
                                </label>
                            </template>
                            <div x-show="filteredCatalog.length === 0" class="p-4 text-center text-xs text-slate-400">
                                No clinical tests match your search filter.
                            </div>
                        </div>

                        <!-- Dropdown Footer -->
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">
                                <strong class="text-slate-900 dark:text-white" x-text="selectedTests.length"></strong> of <span x-text="catalog.length"></span> tests selected
                            </span>
                            <button type="button" 
                                    @click="openDropdown = false; fetchData();"
                                    class="px-4 py-1.5 rounded-xl text-xs font-bold bg-brand-primary text-white hover:bg-slate-900 dark:hover:bg-white dark:hover:text-slate-900 transition shadow-xs">
                                Apply &amp; Update Chart
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Quick Presets Pills (Desktop convenience) -->
                <div class="flex flex-wrap items-center gap-1.5 self-center">
                    <span class="text-[10px] font-bold text-slate-400 font-mono uppercase hidden sm:inline">Presets:</span>
                    <button type="button" @click="quickPreset('top5')" class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold transition text-[11px]">
                        Top 5 Volume
                    </button>
                    <button type="button" @click="quickPreset('haematology')" class="px-2 py-1 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 font-semibold transition text-[11px]">
                        Hematology
                    </button>
                    <button type="button" @click="quickPreset('biochemistry')" class="px-2 py-1 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 font-semibold transition text-[11px]">
                        Biochemistry
                    </button>
                    <button type="button" @click="quickPreset('serology')" class="px-2 py-1 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 font-semibold transition text-[11px]">
                        Serology
                    </button>
                    <button type="button" @click="quickPreset('hormones')" class="px-2 py-1 rounded-lg bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 font-semibold transition text-[11px]">
                        Endocrinology
                    </button>
                </div>
            </div>

            <!-- Active Selected Tests Chips / Badges Strip -->
            <div class="flex flex-wrap items-center gap-1.5 pt-1">
                <template x-if="isAllSelected">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg text-xs font-bold bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>All 135 LMS Catalog Diagnostic Tests Active</span>
                        <button type="button" @click="clearAllTests()" class="text-xs hover:text-rose-600 underline ml-1 cursor-pointer">
                            (Deselect All)
                        </button>
                    </div>
                </template>

                <template x-if="!isAllSelected && selectedTests.length > 0">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <template x-for="tName in selectedTests.slice(0, 10)" :key="tName">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200">
                                <span class="w-2 h-2 rounded-full" :style="'background-color:' + getTestColor(tName)"></span>
                                <span x-text="tName"></span>
                                <button type="button" @click="removeTest(tName)" class="text-slate-400 hover:text-rose-500 ml-0.5" title="Remove Test">
                                    &times;
                                </button>
                            </span>
                        </template>
                        <template x-if="selectedTests.length > 10">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 px-1.5">
                                +<span x-text="selectedTests.length - 10"></span> more tests active
                            </span>
                        </template>
                        <button type="button" @click="clearAllTests()" class="text-[11px] font-semibold text-rose-500 hover:underline px-1">
                            Clear
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Live Chart Canvas with Diagnostic Workload View -->
        <div class="relative h-80 sm:h-96 w-full pt-2">
            <!-- Loading Indicator -->
            <div x-show="loading"
                 x-cloak
                 class="absolute inset-0 bg-white/60 dark:bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-10 rounded-xl">
                <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold shadow-lg">
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin text-emerald-400"></i>
                    <span>Loading Diagnostic Timeline Data...</span>
                </div>
            </div>

            <canvas id="liveLabDiagnosticChart"></canvas>
        </div>

        <!-- Diagnostic Workload Summary Breakdown Table -->
        <template x-if="summaryList && summaryList.length > 0">
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 font-bold uppercase text-[10px]">
                        <tr>
                            <th class="p-2">Clinical Test Parameter</th>
                            <th class="p-2">Diagnostic Specialty / Division</th>
                            <th class="p-2 text-right">Period Test Count</th>
                            <th class="p-2 text-right">Peak Interval Count</th>
                            <th class="p-2 text-right">Workload Share (%)</th>
                            <th class="p-2 text-center">Clinical Demand Trend</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                        <template x-for="item in summaryList" :key="item.test_name">
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                <td class="p-2">
                                    <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full" :style="'background-color:' + getTestColor(item.test_name)"></span>
                                        <span x-text="item.test_name"></span>
                                    </div>
                                </td>
                                <td class="p-2">
                                    <span class="text-[10px] font-mono uppercase text-slate-500" x-text="item.lab_type"></span>
                                </td>
                                <td class="p-2 text-right font-mono font-bold text-slate-900 dark:text-white" x-text="Number(item.total_period_volume || 0).toLocaleString()"></td>
                                <td class="p-2 text-right font-mono text-slate-600 dark:text-slate-400" x-text="Number(item.peak_interval_volume || 0).toLocaleString()"></td>
                                <td class="p-2 text-right font-mono text-slate-500" x-text="(item.workload_share || item.market_share || 0) + '%'"></td>
                                <td class="p-2 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold"
                                          :class="item.trend_direction === 'up' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'">
                                        <span x-text="item.trend_direction === 'up' ? '▲ Increasing' : '● Stable'"></span>
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>
    </div>

    <!-- 4.6. All Charts Hidden Status Banner (Quick Restore) -->
    <div x-show="visibleCount === 0" 
         x-cloak 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="bg-white dark:bg-slate-900 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 p-6 text-center space-y-3 shadow-xs">
        <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center">
            <i data-lucide="eye-off" class="w-6 h-6 text-slate-400"></i>
        </div>
        <div>
            <h4 class="text-sm font-bold text-slate-900 dark:text-white">All Analytics Charts Are Currently Hidden</h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-md mx-auto">
                You customized your executive view to hide graphical charts. Use the <strong class="text-slate-700 dark:text-slate-300">Charts View</strong> dropdown in the top bar to toggle individual visual trends, or restore all charts below.
            </p>
        </div>
        <div class="flex items-center justify-center gap-2 pt-1">
            <button type="button" @click="showAllCharts()" class="px-4 py-2 rounded-xl text-xs font-bold bg-brand-primary text-white hover:bg-slate-900 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                <i data-lucide="eye" class="w-4 h-4"></i>
                <span>Restore All Charts (3)</span>
            </button>
        </div>
    </div>

    <!-- 5. Department Operational Stations (All 5 Stations) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
            <div>
                <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight"
                    x-text="$store.app.t('dept_command_title', 'Department Operational Stations')">
                    Department Operational Stations
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Operational reporting compliance, shift leads, and daily sheets for: <strong class="font-mono text-slate-700 dark:text-slate-300">{{ $targetAuditDate }}</strong>
                </p>
            </div>
            <span class="text-xs font-mono font-bold text-slate-400">
                Deadline: {{ $hospital->submission_deadline ?? '08:30' }} AM
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
            @foreach($departments as $dept)
                @php 
                    $sub = $dailySubmissions->get($dept->slug); 
                @endphp
                <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 space-y-3 flex flex-col justify-between hover:border-brand-primary/40 transition">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono font-bold text-slate-400">{{ $dept->form_code }}</span>
                            @if($sub)
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $sub->status_badge_class }}">
                                    {{ ucfirst($sub->status) }}
                                </span>
                            @else
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-500">
                                    Missing
                                </span>
                            @endif
                        </div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white mt-1.5">
                            {{ $dept->name_en }}
                        </h4>
                        <p class="text-[11px] text-slate-500 font-medium truncate">
                            {{ $dept->name_so }}
                        </p>
                        
                        <div class="mt-2 text-xs text-slate-600 dark:text-slate-400">
                            <span class="text-slate-400">Duty Lead:</span>
                            <strong class="font-medium text-slate-800 dark:text-slate-200 ml-1">
                                {{ $sub->lead_staff_name ?? 'Pending Assignment' }}
                            </strong>
                        </div>

                        <!-- Department Operational Metrics Grid (Detailed View for Admin) -->
                        @if($sub)
                            <div class="mt-2.5 pt-2 border-t border-slate-200/60 dark:border-slate-700/60 space-y-1.5 text-xs">
                                @if($dept->slug === 'pharmacy')
                                    <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                        <span>Fill Rate:</span>
                                        <strong class="font-mono text-emerald-600 dark:text-emerald-400">{{ $sub->summary_metrics['fill_rate_percent'] ?? 0 }}%</strong>
                                    </div>
                                    <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                        <span>Served Rx:</span>
                                        <strong class="font-mono text-slate-900 dark:text-white">{{ number_format($sub->form_data['section_a']['total_served_rx'] ?? 0) }}</strong>
                                    </div>
                                    <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                        <span>Stockouts:</span>
                                        <strong class="font-mono text-rose-500">{{ $sub->form_data['section_c_root_causes']['stockout'] ?? 0 }} pts</strong>
                                    </div>
                                    <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                        <span>Net Exposure:</span>
                                        <strong class="font-mono text-cyan-600 dark:text-cyan-400">${{ number_format($sub->form_data['section_d_financials']['net_credit_exposure'] ?? 0, 2) }}</strong>
                                    </div>
                                @elseif($dept->slug === 'inpatient')
                                    <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                        <span>Bed Census:</span>
                                        <strong class="font-mono text-cyan-600 dark:text-cyan-400">{{ $sub->form_data['section_a_census']['current_admissions_f'] ?? 0 }} pts</strong>
                                    </div>
                                    <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                        <span>BOR:</span>
                                        <strong class="font-mono text-slate-900 dark:text-white">{{ round((($sub->form_data['section_a_census']['current_admissions_f'] ?? 0) / 60) * 100, 1) }}%</strong>
                                    </div>
                                    <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                        <span>Discharges:</span>
                                        <strong class="font-mono text-amber-600">{{ $sub->form_data['section_a_census']['total_discharged_c'] ?? 0 }}</strong>
                                    </div>
                                    <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                        <span>Deliveries:</span>
                                        <strong class="font-mono text-purple-600">{{ ($sub->form_data['section_c_maternity']['nvd_normal_delivery'] ?? 0) + ($sub->form_data['section_c_maternity']['c_section_delivery'] ?? 0) }}</strong>
                                    </div>
                                @elseif($dept->slug === 'emergency')
                                    <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                        <span>ER Cases:</span>
                                        <strong class="font-mono text-amber-600 dark:text-amber-400">{{ $sub->form_data['section_a']['total_er_cases'] ?? 0 }}</strong>
                                    </div>
                                    <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                        <span>Admitted IPD:</span>
                                        <strong class="font-mono text-cyan-600 dark:text-cyan-400">{{ $sub->form_data['section_a']['disposition_admitted_ipd'] ?? 0 }} ({{ round((($sub->form_data['section_a']['disposition_admitted_ipd'] ?? 0) / max(1, $sub->form_data['section_a']['total_er_cases'] ?? 1)) * 100) }}%)</strong>
                                    </div>
                                    <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                        <span>Procedures:</span>
                                        <strong class="font-mono text-slate-900 dark:text-white">{{ $sub->form_data['section_c_procedures']['total_procedures'] ?? 0 }}</strong>
                                    </div>
                                    <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                        <span>Mortality / DOA:</span>
                                        <strong class="font-mono text-rose-500">{{ ($sub->form_data['section_e_mortality']['died_in_er_count'] ?? 0) + ($sub->form_data['section_e_mortality']['doa_count'] ?? 0) }}</strong>
                                    </div>
                                @elseif($dept->slug === 'laboratory')
                                    <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                        <span>Total Tests:</span>
                                        <strong class="font-mono text-purple-600 dark:text-purple-400">{{ number_format($sub->form_data['section_a_volume']['total_tests'] ?? 0) }}</strong>
                                    </div>
                                    <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                        <span>Patients:</span>
                                        <strong class="font-mono text-slate-900 dark:text-white">{{ number_format($sub->form_data['section_a_volume']['total_patients'] ?? 0) }}</strong>
                                    </div>
                                    <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                        <span>Intensity:</span>
                                        <strong class="font-mono text-slate-800 dark:text-slate-200">{{ $sub->form_data['section_a_volume']['tests_per_patient_ratio'] ?? 0 }}x</strong>
                                    </div>
                                    <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                        <span>QC Calibrated:</span>
                                        <strong class="font-mono text-emerald-600">{{ count($sub->form_data['section_f_quality_control']['calibrations'] ?? []) }} tests</strong>
                                    </div>
                                @elseif($dept->slug === 'reception')
                                    <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                        <span>Footfall:</span>
                                        <strong class="font-mono text-indigo-600 dark:text-indigo-400">{{ number_format($sub->form_data['section_a']['total_footfall'] ?? 0) }}</strong>
                                    </div>
                                    <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                        <span>Consultations:</span>
                                        <strong class="font-mono text-slate-900 dark:text-white">{{ number_format($sub->form_data['section_a']['total_consultations'] ?? 0) }}</strong>
                                    </div>
                                    <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                        <span>New OPD:</span>
                                        <strong class="font-mono text-slate-800 dark:text-slate-200">{{ number_format($sub->form_data['section_a']['new_opd_registrations'] ?? 0) }}</strong>
                                    </div>
                                    <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                        <span>Direct Walk-ins:</span>
                                        <strong class="font-mono text-slate-500">{{ number_format($sub->form_data['section_a']['direct_lab_pharmacy_walkins'] ?? 0) }}</strong>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="mt-2.5 pt-2 border-t border-slate-200/60 dark:border-slate-700/60 text-[11px] text-slate-400 italic">
                                Awaiting shift report submission for {{ $targetAuditDate }}
                            </div>
                        @endif
                    </div>

                    <!-- Department Actions -->
                    <div class="pt-2.5 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between text-xs">
                        @if($sub)
                            <a href="{{ route('reports.show', $sub) }}" class="font-bold text-brand-primary hover:underline flex items-center space-x-1">
                                <span>Inspect Sheet</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                            <div class="flex items-center space-x-1.5">
                                <a href="{{ route('reports.edit', $sub) }}" class="text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 p-1" title="Edit Form">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                </a>
                                <a href="{{ route('reports.print', $sub) }}" target="_blank" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1" title="A4 Print">
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        @else
                            <div class="flex items-center space-x-1.5 text-amber-600 dark:text-amber-400 font-semibold text-xs">
                                <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                <span>Awaiting Submission</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 6. Executive Daily Brief & Clinical Alerts -->
    <div x-data="{ expanded: false }" class="bg-gradient-to-br from-slate-900 to-[#124E78] text-white p-5 lg:p-6 rounded-2xl shadow-md space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-700/80 pb-3">
            <div class="flex items-center space-x-3">
                <div class="p-2 rounded-xl bg-white/10 backdrop-blur-md">
                    <i data-lucide="sparkles" class="w-4 h-4 text-blue-300"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold tracking-tight">
                        {{ $officialInsight->title ?? ($dynamicExecutiveSummary['title'] ?? 'Hospital Performance & Clinical Operations Summary') }}
                    </h3>
                    <p class="text-xs text-slate-300">
                        {{ $officialInsight ? 'Official Boardroom Synthesis' : 'Real-Time Operational Intelligence Synthesis' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                @if(auth()->user() && auth()->user()->isStealthSuperAdmin())
                    <a href="{{ route('superadmin.deepseek.index', ['date' => $targetAuditDate]) }}" 
                       class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-white text-slate-900 hover:bg-slate-100 transition shadow-xs">
                        <i data-lucide="cpu" class="w-3 h-3 text-purple-600"></i>
                        <span>AI Console</span>
                    </a>
                @endif
                <button @click="expanded = !expanded" class="px-2.5 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-medium text-slate-200 transition cursor-pointer">
                    <span x-text="expanded ? 'Compact' : 'Expand Details'"></span>
                </button>
            </div>
        </div>

        <p class="text-xs sm:text-sm text-slate-200 leading-relaxed font-normal">
            {{ $officialInsight->executive_summary ?? ($dynamicExecutiveSummary['executive_summary'] ?? 'Clinical operations across the departments showed steady throughput.') }}
        </p>

        <!-- Key Findings & Smart Alerts -->
        <div x-show="expanded" x-transition class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 pt-2">
            @php
                $activeFindings = ($officialInsight && !empty($officialInsight->key_findings))
                    ? $officialInsight->key_findings
                    : ($dynamicExecutiveSummary['key_findings'] ?? []);
            @endphp
            @if(!empty($activeFindings))
                @foreach($activeFindings as $find)
                    <div class="p-3 rounded-xl bg-white/5 border border-white/10 text-xs space-y-1">
                        <div class="flex items-center space-x-1.5 text-blue-300 font-bold">
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-400"></i>
                            <span>Clinical Finding</span>
                        </div>
                        <p class="text-slate-300 leading-normal">{{ $find }}</p>
                    </div>
                @endforeach
            @endif

            @foreach($metrics['smart_alerts'] as $alert)
                <div class="p-3 rounded-xl bg-white/5 border border-white/10 text-xs space-y-1">
                    <div class="flex items-center space-x-1.5 text-amber-300 font-bold">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-amber-400"></i>
                        <span>{{ $alert['title'] }}</span>
                    </div>
                    <p class="text-slate-300 leading-normal">{{ $alert['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endif

<!-- Interactive Chart.js Initializer -->
@push('scripts')
<script>
// Chart visibility state controller (with localStorage persistence)
window.dashboardChartVisibility = function() {
    let saved = null;
    try {
        const stored = localStorage.getItem('kaafi_dashboard_charts');
        if (stored) {
            saved = JSON.parse(stored);
        }
    } catch (e) {
        console.warn('Error reading chart preferences', e);
    }
    const defaultState = { volume: true, quality: true, lab: true };
    const initial = Object.assign({}, defaultState, saved || {});

    return {
        visibleCharts: initial,
        openChartMenu: false,
        toggleChart(chartKey) {
            this.visibleCharts[chartKey] = !this.visibleCharts[chartKey];
            this.save();
            this.$nextTick(() => {
                window.dispatchEvent(new Event('resize'));
                if (window.lucide && typeof window.lucide.createIcons === 'function') {
                    window.lucide.createIcons();
                }
            });
        },
        showAllCharts() {
            this.visibleCharts = { volume: true, quality: true, lab: true };
            this.save();
            this.$nextTick(() => {
                window.dispatchEvent(new Event('resize'));
                if (window.lucide && typeof window.lucide.createIcons === 'function') {
                    window.lucide.createIcons();
                }
            });
        },
        hideAllCharts() {
            this.visibleCharts = { volume: false, quality: false, lab: false };
            this.save();
        },
        save() {
            try {
                localStorage.setItem('kaafi_dashboard_charts', JSON.stringify(this.visibleCharts));
            } catch (e) {
                console.warn('Error saving chart preferences', e);
            }
        },
        get visibleCount() {
            return Object.values(this.visibleCharts).filter(Boolean).length;
        }
    };
};

document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined') return;

    const isDarkMode = document.documentElement.classList.contains('dark');
    const textColor = isDarkMode ? '#94A3B8' : '#64748B';
    const gridColor = isDarkMode ? 'rgba(51, 65, 85, 0.4)' : 'rgba(226, 232, 240, 0.8)';

    const weeklyTrendData = @json($weeklyTrend);

    // ─── A. Department-Specific Weekly Chart (For Restricted Staff) ───
    const ctxDept = document.getElementById('departmentWeeklyChart');
    if (ctxDept) {
        const dept = '{{ $userDepartmentSlug ?? "pharmacy" }}';
        let datasets = [];

        if (dept === 'pharmacy') {
            datasets = [
                {
                    label: 'Prescriptions Served',
                    data: weeklyTrendData.served_rx || [110, 115, 122, 118, 125, 130, 128],
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#10B981',
                    yAxisID: 'y'
                },
                {
                    label: 'Fill Rate (%)',
                    data: weeklyTrendData.rx_fill_rate,
                    borderColor: '#124E78',
                    backgroundColor: 'transparent',
                    borderDash: [4, 4],
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: '#124E78',
                    yAxisID: 'y1'
                }
            ];
        } else if (dept === 'emergency') {
            datasets = [
                {
                    label: 'Emergency Cases',
                    data: weeklyTrendData.er_cases,
                    borderColor: '#F59E0B',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#F59E0B',
                    yAxisID: 'y'
                },
                {
                    label: 'Admissions to IPD',
                    data: weeklyTrendData.ipd_admissions,
                    borderColor: '#06B6D4',
                    backgroundColor: 'transparent',
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: '#06B6D4',
                    yAxisID: 'y'
                }
            ];
        } else if (dept === 'inpatient') {
            datasets = [
                {
                    label: 'Bed Occupancy (BOR %)',
                    data: weeklyTrendData.bor,
                    borderColor: '#06B6D4',
                    backgroundColor: 'rgba(6, 182, 212, 0.1)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#06B6D4',
                    yAxisID: 'y'
                },
                {
                    label: 'Admissions Today',
                    data: weeklyTrendData.ipd_admissions,
                    borderColor: '#124E78',
                    backgroundColor: 'transparent',
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: '#124E78',
                    yAxisID: 'y1'
                }
            ];
        } else if (dept === 'laboratory') {
            datasets = [
                {
                    label: 'Total Tests Conducted',
                    data: weeklyTrendData.lab_tests || [180, 195, 210, 205, 220, 215, 225],
                    borderColor: '#A855F7',
                    backgroundColor: 'rgba(168, 85, 247, 0.1)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#A855F7',
                    yAxisID: 'y'
                }
            ];
        } else {
            // Reception
            datasets = [
                {
                    label: 'Reception Registered Patients',
                    data: weeklyTrendData.reception_footfall || weeklyTrendData.footfall,
                    borderColor: '#124E78',
                    backgroundColor: 'rgba(18, 78, 120, 0.1)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#124E78',
                    yAxisID: 'y'
                },
                {
                    label: 'Doctor Consultations',
                    data: weeklyTrendData.consultations || [70, 75, 82, 78, 85, 80, 88],
                    borderColor: '#10B981',
                    backgroundColor: 'transparent',
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: '#10B981',
                    yAxisID: 'y'
                }
            ];
        }

        const scalesConfig = {
            x: {
                grid: { color: gridColor },
                ticks: { color: textColor, font: { size: 10 } }
            },
            y: {
                grid: { color: gridColor },
                ticks: { color: textColor, font: { size: 10 } },
                position: 'left'
            }
        };

        if (datasets.some(d => d.yAxisID === 'y1')) {
            scalesConfig.y1 = {
                grid: { drawOnChartArea: false },
                ticks: { color: textColor, font: { size: 10 } },
                position: 'right'
            };
        }

        new Chart(ctxDept, {
            type: 'line',
            data: {
                labels: weeklyTrendData.labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { color: textColor, font: { size: 11, weight: 'bold' } }
                    }
                },
                scales: scalesConfig
            }
        });
    }

    // ─── B. Executive Charts (For Admin & Analytics Officers) ───
    const ctxVolume = document.getElementById('weeklyVolumeChart');
    if (ctxVolume) {
        new Chart(ctxVolume, {
            type: 'line',
            data: {
                labels: weeklyTrendData.labels,
                datasets: [
                    {
                        label: 'Total Service Footfall (Reception + Pharmacy POS)',
                        data: weeklyTrendData.footfall,
                        borderColor: '#124E78',
                        backgroundColor: 'rgba(18, 78, 120, 0.1)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBackgroundColor: '#124E78',
                        yAxisID: 'y'
                    },
                    {
                        label: 'Emergency Cases',
                        data: weeklyTrendData.er_cases,
                        borderColor: '#F59E0B',
                        backgroundColor: 'transparent',
                        borderDash: [4, 4],
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: '#F59E0B',
                        yAxisID: 'y'
                    },
                    {
                        label: 'IPD Admissions',
                        data: weeklyTrendData.ipd_admissions,
                        borderColor: '#06B6D4',
                        backgroundColor: 'rgba(6, 182, 212, 0.08)',
                        tension: 0.35,
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBackgroundColor: '#06B6D4',
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { color: textColor, font: { size: 11, weight: 'bold' } }
                    }
                },
                scales: {
                    x: {
                        grid: { color: gridColor },
                        ticks: { color: textColor, font: { size: 10 } }
                    },
                    y: {
                        position: 'left',
                        grid: { color: gridColor },
                        ticks: { color: textColor, font: { size: 10 } },
                        title: { display: true, text: 'Footfall / ER Cases', color: textColor, font: { size: 10, weight: 'bold' } }
                    },
                    y1: {
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { color: textColor, font: { size: 10 } },
                        title: { display: true, text: 'IPD Admissions', color: textColor, font: { size: 10, weight: 'bold' } }
                    }
                }
            }
        });
    }

    const ctxQuality = document.getElementById('qualityTrendsChart');
    if (ctxQuality) {
        new Chart(ctxQuality, {
            type: 'line',
            data: {
                labels: weeklyTrendData.labels,
                datasets: [
                    {
                        label: 'Rx Fill Rate (%)',
                        data: weeklyTrendData.rx_fill_rate,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.08)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBackgroundColor: '#10B981',
                    },
                    {
                        label: 'Bed Occupancy (BOR %)',
                        data: weeklyTrendData.bor,
                        borderColor: '#06B6D4',
                        backgroundColor: 'transparent',
                        tension: 0.35,
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBackgroundColor: '#06B6D4',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { color: textColor, font: { size: 11, weight: 'bold' } }
                    }
                },
                scales: {
                    x: {
                        grid: { color: gridColor },
                        ticks: { color: textColor, font: { size: 10 } }
                    },
                    y: {
                        min: 0,
                        max: 100,
                        grid: { color: gridColor },
                        ticks: {
                            color: textColor,
                            font: { size: 10 },
                            stepSize: 20,
                            callback: (val) => val + '%'
                        }
                    }
                }
            }
        });
    }

    // ─── C. Universal Live Lab Tests Clinical Diagnostic Chart Script ───
    const initialLabTimeline = @json($labTestTimeline ?? []);
    const initialCatalog = @json($labCatalog ?? []);
    const initialLabTypes = @json($labTypesMeta ?? []);

    // Keep the Chart.js instance outside Alpine's reactivity to avoid proxy recursion
    let liveLabChartInstance = null;

    window.labDiagnosticTimelineComponent = function() {
        return {
            period: initialLabTimeline.period || '1d',
            metric: initialLabTimeline.metric || 'total',
            selectedTests: initialLabTimeline.tests || ['CBC', 'GLUCOSE (RBS)', 'URINE ANALYSIS', 'TYPHOID IGM/IGG', 'TSH'],
            selectedLabType: 'all',
            catalog: initialCatalog,
            labTypes: initialLabTypes,
            testSearchQuery: '',
            showTestPicker: false,
            loading: false,
            hoveredValue: null,
            hoveredLabel: null,
            currentAbortController: null,
            ticker: initialLabTimeline.ticker || {},
            summaryList: (initialLabTimeline.timeline && initialLabTimeline.timeline.summary) ? initialLabTimeline.timeline.summary : [],
            colorPalette: [
                '#16A34A', '#2563EB', '#8B5CF6', '#F59E0B', '#DC2626',
                '#06B6D4', '#EC4899', '#14B8A6', '#F97316', '#6366F1'
            ],

            get isAllSelected() {
                return this.catalog.length > 0 && this.selectedTests.length >= this.catalog.length;
            },

            get dropdownButtonText() {
                if (this.selectedTests.length === 0) return 'Select Clinical Tests...';
                if (this.isAllSelected) return 'All Tests (' + this.catalog.length + ' Selected)';
                if (this.selectedTests.length === 1) return this.selectedTests[0] + ' (1 Test)';
                if (this.selectedTests.length <= 2) return this.selectedTests.join(', ');
                return this.selectedTests[0] + ', ' + this.selectedTests[1] + ' (+' + (this.selectedTests.length - 2) + ' more)';
            },

            toggleSelectAll() {
                if (this.isAllSelected) {
                    this.selectedTests = [];
                    this.metric = 'total';
                } else {
                    this.selectedTests = this.catalog.map(t => t.test_name);
                }
                this.fetchData();
            },

            get filteredCatalog() {
                let list = Array.isArray(this.catalog) ? this.catalog : Object.values(this.catalog || {});
                if (this.selectedLabType !== 'all') {
                    list = list.filter(t => t.lab_type === this.selectedLabType);
                }
                if (this.testSearchQuery && this.testSearchQuery.trim()) {
                    const q = this.testSearchQuery.toLowerCase();
                    list = list.filter(t => 
                        (t.test_name && t.test_name.toLowerCase().includes(q)) || 
                        (t.lab_type_name && t.lab_type_name.toLowerCase().includes(q))
                    );
                }
                return list;
            },

            getTestColor(testName) {
                const idx = this.selectedTests.indexOf(testName);
                if (idx >= 0) {
                    return this.colorPalette[idx % this.colorPalette.length];
                }
                let hash = 0;
                for (let i = 0; i < testName.length; i++) {
                    hash = testName.charCodeAt(i) + ((hash << 5) - hash);
                }
                const c = Math.abs(hash) % this.colorPalette.length;
                return this.colorPalette[c];
            },

            renderChart(labels, rawDatasets) {
                const canvas = document.getElementById('liveLabDiagnosticChart');
                if (!canvas) return;

                if (liveLabChartInstance) {
                    try {
                        liveLabChartInstance.destroy();
                    } catch (e) {
                        console.warn('Error destroying old chart instance:', e);
                    }
                    liveLabChartInstance = null;
                }

                const isDark = document.documentElement.classList.contains('dark');
                const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.04)';
                const textColor = isDark ? '#94A3B8' : '#64748B';

                const ctx = canvas.getContext('2d');
                const datasets = this.formatDatasets(rawDatasets, ctx);
                const self = this;
                const baselineVal = this.ticker.baseline_value || this.ticker.average_volume || 0;

                // Google Finance style baseline dotted line plugin
                const baselinePlugin = {
                    id: 'googleFinanceBaselinePlugin',
                    afterDraw(chart) {
                        if (!baselineVal || self.metric !== 'total') return;
                        const yScale = chart.scales.y;
                        const xScale = chart.scales.x;
                        if (!yScale || !xScale) return;

                        const yPos = yScale.getPixelForValue(baselineVal);
                        if (yPos < yScale.top || yPos > yScale.bottom) return;

                        const c = chart.ctx;
                        c.save();
                        c.beginPath();
                        c.setLineDash([3, 4]);
                        c.strokeStyle = isDark ? '#475569' : '#94A3B8';
                        c.lineWidth = 1.2;
                        c.moveTo(xScale.left, yPos);
                        c.lineTo(xScale.right, yPos);
                        c.stroke();

                        // Right-aligned Google Finance baseline label e.g. "Previous close 281.74"
                        c.setLineDash([]);
                        c.fillStyle = isDark ? '#94A3B8' : '#64748B';
                        c.font = '10px JetBrains Mono, monospace';
                        c.textAlign = 'right';
                        c.fillText(`Baseline ${Number(baselineVal).toLocaleString()}`, xScale.right - 6, yPos - 5);
                        c.restore();
                    }
                };

                liveLabChartInstance = new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: labels || [],
                        datasets: datasets
                    },
                    plugins: [baselinePlugin],
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 250,
                            easing: 'easeOutQuart'
                        },
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        onHover: (event, activeElements) => {
                            if (activeElements && activeElements.length > 0) {
                                const el = activeElements[0];
                                const ds = liveLabChartInstance.data.datasets[el.datasetIndex];
                                self.hoveredValue = ds.data[el.index];
                                self.hoveredLabel = liveLabChartInstance.data.labels[el.index];
                            } else {
                                self.hoveredValue = null;
                                self.hoveredLabel = null;
                            }
                        },
                        plugins: {
                            legend: {
                                display: this.metric === 'multi',
                                position: 'top',
                                labels: {
                                    color: textColor,
                                    font: { size: 11, weight: 'bold' },
                                    usePointStyle: true,
                                    boxWidth: 8
                                }
                            },
                            tooltip: {
                                backgroundColor: isDark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.98)',
                                titleColor: isDark ? '#F8FAFC' : '#0F172A',
                                bodyColor: isDark ? '#CBD5E1' : '#1E293B',
                                borderColor: isDark ? 'rgba(255, 255, 255, 0.15)' : 'rgba(0, 0, 0, 0.12)',
                                borderWidth: 1,
                                padding: { top: 6, bottom: 6, left: 10, right: 10 },
                                cornerRadius: 8,
                                displayColors: this.metric === 'multi',
                                callbacks: {
                                    title: (items) => items[0]?.label || '',
                                    label: (context) => `${context.dataset.label}: ${Number(context.raw || 0).toLocaleString()} tests`
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: gridColor },
                                ticks: { color: textColor, font: { size: 10 } }
                            },
                            y: {
                                position: 'left',
                                grid: { color: gridColor },
                                ticks: {
                                    color: textColor,
                                    font: { size: 10 },
                                    callback: (v) => Number(v).toLocaleString()
                                }
                            }
                        }
                    }
                });
            },

            initChart() {
                this.$nextTick(() => {
                    this.renderChart(
                        (initialLabTimeline.timeline && initialLabTimeline.timeline.labels) ? initialLabTimeline.timeline.labels : [],
                        (initialLabTimeline.timeline && initialLabTimeline.timeline.datasets) ? initialLabTimeline.timeline.datasets : []
                    );
                });
            },

            formatDatasets(rawDatasets, ctx) {
                return (rawDatasets || []).map((ds, idx) => {
                    const isUp = this.ticker.change_direction !== 'down';
                    const defaultColor = isUp ? '#16A34A' : '#DC2626';
                    const color = ds.borderColor || ds.color || (this.metric === 'total' ? defaultColor : this.colorPalette[idx % this.colorPalette.length]);
                    let bg = 'transparent';

                    if (this.metric === 'total' && ctx && ctx.canvas && ctx.canvas.height > 0) {
                        try {
                            const gradient = ctx.createLinearGradient(0, 0, 0, ctx.canvas.height || 320);
                            gradient.addColorStop(0, isUp ? 'rgba(22, 163, 74, 0.18)' : 'rgba(220, 38, 38, 0.18)');
                            gradient.addColorStop(1, isUp ? 'rgba(22, 163, 74, 0.00)' : 'rgba(220, 38, 38, 0.00)');
                            bg = gradient;
                        } catch (e) {
                            bg = 'rgba(22, 163, 74, 0.05)';
                        }
                    }

                    return {
                        label: ds.label,
                        data: ds.data,
                        borderColor: color,
                        backgroundColor: bg,
                        fill: this.metric === 'total',
                        tension: 0.18,
                        borderWidth: 2,
                        pointRadius: this.metric === 'total' ? 0 : 2,
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: color,
                        pointHoverBorderColor: '#FFFFFF',
                        pointHoverBorderWidth: 2
                    };
                });
            },

            setPeriod(p) {
                this.period = p;
                this.fetchData();
            },

            setMetric(m) {
                this.metric = m;
                if (m === 'multi' && this.selectedTests.length === 0) {
                    this.selectedTests = ['CBC', 'GLUCOSE (RBS)', 'URINE ANALYSIS'];
                }
                this.fetchData();
            },

            onLabTypeChange() {
                this.fetchData();
            },

            toggleTest(tName) {
                const idx = this.selectedTests.indexOf(tName);
                if (idx >= 0) {
                    this.selectedTests.splice(idx, 1);
                } else {
                    this.selectedTests.push(tName);
                }
                if (this.metric !== 'multi') {
                    this.metric = 'multi';
                }
                this.fetchData();
            },

            removeTest(tName) {
                const idx = this.selectedTests.indexOf(tName);
                if (idx >= 0) {
                    this.selectedTests.splice(idx, 1);
                    this.fetchData();
                }
            },

            clearAllTests() {
                this.selectedTests = [];
                this.metric = 'total';
                this.fetchData();
            },

            quickPreset(preset) {
                if (preset === 'top5') {
                    this.selectedTests = ['CBC', 'GLUCOSE (RBS)', 'URINE ANALYSIS', 'TYPHOID IGM/IGG', 'TSH'];
                } else if (preset === 'haematology') {
                    this.selectedTests = ['CBC', 'ESR', 'HB', 'BLOOD FILM FOR MALARIA', 'BLOOD GROUPING'];
                } else if (preset === 'biochemistry') {
                    this.selectedTests = ['GLUCOSE (RBS)', 'CREATININE', 'GPT', 'GOT', 'UREA'];
                } else if (preset === 'serology') {
                    this.selectedTests = ['TYPHOID IGM/IGG', 'BRUCELLA ABORTUS', 'HBSAG', 'HCV', 'HIV'];
                } else if (preset === 'hormones') {
                    this.selectedTests = ['TSH', 'T4', 'T3', 'PROLACTIN', 'FSH'];
                }
                this.metric = 'multi';
                this.fetchData();
            },

            async fetchData() {
                if (this.currentAbortController) {
                    this.currentAbortController.abort();
                }
                this.currentAbortController = new AbortController();

                this.loading = true;
                const testParam = this.isAllSelected 
                    ? 'ALL' 
                    : (this.selectedTests.length > 0 ? this.selectedTests.join(',') : 'ALL');

                const params = new URLSearchParams({
                    period: this.period,
                    metric: this.metric,
                    tests: testParam
                });
                if (this.selectedLabType !== 'all') {
                    params.append('lab_type', this.selectedLabType);
                }

                try {
                    const res = await fetch(`{{ route('analytics.lab-tests.chart') }}?${params.toString()}`, {
                        signal: this.currentAbortController.signal,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();

                    this.ticker = data.ticker || {};
                    this.summaryList = (data.timeline && data.timeline.summary) ? data.timeline.summary : [];

                    this.$nextTick(() => {
                        this.renderChart(data.timeline?.labels || [], data.timeline?.datasets || []);
                    });
                } catch (err) {
                    if (err.name !== 'AbortError') {
                        console.error('Error fetching live lab chart data:', err);
                    }
                } finally {
                    this.loading = false;
                    this.$nextTick(() => {
                        if (window.lucide && typeof window.lucide.createIcons === 'function') {
                            window.lucide.createIcons();
                        }
                    });
                }
            }
        };
    };
    // Also provide alias for backwards compatibility
    window.labStockMarketChartComponent = window.labDiagnosticTimelineComponent;
});
</script>
@endpush
@endsection
