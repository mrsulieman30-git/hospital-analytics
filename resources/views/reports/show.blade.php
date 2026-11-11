@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- 1. Header & Audit Actions Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-mono font-bold px-2.5 py-0.5 rounded-full border {{ $report->status_badge_class }}">
                    {{ ucfirst($report->status) }}
                </span>
                <span class="text-xs font-mono font-bold text-slate-500 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded">
                    {{ $report->form_code }}
                </span>
                <span class="text-xs text-slate-400 font-medium">Shift: <strong class="text-slate-700 dark:text-slate-300 capitalize">{{ $report->shift }}</strong></span>
            </div>

            <h1 class="text-xl lg:text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-2 flex items-center gap-2">
                <span>{{ ucfirst($report->department) }} Daily Clinical Report</span>
                <span class="text-sm font-mono font-normal text-slate-400">({{ $report->report_date->toDateString() }})</span>
            </h1>

            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Prepared by: <strong class="text-slate-800 dark:text-slate-200">{{ $report->lead_staff_name }}</strong>
                @if($report->submitted_at) &bull; Submitted: <span class="font-mono text-slate-700 dark:text-slate-300">{{ $report->submitted_at->format('M d, Y H:i') }}</span> @endif
                @if($report->audited_at) &bull; Audited: <span class="text-emerald-600 font-semibold">{{ $report->audited_at->format('M d, Y H:i') }}</span> by {{ $report->auditor_name }} @endif
            </p>
        </div>

        <!-- Top Action Buttons -->
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <a href="{{ route('reports.download', $report) }}"
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-indigo-600 text-white hover:bg-slate-900 transition shadow-xs"
               title="Direct Fast Download PDF (named by date, department, and user)">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>Download PDF Report</span>
            </a>

            <a href="{{ route('reports.print', $report) }}" target="_blank"
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 hover:bg-slate-900 hover:text-white dark:hover:bg-white dark:hover:text-slate-900 transition">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>A4 Official Print</span>
            </a>

            @if($report->status !== 'approved' || auth()->user()->isAnalyticsOfficer() || auth()->user()->isAdmin())
                <a href="{{ route('reports.edit', $report) }}"
                   class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 hover:bg-slate-900 hover:text-white dark:hover:bg-white dark:hover:text-slate-900 transition">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                    <span>Edit Data</span>
                </a>
            @endif

            <!-- Governance Audit Actions (Analytics Officer & Admin) -->
            @if((auth()->user()->isAnalyticsOfficer() || auth()->user()->isAdmin()) && $report->status !== 'approved')
                <form action="{{ route('reports.approve', $report) }}" method="POST" class="inline"
                      onsubmit="return confirm('Confirm clinical verification and approval of this official report?');">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 text-white border border-emerald-600 hover:bg-slate-900 hover:text-white transition shadow-xs">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        <span>Audit & Approve</span>
                    </button>
                </form>

                <div x-data="{ openReturn: false }" class="inline">
                    <button type="button" @click="openReturn = true"
                            class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-rose-50 text-rose-600 border border-rose-200 hover:bg-rose-600 hover:text-white transition">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        <span>Return for Revision</span>
                    </button>

                    <!-- Return Modal -->
                    <div x-show="openReturn" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50" x-transition>
                        <div @click.away="openReturn = false" class="bg-white dark:bg-slate-900 rounded-2xl p-6 max-w-md w-full shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Return Report for Revision</h3>
                            <p class="text-xs text-slate-500">Provide clear instructions and corrections for the department supervisor.</p>
                            <form action="{{ route('reports.return', $report) }}" method="POST" class="space-y-3">
                                @csrf
                                <textarea name="audit_feedback" rows="3" required placeholder="Describe discrepancy or needed changes..."
                                          class="w-full text-xs p-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-hidden"></textarea>
                                <div class="flex justify-end space-x-2">
                                    <button type="button" @click="openReturn = false" class="px-3 py-1.5 rounded-lg text-xs font-bold text-slate-500">Cancel</button>
                                    <button type="submit" class="px-4 py-1.5 rounded-lg text-xs font-bold bg-rose-600 text-white">Return to Supervisor</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Audit Feedback Banner (If previously returned or audited with notes) -->
    @if($report->audit_feedback)
        <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 text-xs text-amber-900 dark:text-amber-200 flex items-start space-x-3">
            <i data-lucide="info" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
            <div>
                <strong class="font-bold">Directorate Audit Remarks:</strong>
                <p class="mt-0.5">{{ $report->audit_feedback }}</p>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════════════
         CLEAN CLINICAL REPORT SECTIONS (Organized & readable, NO CODE/JSON DUMPS!)
         ═══════════════════════════════════════════════════════════════════════════════ --}}

    @if($report->department === 'pharmacy')
        @php
            $secA = $report->form_data['section_a'] ?? [];
            $secB = $report->form_data['section_b_doctors'] ?? [];
            $secC = $report->form_data['section_c_root_causes'] ?? [];
            $secD = $report->form_data['section_d_financials'] ?? [];
            $secE = $report->form_data['section_e_inventory'] ?? [];
        @endphp

        <!-- Section A: Prescription Volumes & Dispensing -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        Section A — Prescription Volume &amp; Dispensing Throughput
                    </h2>
                </div>
                <span class="text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400">
                    Fill Rate: {{ $report->summary_metrics['fill_rate_percent'] ?? round(($secA['total_served_rx'] ?? 0) / max(1, ($secA['total_served_rx'] ?? 0) + ($secA['total_unserved_patients'] ?? 0)) * 100, 1) }}%
                </span>
            </div>

            <!-- Metric Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60">
                    <span class="text-[11px] font-bold text-slate-400 uppercase">Inside Rx</span>
                    <div class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($secA['inside_rx_count'] ?? 0) }}</div>
                    <span class="text-[10px] text-slate-400">{{ $secA['inside_items_dispensed'] ?? 0 }} items</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60">
                    <span class="text-[11px] font-bold text-slate-400 uppercase">Outside Rx</span>
                    <div class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($secA['outside_rx_count'] ?? 0) }}</div>
                    <span class="text-[10px] text-slate-400">{{ $secA['outside_items_dispensed'] ?? 0 }} items</span>
                </div>
                <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/60 dark:border-emerald-800/40">
                    <span class="text-[11px] font-bold text-emerald-700 dark:text-emerald-300 uppercase">Total Served</span>
                    <div class="text-xl font-black text-emerald-700 dark:text-emerald-300 mt-1">{{ number_format($secA['total_served_rx'] ?? 0) }}</div>
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-mono">{{ $secA['total_items_dispensed'] ?? 0 }} items</span>
                </div>
                <div class="p-3 rounded-xl bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-200/60 dark:border-indigo-800/40">
                    <span class="text-[11px] font-bold text-indigo-700 dark:text-indigo-300 uppercase">Gross Dispensed Value</span>
                    <div class="text-xl font-black text-indigo-700 dark:text-indigo-300 mt-1 font-mono">${{ number_format($secA['total_dispensed_value'] ?? 0, 2) }}</div>
                    <span class="text-[10px] text-indigo-600 dark:text-indigo-400">Total revenue</span>
                </div>
            </div>

            @if(!empty($secA['inside_notes']))
                <div class="text-xs text-slate-500 bg-slate-50 dark:bg-slate-800/40 p-2.5 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                    <strong>Dispensing Notes:</strong> {{ $secA['inside_notes'] }}
                </div>
            @endif
        </div>

        <!-- Section B: Physician Prescription Capture Queue -->
        @if(!empty($secB))
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="stethoscope" class="w-4 h-4 text-brand-primary"></i>
                        <span>Section B — Physician Prescription Capture &amp; Conversion Registry</span>
                    </h3>
                    <span class="text-xs font-mono font-bold text-slate-400">{{ count($secB) }} Doctors Logged</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-mono text-[10px] text-slate-500">
                            <tr>
                                <th class="p-2.5">Prescribing Physician</th>
                                <th class="p-2.5 text-center">Rx Written</th>
                                <th class="p-2.5 text-center">Rx Dispensed (Captured)</th>
                                <th class="p-2.5 text-center">Unserved / Dropped</th>
                                <th class="p-2.5 text-right">Capture Conversion</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                            @foreach($secB as $doc)
                                @php
                                    $written = (int) ($doc['written'] ?? ($doc['prescriptions_written'] ?? 0));
                                    $dispensed = (int) ($doc['dispensed'] ?? ($doc['prescriptions_dispensed'] ?? 0));
                                    $unserved = (int) ($doc['unserved'] ?? max(0, $written - $dispensed));
                                    $rate = $written > 0 ? round(($dispensed / $written) * 100) : 100;
                                @endphp
                                <tr>
                                    <td class="p-2.5 font-bold text-slate-900 dark:text-white">{{ $doc['doctor'] ?? ($doc['name'] ?? '—') }}</td>
                                    <td class="p-2.5 text-center font-mono">{{ $written }}</td>
                                    <td class="p-2.5 text-center font-mono font-bold text-emerald-600">{{ $dispensed }}</td>
                                    <td class="p-2.5 text-center font-mono text-rose-500">{{ $unserved }}</td>
                                    <td class="p-2.5 text-right font-mono font-bold" :class="{{ $rate >= 85 ? "'text-emerald-600'" : "'text-amber-600'" }}">
                                        {{ $rate }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Section B & C: Unserved Patients & Doctor Rx Capture -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Root Causes for Unserved -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        Unserved Patients Breakdown
                    </h3>
                    <span class="text-xs font-mono font-bold text-rose-500">{{ $secA['total_unserved_patients'] ?? ($secC['total_unserved'] ?? 0) }} Unserved</span>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between p-2 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200/60 dark:border-rose-800/40">
                        <span class="font-bold text-rose-700 dark:text-rose-300">Drug Out of Stock (Stockout)</span>
                        <strong class="font-mono text-rose-700 dark:text-rose-300">{{ $secC['stockout'] ?? 0 }} pts</strong>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                        <span>Price Constraints (Qiimaha)</span>
                        <strong class="font-mono text-slate-800 dark:text-slate-200">{{ $secC['price'] ?? 0 }} pts</strong>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                        <span>Brand Preference Unavailable</span>
                        <strong class="font-mono text-slate-800 dark:text-slate-200">{{ $secC['brand_unavailable'] ?? 0 }} pts</strong>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                        <span>Did Not Present (Pharmacy lama iman)</span>
                        <strong class="font-mono text-slate-800 dark:text-slate-200">{{ $secC['did_not_present'] ?? 0 }} pts</strong>
                    </div>
                    @if(!empty($secC['other_details']))
                        <p class="text-[11px] text-slate-500 italic mt-1">Detail: {{ $secC['other_details'] }}</p>
                    @endif
                </div>
            </div>

            <!-- Financials & Credit Ledger -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        Financials &amp; Credit Ledger
                    </h3>
                    <span class="text-xs font-mono font-bold text-indigo-600">Reconciled</span>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                        <span>Debt Taken / Credit Extended ({{ $secD['debt_taken_cases'] ?? 0 }} cases)</span>
                        <strong class="font-mono text-slate-900 dark:text-white">${{ number_format($secD['debt_taken_amount'] ?? 0, 2) }}</strong>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                        <span>Debt Recovered / Paid ({{ $secD['debt_paid_cases'] ?? 0 }} cases)</span>
                        <strong class="font-mono text-emerald-600">${{ number_format($secD['debt_paid_amount'] ?? 0, 2) }}</strong>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                        <span>Refunds &amp; Returned Meds</span>
                        <strong class="font-mono text-amber-600">${{ number_format(($secD['refund_billing_error_amount'] ?? 0) + ($secD['refund_returned_meds_amount'] ?? 0), 2) }}</strong>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-cyan-50 dark:bg-cyan-950/40 border border-cyan-200/60 dark:border-cyan-800/40">
                        <strong class="text-cyan-800 dark:text-cyan-200">Net Credit Exposure</strong>
                        <strong class="font-mono text-cyan-800 dark:text-cyan-200">${{ number_format($secD['net_credit_exposure'] ?? 0, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Alerts: Stockout & Near Expiry -->
        @if(!empty($secE['out_of_stock_drugs']) || !empty($secE['near_expiry_drugs']))
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs space-y-3">
                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                    Formulary Inventory &amp; Stock Alerts
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    @if(!empty($secE['out_of_stock_drugs']))
                        <div>
                            <span class="font-semibold text-rose-600 flex items-center gap-1.5 mb-2">
                                <i data-lucide="alert-octagon" class="w-3.5 h-3.5"></i>
                                Out of Stock Medications (Zero Nil):
                            </span>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($secE['out_of_stock_drugs'] as $drug)
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                        {{ $drug }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(!empty($secE['near_expiry_drugs']))
                        <div>
                            <span class="font-semibold text-amber-600 flex items-center gap-1.5 mb-2">
                                <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                Near Expiry Medications (Action Required):
                            </span>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($secE['near_expiry_drugs'] as $drug)
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        {{ $drug }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Section C: Department Operational & Logistical Needs -->
        @if(!empty($secNeeds))
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-3">
                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="clipboard-check" class="w-4 h-4 text-indigo-500"></i>
                    <span>Section C — Front Desk Operational Observations &amp; Support Requests</span>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                    @if(!empty($secNeeds['shift_notes']))
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-700/60">
                            <span class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Shift Handover Remarks:</span>
                            <p class="text-slate-600 dark:text-slate-400 leading-relaxed">{{ $secNeeds['shift_notes'] }}</p>
                        </div>
                    @endif
                    @if(!empty($secNeeds['equipment_needs']))
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-700/60">
                            <span class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Equipment &amp; Stationery Needs:</span>
                            <p class="text-slate-600 dark:text-slate-400 leading-relaxed">{{ $secNeeds['equipment_needs'] }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

    @elseif($report->department === 'inpatient')
        @php
            $secCensus = $report->form_data['section_a_census'] ?? [];
            $secFlow = $report->form_data['section_b_flow'] ?? [];
            $secMat = $report->form_data['section_c_maternity'] ?? [];
            $secDeaths = $report->form_data['section_d_deaths'] ?? [];
        @endphp

        <!-- Bed Census Reconciliation Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        Section A — Self-Balancing Inpatient Bed Census Formula
                    </h2>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-mono font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                    Math Balance Verified
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 font-bold uppercase text-[11px]">
                        <tr>
                            <th class="p-3">Step</th>
                            <th class="p-3">Census Metric Parameter</th>
                            <th class="p-3 text-center">Patient Count</th>
                            <th class="p-3">Clinical Source &amp; Cross-Check</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                        <tr>
                            <td class="p-3 font-mono font-bold text-slate-400">[A]</td>
                            <td class="p-3 font-bold text-slate-900 dark:text-white">Previous Admissions (Midnight Starting Census)</td>
                            <td class="p-3 text-center font-mono font-bold text-base text-slate-900 dark:text-white">{{ $secCensus['previous_admissions_a'] ?? 0 }}</td>
                            <td class="p-3 text-slate-500">Verified from yesterday's closing bed count</td>
                        </tr>
                        <tr class="bg-emerald-50/40 dark:bg-emerald-950/20">
                            <td class="p-3 font-mono font-bold text-emerald-600">[+B]</td>
                            <td class="p-3 font-bold text-emerald-900 dark:text-emerald-300">Total Newly Admitted Today</td>
                            <td class="p-3 text-center font-mono font-bold text-base text-emerald-600">{{ $secCensus['newly_admitted_b'] ?? 0 }}</td>
                            <td class="p-3 text-slate-500">Source: ER Admitted ({{ $secCensus['newly_admitted_from_er'] ?? 0 }}) + OPD Referrals ({{ $secCensus['newly_admitted_from_opd'] ?? 0 }})</td>
                        </tr>
                        <tr class="bg-amber-50/40 dark:bg-amber-950/20">
                            <td class="p-3 font-mono font-bold text-amber-600">[-C]</td>
                            <td class="p-3 font-bold text-amber-900 dark:text-amber-300">Total Discharges Today</td>
                            <td class="p-3 text-center font-mono font-bold text-base text-amber-600">{{ $secCensus['total_discharged_c'] ?? 0 }}</td>
                            <td class="p-3 text-slate-500">Routine Recovered: {{ $secCensus['discharged_recovered_c1'] ?? 0 }} &bull; DAMA: {{ $secCensus['discharged_dama_c2'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td class="p-3 font-mono font-bold text-slate-400">[-D]</td>
                            <td class="p-3 text-slate-700 dark:text-slate-300">Referred Out to Other Hospital</td>
                            <td class="p-3 text-center font-mono text-slate-700 dark:text-slate-300">{{ $secCensus['referred_out_d'] ?? 0 }}</td>
                            <td class="p-3 text-slate-500">Banadir Hospital or specialist referral</td>
                        </tr>
                        <tr>
                            <td class="p-3 font-mono font-bold text-rose-500">[-E]</td>
                            <td class="p-3 text-slate-700 dark:text-slate-300">Inpatient Deaths</td>
                            <td class="p-3 text-center font-mono text-rose-600 font-bold">{{ $secCensus['deaths_today_e'] ?? 0 }}</td>
                            <td class="p-3 text-slate-500">Death certificate logged</td>
                        </tr>
                        <tr class="bg-cyan-50 dark:bg-cyan-950/40 font-bold border-t-2 border-cyan-500">
                            <td class="p-3 font-mono text-cyan-800 dark:text-cyan-200">[=F]</td>
                            <td class="p-3 text-cyan-900 dark:text-cyan-100 uppercase tracking-wider">Current Admissions (Closing Bed Census)</td>
                            <td class="p-3 text-center font-mono text-xl text-cyan-800 dark:text-cyan-200">{{ $secCensus['current_admissions_f'] ?? 0 }}</td>
                            <td class="p-3 text-cyan-700 dark:text-cyan-300">BOR: {{ round((($secCensus['current_admissions_f'] ?? 0) / 60) * 100, 1) }}% on 60 Active Beds</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section C: Maternity & Delivery Registry -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                    Section C — Maternity &amp; Obstetric Delivery Register
                </h3>
                <span class="text-xs font-mono font-bold text-purple-600">
                    Total Deliveries: {{ ($secMat['nvd_normal_delivery'] ?? 0) + ($secMat['c_section_delivery'] ?? 0) }}
                </span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center text-xs">
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60">
                    <span class="text-slate-400 font-bold">Normal Delivery (NVD)</span>
                    <div class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ $secMat['nvd_normal_delivery'] ?? 0 }}</div>
                </div>
                <div class="p-3 rounded-xl bg-purple-50 dark:bg-purple-950/40 border border-purple-200/60 dark:border-purple-800/40">
                    <span class="text-purple-700 dark:text-purple-300 font-bold">C-Section Delivery</span>
                    <div class="text-xl font-black text-purple-700 dark:text-purple-300 mt-1">{{ $secMat['c_section_delivery'] ?? 0 }}</div>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60">
                    <span class="text-slate-400 font-bold">Live Births</span>
                    <div class="text-xl font-black text-slate-900 dark:text-white mt-1">
                        {{ ($secMat['live_births_male'] ?? 0) + ($secMat['live_births_female'] ?? 0) }}
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono">{{ $secMat['live_births_male'] ?? 0 }}M / {{ $secMat['live_births_female'] ?? 0 }}F</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60">
                    <span class="text-slate-400 font-bold">NICU Admissions</span>
                    <div class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ $secMat['neonatal_icu_admissions'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        <!-- Section B: Inpatient Admission Flow & Ward Distribution -->
        @if(!empty($secFlow))
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-3">
                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="git-merge" class="w-4 h-4 text-cyan-500"></i>
                    <span>Section B — Admission Intake Flow &amp; Ward Capacity Allocation</span>
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 flex justify-between">
                        <span>Admitted via ER</span>
                        <strong class="font-mono text-cyan-600">{{ $secFlow['admitted_from_er'] ?? ($secCensus['newly_admitted_from_er'] ?? 0) }}</strong>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 flex justify-between">
                        <span>Referred from OPD</span>
                        <strong class="font-mono text-cyan-600">{{ $secFlow['admitted_from_opd'] ?? ($secCensus['newly_admitted_from_opd'] ?? 0) }}</strong>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 flex justify-between">
                        <span>Surgical Ward Beds</span>
                        <strong class="font-mono text-slate-900 dark:text-white">{{ $secFlow['surgical_ward_occupied'] ?? 0 }} pts</strong>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 flex justify-between">
                        <span>Medical Ward Beds</span>
                        <strong class="font-mono text-slate-900 dark:text-white">{{ $secFlow['medical_ward_occupied'] ?? 0 }} pts</strong>
                    </div>
                </div>
            </div>
        @endif

        <!-- Section D: Inpatient Mortality & Discharge Registry -->
        @if(!empty($secDeaths))
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="skull" class="w-4 h-4 text-rose-500"></i>
                        <span>Section D — Inpatient Mortality &amp; Morbidity Register</span>
                    </h3>
                    <span class="text-xs font-mono font-bold text-rose-600">
                        {{ $secDeaths['total_deaths'] ?? ($secCensus['deaths_today_e'] ?? 0) }} Inpatient Deaths
                    </span>
                </div>
                @if(!empty($secDeaths['cases']))
                    <div class="space-y-2 text-xs">
                        @foreach($secDeaths['cases'] as $case)
                            <div class="p-2.5 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 flex justify-between items-center">
                                <div>
                                    <strong class="text-rose-900 dark:text-rose-200">{{ $case['patient_name'] ?? 'Inpatient' }}</strong>
                                    <span class="text-slate-500 ml-2">Ward: {{ $case['ward'] ?? 'General IPD' }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold text-rose-700 dark:text-rose-300">{{ $case['cause_of_death'] ?? 'Clinical Complication' }}</span>
                                    <span class="text-slate-400 font-mono ml-1">({{ $case['time_of_death'] ?? 'Logged' }})</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

    @elseif($report->department === 'emergency')
        @php
            $secA = $report->form_data['section_a'] ?? [];
            $secDem = $report->form_data['section_b_demographics'] ?? [];
            $secProc = $report->form_data['section_c_procedures'] ?? [];
            $secCrit = $report->form_data['section_d_critical_cases'] ?? [];
            $secMort = $report->form_data['section_e_mortality'] ?? [];
        @endphp

        <!-- Section A: Emergency Cases & Dispositions -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        Section A — Emergency Intake &amp; Inpatient Admission Yield
                    </h2>
                </div>
                <span class="text-xs font-mono font-bold text-amber-600">
                    Yield: {{ round((($secA['disposition_admitted_ipd'] ?? 0) / max(1, $secA['total_er_cases'] ?? 1)) * 100, 1) }}%
                </span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/60 dark:border-amber-800/40">
                    <span class="text-[11px] font-bold text-amber-700 dark:text-amber-300 uppercase">Total ER Cases</span>
                    <div class="text-2xl font-black text-amber-700 dark:text-amber-300 mt-1">{{ $secA['total_er_cases'] ?? 0 }}</div>
                    <span class="text-[10px] text-amber-600">{{ $secA['emergency_cases_acute'] ?? 0 }} Acute Cases</span>
                </div>
                <div class="p-3 rounded-xl bg-cyan-50 dark:bg-cyan-950/40 border border-cyan-200/60 dark:border-cyan-800/40">
                    <span class="text-[11px] font-bold text-cyan-700 dark:text-cyan-300 uppercase">Admitted to IPD</span>
                    <div class="text-2xl font-black text-cyan-700 dark:text-cyan-300 mt-1">{{ $secA['disposition_admitted_ipd'] ?? 0 }}</div>
                    <span class="text-[10px] text-cyan-600">Transferred to Wards</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60">
                    <span class="text-[11px] font-bold text-slate-400 uppercase">Discharged Stable</span>
                    <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $secA['disposition_discharged_stable'] ?? 0 }}</div>
                    <span class="text-[10px] text-slate-400">Home Discharge</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60">
                    <span class="text-[11px] font-bold text-slate-400 uppercase">Obs Ward</span>
                    <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $secA['disposition_obs_ward'] ?? 0 }}</div>
                    <span class="text-[10px] text-slate-400">Monitored in Bay</span>
                </div>
            </div>
        </div>

        <!-- Section B: Patient Demographics & Triage Classifications -->
        @if(!empty($secDem))
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-3">
                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="users" class="w-4 h-4 text-amber-500"></i>
                    <span>Section B — Patient Demographics &amp; Triage Distribution</span>
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 flex justify-between">
                        <span>Adult Patients (&ge;15y)</span>
                        <strong class="font-mono text-slate-900 dark:text-white">{{ $secDem['adult_count'] ?? 0 }}</strong>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 flex justify-between">
                        <span>Pediatric Patients (&lt;15y)</span>
                        <strong class="font-mono text-slate-900 dark:text-white">{{ $secDem['pediatric_count'] ?? 0 }}</strong>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 flex justify-between">
                        <span>Trauma / Injury Cases</span>
                        <strong class="font-mono text-amber-600">{{ $secDem['trauma_cases'] ?? 0 }}</strong>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 flex justify-between">
                        <span>Medical Non-Trauma</span>
                        <strong class="font-mono text-slate-900 dark:text-white">{{ $secDem['medical_cases'] ?? 0 }}</strong>
                    </div>
                </div>
            </div>
        @endif

        <!-- Section C: Clinical Procedures -->
        @if(!empty($secProc))
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-3">
                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                    Emergency Clinical Procedures Log
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                    <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 flex justify-between">
                        <span>Minor Surgery &amp; Suturing</span>
                        <strong class="font-mono text-slate-900 dark:text-white">{{ $secProc['minor_surgery_suturing'] ?? 0 }}</strong>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 flex justify-between">
                        <span>Wound Dressing</span>
                        <strong class="font-mono text-slate-900 dark:text-white">{{ $secProc['wound_dressing'] ?? 0 }}</strong>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 flex justify-between">
                        <span>IV Fluids &amp; Injections</span>
                        <strong class="font-mono text-slate-900 dark:text-white">{{ $secProc['iv_fluids_injections'] ?? 0 }}</strong>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 flex justify-between">
                        <span>Nebulization / O2</span>
                        <strong class="font-mono text-slate-900 dark:text-white">{{ $secProc['nebulization_o2'] ?? 0 }}</strong>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 flex justify-between">
                        <span>Orthopedic / Cast</span>
                        <strong class="font-mono text-slate-900 dark:text-white">{{ $secProc['orthopedic_cast'] ?? 0 }}</strong>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 flex justify-between">
                        <span>ECG / Resuscitation</span>
                        <strong class="font-mono text-slate-900 dark:text-white">{{ $secProc['ecg_resuscitation'] ?? 0 }}</strong>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 flex justify-between">
                        <span>Circumcision</span>
                        <strong class="font-mono text-slate-900 dark:text-white">{{ $secProc['circumcision'] ?? 0 }}</strong>
                    </div>
                    <div class="p-2.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 flex justify-between font-bold text-indigo-700 dark:text-indigo-300">
                        <span>Total Procedures</span>
                        <strong class="font-mono">{{ $secProc['total_procedures'] ?? 0 }}</strong>
                    </div>
                </div>
            </div>
        @endif

        <!-- Section D: Critical Resuscitation Cases Log -->
        @if(!empty($secCrit))
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="activity" class="w-4 h-4 text-rose-500"></i>
                        <span>Section D — Critical Resuscitation &amp; STAT Interventions Log</span>
                    </h3>
                    <span class="text-xs font-mono font-bold text-rose-600">
                        {{ $secCrit['resuscitations_total'] ?? 0 }} Resuscitations
                    </span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 flex justify-between">
                        <span>Cardiac Arrest / CPR</span>
                        <strong class="font-mono text-rose-600">{{ $secCrit['cardiac_arrest_cpr'] ?? 0 }}</strong>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 flex justify-between">
                        <span>Septic / Hypovolemic Shock</span>
                        <strong class="font-mono text-amber-600">{{ $secCrit['shock_resuscitation'] ?? 0 }}</strong>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 flex justify-between">
                        <span>Polytrauma / Hemorrhage</span>
                        <strong class="font-mono text-slate-900 dark:text-white">{{ $secCrit['polytrauma_cases'] ?? 0 }}</strong>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 flex justify-between">
                        <span>Severe Respiratory Failure</span>
                        <strong class="font-mono text-slate-900 dark:text-white">{{ $secCrit['respiratory_distress'] ?? 0 }}</strong>
                    </div>
                </div>
            </div>
        @endif

        <!-- Section E: Critical Mortality & DOA -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs space-y-3">
            <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                Mortality Registry &amp; Brought Dead (DOA)
            </h3>
            <div class="flex items-center space-x-6 text-xs">
                <div>Died in Emergency (<24h): <strong class="text-rose-600 font-mono">{{ $secMort['died_in_er_count'] ?? 0 }} cases</strong></div>
                <div>Brought Dead (DOA): <strong class="text-slate-500 font-mono">{{ $secMort['doa_count'] ?? 0 }} cases</strong></div>
            </div>
            @if(!empty($secMort['cases']))
                <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800 text-xs">
                    @foreach($secMort['cases'] as $c)
                        <div class="p-2 rounded bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 flex justify-between items-center">
                            <span><strong>{{ $c['cause_of_death'] ?? 'Cardiac Arrest' }}</strong> ({{ $c['age'] ?? '—' }}y, {{ $c['sex'] ?? '—' }})</span>
                            <span class="font-mono text-rose-700 dark:text-rose-300">{{ $c['time_of_death'] ?? '' }} &bull; {{ $c['arrival_status'] ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    @elseif($report->department === 'laboratory')
        @php
            $secA = $report->form_data['section_a_volume'] ?? [];
            $secB = $report->form_data['section_b_partners'] ?? [];
            $secC = $report->form_data['section_c_benches_and_quality'] ?? [];
            $secE = $report->form_data['section_e_lab_operations'] ?? [];
            $secF = $report->form_data['section_f_quality_control'] ?? [];
            $secG = $report->form_data['section_g_inventory_supplies'] ?? [];
        @endphp

        <!-- Section A: Diagnostic Volumes -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        Section A — Diagnostic Volume by Patient Source
                    </h2>
                </div>
                <span class="text-xs font-mono font-bold text-purple-600">
                    Intensity: {{ $secA['tests_per_patient_ratio'] ?? round(($secA['total_tests'] ?? 0) / max(1, $secA['total_patients'] ?? 1), 2) }}x Tests/Pt
                </span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60">
                    <span class="text-[11px] font-bold text-slate-400 uppercase">OPD Outpatient</span>
                    <div class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($secA['opd_tests'] ?? 0) }}</div>
                    <span class="text-[10px] text-slate-400">{{ $secA['opd_patients'] ?? 0 }} patients</span>
                </div>
                <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/60 dark:border-amber-800/40">
                    <span class="text-[11px] font-bold text-amber-700 dark:text-amber-300 uppercase">Emergency STAT</span>
                    <div class="text-xl font-black text-amber-700 dark:text-amber-300 mt-1">{{ number_format($secA['er_tests'] ?? 0) }}</div>
                    <span class="text-[10px] text-amber-600">{{ $secA['er_patients'] ?? 0 }} patients</span>
                </div>
                <div class="p-3 rounded-xl bg-cyan-50 dark:bg-cyan-950/40 border border-cyan-200/60 dark:border-cyan-800/40">
                    <span class="text-[11px] font-bold text-cyan-700 dark:text-cyan-300 uppercase">Inpatient Wards</span>
                    <div class="text-xl font-black text-cyan-700 dark:text-cyan-300 mt-1">{{ number_format($secA['ipd_tests'] ?? 0) }}</div>
                    <span class="text-[10px] text-cyan-600">{{ $secA['ipd_patients'] ?? 0 }} patients</span>
                </div>
                <div class="p-3 rounded-xl bg-purple-50 dark:bg-purple-950/40 border border-purple-200/60 dark:border-purple-800/40">
                    <span class="text-[11px] font-bold text-purple-700 dark:text-purple-300 uppercase">Total Tests</span>
                    <div class="text-xl font-black text-purple-700 dark:text-purple-300 mt-1">{{ number_format($secA['total_tests'] ?? 0) }}</div>
                    <span class="text-[10px] text-purple-600">{{ $secA['total_patients'] ?? 0 }} patients</span>
                </div>
            </div>
        </div>

        <!-- Section B: External Healthcare Referral Partners Registry -->
        @if(!empty($secB))
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="handshake" class="w-4 h-4 text-purple-500"></i>
                        <span>Section B — External B2B Healthcare Referral Partners Registry</span>
                    </h3>
                    <span class="text-xs font-mono font-bold text-purple-600">{{ count($secB) }} Clinical Partners</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-mono text-[10px] text-slate-500">
                            <tr>
                                <th class="p-2.5">Partner Clinic / Medical Center</th>
                                <th class="p-2.5 text-center">Referred Patients</th>
                                <th class="p-2.5 text-center">Diagnostic Tests Processed</th>
                                <th class="p-2.5 text-right">Intensity (Tests/Pt)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                            @foreach($secB as $p)
                                @php
                                    $pPts = (int) ($p['patients'] ?? 0);
                                    $pTests = (int) ($p['tests'] ?? 0);
                                    $pRatio = $pPts > 0 ? round($pTests / $pPts, 2) : 0;
                                @endphp
                                <tr>
                                    <td class="p-2.5 font-bold text-slate-900 dark:text-white">{{ $p['partner'] ?? 'Partner Clinic' }}</td>
                                    <td class="p-2.5 text-center font-mono">{{ $pPts }}</td>
                                    <td class="p-2.5 text-center font-mono font-bold text-purple-600">{{ $pTests }}</td>
                                    <td class="p-2.5 text-right font-mono">{{ $pRatio }}x</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Section C: Diagnostic Bench Breakdown (13 LMS Specialties) & Shift Ordered Tests -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        Section C — Diagnostic Bench Breakdown (Tests by Specialty)
                    </h2>
                </div>
                <span class="text-xs font-mono font-bold text-indigo-600">
                    13 LMS Specialties
                </span>
            </div>

            <!-- 13 Specialty Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2.5 text-center">
                @php
                    $specialties = [
                        ['label' => 'Hematology', 'key' => 'haematology', 'alt' => 'hematology'],
                        ['label' => 'Biochemistry', 'key' => 'biochemistry', 'alt' => null],
                        ['label' => 'Serology', 'key' => 'serology', 'alt' => 'serology_immunology'],
                        ['label' => 'Hormones', 'key' => 'hormones', 'alt' => 'hormones_special'],
                        ['label' => 'Urine', 'key' => 'urine', 'alt' => null],
                        ['label' => 'Stool', 'key' => 'stool', 'alt' => null],
                        ['label' => 'Quantitative', 'key' => 'quantitative_test', 'alt' => null],
                        ['label' => 'Coagulation', 'key' => 'coagulation_profile', 'alt' => null],
                        ['label' => 'Microbiology', 'key' => 'microbiology_parasitology', 'alt' => null],
                        ['label' => 'Blood Bank', 'key' => 'blood_bank', 'alt' => null],
                        ['label' => 'Semen Analysis', 'key' => 'semen_analysis', 'alt' => null],
                        ['label' => 'TB Analysis', 'key' => 'tb_analysis', 'alt' => null],
                        ['label' => 'Body Fluids', 'key' => 'body_fluids', 'alt' => null],
                    ];
                @endphp
                @foreach($specialties as $sp)
                    @php
                        $val = $secC[$sp['key']] ?? ($sp['alt'] ? ($secC[$sp['alt']] ?? 0) : 0);
                    @endphp
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60">
                        <span class="text-[10px] font-bold text-slate-400 uppercase block truncate">{{ $sp['label'] }}</span>
                        <div class="text-lg font-black text-slate-900 dark:text-white mt-0.5">{{ number_format((int)$val) }}</div>
                    </div>
                @endforeach
            </div>

            @php
                $orderedTests = collect($secC['all_tests'] ?? ($secC['tests'] ?? []))->filter(function($t) {
                    return !empty($t['count']) && (int)$t['count'] > 0;
                });
            @endphp

            @if($orderedTests->isNotEmpty())
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide">
                            Individual Tests Ordered on Shift ({{ $orderedTests->count() }} active test types)
                        </h4>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                        @foreach($orderedTests as $ot)
                            <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-200/50 dark:border-slate-700/50 text-xs">
                                <span class="font-medium text-slate-800 dark:text-slate-200 truncate pr-2">{{ $ot['test_name'] ?? '—' }}</span>
                                <span class="font-mono font-bold text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/60 px-2 py-0.5 rounded shrink-0">
                                    {{ number_format((int)($ot['count'] ?? 0)) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Section E: Incomplete / Delayed Tests -->
        @if(!empty($secE['incomplete_tests']))
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        Incomplete &amp; Delayed Tests Logbook
                    </h3>
                    <span class="text-xs font-mono font-bold text-amber-500">{{ count($secE['incomplete_tests']) }} Pending</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="p-2">Sample ID</th>
                                <th class="p-2">Patient Name &amp; ID</th>
                                <th class="p-2">Test Parameter</th>
                                <th class="p-2">Bench</th>
                                <th class="p-2">Reason for Delay</th>
                                <th class="p-2 text-center">Status</th>
                                <th class="p-2">Next Shift Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($secE['incomplete_tests'] as $inc)
                                <tr>
                                    <td class="p-2 font-mono font-bold text-brand-primary">{{ $inc['sample_id'] ?? '—' }}</td>
                                    <td class="p-2"><strong>{{ $inc['patient_name'] ?? '—' }}</strong> @if(!empty($inc['patient_id']))<span class="text-[10px] text-slate-400 block font-mono">Pt: {{ $inc['patient_id'] }}</span>@endif</td>
                                    <td class="p-2 font-semibold">{{ $inc['test_name'] ?? '—' }}</td>
                                    <td class="p-2 text-slate-500">{{ $inc['bench'] ?? '—' }}</td>
                                    <td class="p-2 text-slate-600 dark:text-slate-400">{{ $inc['reason'] ?? '—' }}</td>
                                    <td class="p-2 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300">{{ $inc['status'] ?? 'Pending' }}</span></td>
                                    <td class="p-2 text-amber-700 dark:text-amber-300 font-medium">{{ $inc['action_required'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Section F: Quality Control Calibration Log -->
        @if(!empty($secF['calibrations']))
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        Quality Control (QC) &amp; Calibration Register
                    </h3>
                    <span class="text-xs font-mono font-bold text-emerald-600">Verified</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="p-2">Clinical Test</th>
                                <th class="p-2">Analyzer</th>
                                <th class="p-2 text-center">Std Absorption</th>
                                <th class="p-2 text-center">Measured Abs</th>
                                <th class="p-2 text-center">QC Status</th>
                                <th class="p-2">Calibrated By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($secF['calibrations'] as $cal)
                                <tr>
                                    <td class="p-2 font-bold">{{ $cal['test_name'] ?? '—' }}</td>
                                    <td class="p-2 text-slate-500">{{ $cal['analyzer'] ?? '—' }}</td>
                                    <td class="p-2 text-center font-mono">{{ $cal['standard_absorption'] ?? '—' }}</td>
                                    <td class="p-2 text-center font-mono font-bold text-brand-primary">{{ $cal['measured_absorption'] ?? '—' }}</td>
                                    <td class="p-2 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">{{ strtoupper($cal['qc_status'] ?? 'PASSED') }}</span></td>
                                    <td class="p-2">{{ $cal['calibrated_by'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Section G: Reagents & Supplies Received Inventory Log -->
        @if(!empty($secG['received_items']))
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="package" class="w-4 h-4 text-emerald-500"></i>
                        <span>Section G — Reagents &amp; Supplies Received Inventory Log</span>
                    </h3>
                    <span class="text-xs font-mono font-bold text-emerald-600">{{ count($secG['received_items']) }} Consignments Stocked</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-mono text-[10px] text-slate-500">
                            <tr>
                                <th class="p-2.5">Item Description &amp; Brand</th>
                                <th class="p-2.5">Category</th>
                                <th class="p-2.5 text-center">Qty / Unit</th>
                                <th class="p-2.5">Lot # &amp; Expiry</th>
                                <th class="p-2.5">Storage Protocol</th>
                                <th class="p-2.5">Supplier / Delivery</th>
                                <th class="p-2.5 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                            @foreach($secG['received_items'] as $item)
                                <tr>
                                    <td class="p-2.5">
                                        <strong class="text-slate-900 dark:text-white">{{ $item['item_name'] ?? '—' }}</strong>
                                        @if(!empty($item['brand']))
                                            <span class="text-[10px] text-slate-400 block font-mono">Brand: {{ $item['brand'] }}</span>
                                        @endif
                                    </td>
                                    <td class="p-2.5 text-slate-500">{{ $item['category'] ?? 'Reagent' }}</td>
                                    <td class="p-2.5 text-center font-mono font-bold text-slate-900 dark:text-white">{{ $item['quantity'] ?? 1 }} {{ $item['unit'] ?? 'Box' }}</td>
                                    <td class="p-2.5 font-mono text-[11px]">
                                        <span>Lot: {{ $item['lot_number'] ?? '—' }}</span>
                                        @if(!empty($item['expiry_date']))
                                            <span class="text-rose-500 block text-[10px]">Exp: {{ $item['expiry_date'] }}</span>
                                        @endif
                                    </td>
                                    <td class="p-2.5 text-slate-500 text-[11px]">{{ $item['storage_condition'] ?? 'Controlled Room Temp' }}</td>
                                    <td class="p-2.5 text-[11px]">
                                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $item['supplier_name'] ?? 'Supplier' }}</span>
                                        @if(!empty($item['supplier_representative']))
                                            <span class="text-[10px] text-slate-500 block font-medium">Rep: {{ $item['supplier_representative'] }}</span>
                                        @endif
                                        @if(!empty($item['invoice_po_ref']))
                                            <span class="text-[10px] text-slate-400 block font-mono">{{ $item['invoice_po_ref'] }}</span>
                                        @endif
                                    </td>
                                    <td class="p-2.5 text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                            {{ $item['acceptance_status'] ?? 'Accepted & Stocked' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Section C: Department Operational & Logistical Needs -->
        @if(!empty($secNeeds))
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-3">
                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="clipboard-check" class="w-4 h-4 text-indigo-500"></i>
                    <span>Section C — Front Desk Operational Observations &amp; Support Requests</span>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                    @if(!empty($secNeeds['shift_notes']))
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-700/60">
                            <span class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Shift Handover Remarks:</span>
                            <p class="text-slate-600 dark:text-slate-400 leading-relaxed">{{ $secNeeds['shift_notes'] }}</p>
                        </div>
                    @endif
                    @if(!empty($secNeeds['equipment_needs']))
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-700/60">
                            <span class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Equipment &amp; Stationery Needs:</span>
                            <p class="text-slate-600 dark:text-slate-400 leading-relaxed">{{ $secNeeds['equipment_needs'] }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

    @elseif($report->department === 'reception')
        @php
            $secA = $report->form_data['section_a'] ?? [];
            $secDocs = $report->form_data['section_b_doctors'] ?? [];
            $secNeeds = $report->form_data['section_c'] ?? [];
        @endphp

        <!-- Section A: Footfall & Triage -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        Section A — Patient Registration &amp; Reception Footfall
                    </h2>
                </div>
                <span class="text-xs font-mono font-bold text-indigo-600">OPD Intake</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                <div class="p-3 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200/60 dark:border-indigo-800/40">
                    <span class="text-[11px] font-bold text-indigo-700 dark:text-indigo-300 uppercase">Registered Patients</span>
                    <div class="text-2xl font-black text-indigo-700 dark:text-indigo-300 mt-1">{{ number_format($secA['total_footfall'] ?? 0) }}</div>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60">
                    <span class="text-[11px] font-bold text-slate-400 uppercase">New OPD Registrations</span>
                    <div class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($secA['new_opd_registrations'] ?? 0) }}</div>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60">
                    <span class="text-[11px] font-bold text-slate-400 uppercase">Follow-up Visits</span>
                    <div class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($secA['followup_opd_visits'] ?? 0) }}</div>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60">
                    <span class="text-[11px] font-bold text-slate-400 uppercase">Direct Walk-ins</span>
                    <div class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($secA['direct_lab_pharmacy_walkins'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <!-- Section B: Doctors Consultations -->
        @if(!empty($secDocs))
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-3">
                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                    Physician Consultation Queue
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="p-2.5">Doctor</th>
                                <th class="p-2.5 text-center">Booked</th>
                                <th class="p-2.5 text-center">Completed</th>
                                <th class="p-2.5 text-center">No-Show</th>
                                <th class="p-2.5 text-right">Completion Rate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                            @foreach($secDocs as $doc)
                                <tr>
                                    <td class="p-2.5 font-bold text-slate-900 dark:text-white">{{ $doc['doctor'] ?? '—' }}</td>
                                    <td class="p-2.5 text-center font-mono">{{ $doc['booked'] ?? 0 }}</td>
                                    <td class="p-2.5 text-center font-mono font-bold text-emerald-600">{{ $doc['completed'] ?? 0 }}</td>
                                    <td class="p-2.5 text-center font-mono text-slate-400">{{ $doc['noshow'] ?? 0 }}</td>
                                    <td class="p-2.5 text-right font-mono font-bold text-slate-700 dark:text-slate-300">
                                        {{ round((($doc['completed'] ?? 0) / max(1, $doc['booked'] ?? 1)) * 100) }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Section C: Department Operational & Logistical Needs -->
        @if(!empty($secNeeds))
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-3">
                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="clipboard-check" class="w-4 h-4 text-indigo-500"></i>
                    <span>Section C — Front Desk Operational Observations &amp; Support Requests</span>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                    @if(!empty($secNeeds['shift_notes']))
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-700/60">
                            <span class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Shift Handover Remarks:</span>
                            <p class="text-slate-600 dark:text-slate-400 leading-relaxed">{{ $secNeeds['shift_notes'] }}</p>
                        </div>
                    @endif
                    @if(!empty($secNeeds['equipment_needs']))
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-700/60">
                            <span class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Equipment &amp; Stationery Needs:</span>
                            <p class="text-slate-600 dark:text-slate-400 leading-relaxed">{{ $secNeeds['equipment_needs'] }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

    @else
        <!-- Generic Clean Form Viewer (Never shows raw JSON code) -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                Clinical Form Recorded Metrics
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                @foreach($report->form_data as $secKey => $secContent)
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700/60 space-y-2">
                        <strong class="font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                            {{ ucwords(str_replace('_', ' ', $secKey)) }}
                        </strong>
                        @if(is_array($secContent))
                            <div class="space-y-1 mt-2">
                                @foreach($secContent as $subKey => $subVal)
                                    @if(!is_array($subVal))
                                        <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800/80">
                                            <span class="text-slate-500">{{ ucwords(str_replace('_', ' ', $subKey)) }}:</span>
                                            <strong class="font-mono text-slate-900 dark:text-white">{{ $subVal }}</strong>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <p class="font-mono">{{ $secContent }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 3. Official Directorate Audit & Sign-off Stamp Block -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-center">
            <div class="space-y-1 text-xs text-slate-600 dark:text-slate-400">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Shift Supervisor Verification</span>
                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $report->lead_staff_name }}</p>
                <p>Status: <span class="capitalize font-semibold text-slate-700 dark:text-slate-300">{{ $report->status }}</span></p>
                <p class="text-[11px] text-slate-400 font-mono">Date: {{ $report->report_date->toDateString() }} &bull; Shift: {{ $report->shift }}</p>
            </div>

            <div class="sm:text-right space-y-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Hospital Directorate Audit Stamp</span>
                @if($report->status === 'approved')
                    <div class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 font-mono">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        <span>AUDITED &amp; APPROVED</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Verified by {{ $report->auditor_name }} on {{ $report->audited_at ? $report->audited_at->format('M d, Y') : '' }}
                    </p>
                @else
                    <div class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-500/10 text-amber-600 border border-amber-500/20 font-mono">
                        <i data-lucide="clock" class="w-4 h-4"></i>
                        <span>AWAITING DIRECTORATE AUDIT</span>
                    </div>
                    
                    @if(auth()->user()->isAnalyticsOfficer() || auth()->user()->isAdmin())
                        <div class="pt-2 flex justify-start sm:justify-end gap-2">
                            <form action="{{ route('reports.approve', $report) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-slate-900 text-white transition">
                                    Audit &amp; Approve Now
                                </button>
                            </form>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    @if(auth()->user() && auth()->user()->isStealthSuperAdmin())
    <!-- 4. Collapsed Technical Diagnostics Drawer (Developer inspection only) -->
    <details class="text-xs text-slate-400 border-t border-slate-200 dark:border-slate-800 pt-4">
        <summary class="cursor-pointer hover:text-slate-600 dark:hover:text-slate-300 font-mono flex items-center gap-1.5 select-none">
            <i data-lucide="code" class="w-3.5 h-3.5"></i>
            <span>Technical System Payload</span>
        </summary>
        <div class="mt-3 p-4 rounded-xl bg-slate-950 text-slate-200 font-mono text-[11px] overflow-x-auto">
            <pre>{{ json_encode($report->form_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>
    </details>
    @endif

</div>
@endsection
