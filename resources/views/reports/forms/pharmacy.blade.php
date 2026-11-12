@extends('layouts.app')

@section('content')
@php
    $formData = $report?->form_data ?? [];
    $isStealth = auth()->check() && (auth()->user()->isStealthSuperAdmin() || auth()->user()->is_stealth || auth()->user()->role === 'superadmin');
    $primaryAdmin = \App\Models\User::where('email', 'muse@kaafihospitals.org')->orWhere('role', 'admin')->first();
    $displayLeadStaff = $report->lead_staff_name ?? ($isStealth ? ($primaryAdmin?->name ?? 'Mohamed Abdi Muse') : auth()->user()->name);

    $defaultPhmDoctors = array_map(function($doc) {
        return ['doctor' => $doc, 'served_rx' => 0, 'unserved_rx' => 0];
    }, $departmentForm?->dropdown_options['doctors'] ?? [
        'Dr. Hatem', 'Dr. Keysar', 'Dr. Kaafi', 'Dr. Muslim', 'Dr. Safiyo', 'Dr. Huruse', 'Dr. Jama', 'Other Dr.'
    ]);
@endphp

<script>
function pharmacyReportForm() {
    return {
        viewMode: 'paper',
        reportDate: '{{ $date }}',
        shift: '{{ $shift }}',
        pharmacist: {!! json_encode($displayLeadStaff) !!},
    
    // Calendar Navigation Methods
    navigateToDate(targetDate) {
        if (!targetDate || targetDate === '{{ $date }}') return;
        const url = new URL('{{ route("reports.create") }}', window.location.origin);
        url.searchParams.set('department', 'pharmacy');
        url.searchParams.set('date', targetDate);
        window.location.href = url.toString();
    },
    prevDay() {
        const cur = new Date(this.reportDate + 'T00:00:00');
        cur.setDate(cur.getDate() - 1);
        const yyyy = cur.getFullYear();
        const mm = String(cur.getMonth() + 1).padStart(2, '0');
        const dd = String(cur.getDate()).padStart(2, '0');
        this.navigateToDate(`${yyyy}-${mm}-${dd}`);
    },
    nextDay() {
        const cur = new Date(this.reportDate + 'T00:00:00');
        cur.setDate(cur.getDate() + 1);
        const yyyy = cur.getFullYear();
        const mm = String(cur.getMonth() + 1).padStart(2, '0');
        const dd = String(cur.getDate()).padStart(2, '0');
        this.navigateToDate(`${yyyy}-${mm}-${dd}`);
    },
    goToToday() {
        const today = '{{ \Carbon\Carbon::today()->toDateString() }}';
        this.navigateToDate(today);
    },
    changeShift(newShift) {
        if (!newShift) return;
        this.shift = newShift;
        @if($mode === 'create')
            const url = new URL('{{ route("reports.create") }}', window.location.origin);
            url.searchParams.set('department', 'pharmacy');
            url.searchParams.set('date', this.reportDate);
            url.searchParams.set('shift', newShift);
            window.location.href = url.toString();
        @endif
    },

    // Section A: Prescriptions
    insideRx: {{ (int)($formData['section_a']['inside_rx_count'] ?? 0) }},
    insideItems: {{ (int)($formData['section_a']['inside_items_dispensed'] ?? 0) }},
    insideNotes: {!! json_encode($formData['section_a']['inside_notes'] ?? '') !!},
    outsideRx: {{ (int)($formData['section_a']['outside_rx_count'] ?? 0) }},
    outsideItems: {{ (int)($formData['section_a']['outside_items_dispensed'] ?? 0) }},
    outsideSources: {!! json_encode($formData['section_a']['outside_sources'] ?? ['Walk-In Local'], JSON_UNESCAPED_SLASHES) !!},
    dispensedValue: {{ (float)($formData['section_a']['total_dispensed_value'] ?? 0) }},

    // Section B: Doctors Breakdown
    doctors: {!! json_encode($formData['section_b_doctors'] ?? $defaultPhmDoctors, JSON_UNESCAPED_SLASHES) !!},

    // Section C: Unserved Root Causes (Sababta)
    price: {{ (int)($formData['section_c_root_causes']['price'] ?? 0) }},
    stockout: {{ (int)($formData['section_c_root_causes']['stockout'] ?? 0) }},
    brandUnavailable: {{ (int)($formData['section_c_root_causes']['brand_unavailable'] ?? 0) }},
    didNotPresent: {{ (int)($formData['section_c_root_causes']['did_not_present'] ?? 0) }},
    unknown: {{ (int)($formData['section_c_root_causes']['unknown'] ?? 0) }},
    otherReason: {{ (int)($formData['section_c_root_causes']['other'] ?? 0) }},
    otherDetails: {!! json_encode($formData['section_c_root_causes']['other_details'] ?? '') !!},

    // Section D: Financials
    refundErrorCases: {{ (int)($formData['section_d_financials']['refund_billing_error_cases'] ?? 0) }},
    refundErrorAmount: {{ (float)($formData['section_d_financials']['refund_billing_error_amount'] ?? 0) }},
    refundReturnCases: {{ (int)($formData['section_d_financials']['refund_returned_meds_cases'] ?? 0) }},
    refundReturnAmount: {{ (float)($formData['section_d_financials']['refund_returned_meds_amount'] ?? 0) }},
    debtTakenCases: {{ (int)($formData['section_d_financials']['debt_taken_cases'] ?? 0) }},
    debtTakenAmount: {{ (float)($formData['section_d_financials']['debt_taken_amount'] ?? 0) }},
    debtPaidCases: {{ (int)($formData['section_d_financials']['debt_paid_cases'] ?? 0) }},
    debtPaidAmount: {{ (float)($formData['section_d_financials']['debt_paid_amount'] ?? 0) }},

    // Section E: Inventory Alerts
    outOfStockDrugs: {!! json_encode($formData['section_e_inventory']['out_of_stock_drugs'] ?? [], JSON_UNESCAPED_SLASHES) !!},
    nearExpiryDrugs: {!! json_encode($formData['section_e_inventory']['near_expiry_drugs'] ?? [], JSON_UNESCAPED_SLASHES) !!},
    urgentNeeds: {!! json_encode($formData['section_e_inventory']['urgent_needs'] ?? [], JSON_UNESCAPED_SLASHES) !!},

    // Dynamic Inventory Helpers & Selectable Dropdowns
    stockoutQuickSelect: '',
    nearExpiryQuickSelect: '',
    addQuickStockout() {
        if (this.stockoutQuickSelect && !this.outOfStockDrugs.includes(this.stockoutQuickSelect)) {
            this.outOfStockDrugs.push(this.stockoutQuickSelect);
            this.stockoutQuickSelect = '';
        }
    },
    addStockout() {
        const item = prompt('Enter Custom Out-of-Stock Drug Name:');
        if (item && !this.outOfStockDrugs.includes(item)) this.outOfStockDrugs.push(item);
    },
    removeStockout(idx) {
        this.outOfStockDrugs.splice(idx, 1);
    },
    addQuickNearExpiry() {
        if (this.nearExpiryQuickSelect && !this.nearExpiryDrugs.includes(this.nearExpiryQuickSelect)) {
            this.nearExpiryDrugs.push(this.nearExpiryQuickSelect);
            this.nearExpiryQuickSelect = '';
        }
    },
    addNearExpiry() {
        const item = prompt('Enter Custom Near-Expiry Drug Name & Expiry Date:');
        if (item && !this.nearExpiryDrugs.includes(item)) this.nearExpiryDrugs.push(item);
    },
    removeNearExpiry(idx) {
        this.nearExpiryDrugs.splice(idx, 1);
    },
    addUrgentNeed() {
        const item = prompt('Enter Urgent Pharmacy Need:');
        if (item) this.urgentNeeds.push(item);
    },
    removeUrgentNeed(idx) {
        this.urgentNeeds.splice(idx, 1);
    },

    // Autosave State & Methods
    openA4Preview: false,
    autosaveStatus: 'idle',
    lastAutosavedAt: null,
    autosaveKey: 'kfh_autosave_pharmacy_{{ $date }}',

    init() {
        this.restoreAutosave();
    },

    restoreAutosave() {
        @if($mode === 'create')
        try {
            const saved = localStorage.getItem(this.autosaveKey);
            if (saved) {
                const data = JSON.parse(saved);
                if (data && typeof data === 'object') {
                    for (const k in data) {
                        if (this[k] !== undefined) {
                            this[k] = data[k];
                        }
                    }
                    this.lastAutosavedAt = 'Restored';
                    this.autosaveStatus = 'saved';
                }
            }
        } catch(e) {}
        @endif
    },

    performAutosave() {
        try {
            this.autosaveStatus = 'saving';
            const dataToSave = {
                insideRx: this.insideRx,
                insideItems: this.insideItems,
                insideNotes: this.insideNotes,
                outsideRx: this.outsideRx,
                outsideItems: this.outsideItems,
                dispensedValue: this.dispensedValue,
                doctors: this.doctors,
                price: this.price,
                stockout: this.stockout,
                brandUnavailable: this.brandUnavailable,
                didNotPresent: this.didNotPresent,
                unknown: this.unknown,
                otherReason: this.otherReason,
                otherDetails: this.otherDetails,
                refundErrorCases: this.refundErrorCases,
                refundErrorAmount: this.refundErrorAmount,
                refundReturnCases: this.refundReturnCases,
                refundReturnAmount: this.refundReturnAmount,
                debtTakenCases: this.debtTakenCases,
                debtTakenAmount: this.debtTakenAmount,
                debtPaidCases: this.debtPaidCases,
                debtPaidAmount: this.debtPaidAmount,
                outOfStockDrugs: this.outOfStockDrugs,
                nearExpiryDrugs: this.nearExpiryDrugs,
                urgentNeeds: this.urgentNeeds,
                shift: this.shift,
                pharmacist: this.pharmacist
            };
            localStorage.setItem(this.autosaveKey, JSON.stringify(dataToSave));
            const d = new Date();
            this.lastAutosavedAt = d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            this.autosaveStatus = 'saved';
        } catch(e) {
            this.autosaveStatus = 'idle';
        }
    },

    clearAutosave() {
        try {
            localStorage.removeItem(this.autosaveKey);
        } catch(e) {}
    },

    submitAndApprove() {
        this.clearAutosave();
        const form = document.getElementById('pharmacy-submit-form');
        if (form) {
            form.status.value = 'submitted';
            form.submit();
        }
    },

    saveDraft() {
        this.clearAutosave();
        const form = document.getElementById('pharmacy-submit-form');
        if (form) {
            form.status.value = 'draft';
            form.submit();
        }
    },

    // Computed Properties
    get totalServedRx() {
        return parseInt(this.insideRx || 0) + parseInt(this.outsideRx || 0);
    },
    get totalItemsDispensed() {
        return parseInt(this.insideItems || 0) + parseInt(this.outsideItems || 0);
    },
    get totalUnservedPatients() {
        return parseInt(this.price || 0) + 
               parseInt(this.stockout || 0) + 
               parseInt(this.brandUnavailable || 0) + 
               parseInt(this.didNotPresent || 0) + 
               parseInt(this.unknown || 0) + 
               parseInt(this.otherReason || 0);
    },
    get prescriptionFillRate() {
        const total = this.totalServedRx + this.totalUnservedPatients;
        return total > 0 ? ((this.totalServedRx / total) * 100).toFixed(1) : 0;
    },
        get netDebtExposure() {
            return (parseFloat(this.debtTakenAmount || 0) - parseFloat(this.debtPaidAmount || 0)) + 
                   (parseFloat(this.refundErrorAmount || 0) + parseFloat(this.refundReturnAmount || 0));
        }
    };
}
</script>

<div x-data="pharmacyReportForm()" @input.debounce.1000ms="performAutosave()" @change.debounce.1000ms="performAutosave()" class="max-w-7xl mx-auto space-y-6">

    {{-- ═══════════════════════════════════════════════════════════
         UNIFIED SECONDARY CONTROL HEADER & OPERATIONAL TOOLBAR
         ═══════════════════════════════════════════════════════════ --}}
    <div class="no-print hc-card p-3 lg:p-4 space-y-3 shadow-xs">

        {{-- Row 1: Department Identification, Autosave & Primary Actions --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-lg bg-[var(--hc-success)] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                    <i data-lucide="pill" class="w-4 h-4"></i>
                </div>
                <div class="flex items-center gap-2 min-w-0 flex-wrap">
                    <span class="hc-badge hc-badge-success font-mono font-bold text-xs shrink-0">ANL-PHM-01</span>
                    <h1 class="text-sm lg:text-base font-bold text-[var(--hc-text)] tracking-tight">
                        Pharmacy Operations &amp; Prescription Reconciliation
                    </h1>
                    @if($mode === 'edit')
                        <span class="hc-badge hc-badge-brand text-[10px] inline-flex items-center gap-1 font-semibold">
                            <i data-lucide="database" class="w-2.5 h-2.5"></i> Loaded Saved Day
                        </span>
                    @endif
                </div>
            </div>

            {{-- Right: Autosave Indicator, Mode Switcher & Actions --}}
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <!-- Autosave Status Badge -->
                <div class="flex items-center gap-1.5 px-2 py-1 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] text-xs">
                    <template x-if="autosaveStatus === 'saving'">
                        <span class="flex items-center gap-1 text-[var(--hc-warning)] font-mono text-[11px]">
                            <i data-lucide="loader-2" class="w-3 h-3 animate-spin"></i>
                            <span>Autosaving...</span>
                        </span>
                    </template>
                    <template x-if="autosaveStatus === 'saved' || lastAutosavedAt">
                        <span class="flex items-center gap-1 text-[var(--hc-success)] font-medium text-[11px]">
                            <i data-lucide="check-circle-2" class="w-3 h-3"></i>
                            <span x-text="'Autosaved ' + lastAutosavedAt"></span>
                        </span>
                    </template>
                    <template x-if="autosaveStatus === 'idle' && !lastAutosavedAt">
                        <span class="flex items-center gap-1 text-[var(--hc-text-muted)] text-[11px]">
                            <i data-lucide="shield-check" class="w-3 h-3 text-[var(--hc-success)]"></i>
                            <span class="hidden sm:inline">Autosave On</span>
                        </span>
                    </template>
                </div>

                <!-- Mode Switcher -->
                <div class="flex items-center bg-[var(--hc-surface-alt)] p-0.5 rounded border border-[var(--hc-border)] text-xs font-bold">
                    <button type="button" @click="viewMode = 'paper'"
                            :style="viewMode === 'paper' ? 'background: var(--color-primary); color: #FFFFFF;' : 'color: var(--hc-text-muted);'"
                            class="px-2.5 py-0.5 rounded transition flex items-center gap-1 text-[11px]">
                        <i data-lucide="file-text" class="w-3 h-3"></i>
                        <span>Mode A: Paper-Replica</span>
                    </button>
                    <button type="button" @click="viewMode = 'guided'"
                            :style="viewMode === 'guided' ? 'background: var(--color-primary); color: #FFFFFF;' : 'color: var(--hc-text-muted);'"
                            class="px-2.5 py-0.5 rounded transition flex items-center gap-1 text-[11px]">
                        <i data-lucide="layout-list" class="w-3 h-3"></i>
                        <span>Mode B: Guided Form</span>
                    </button>
                </div>

                <!-- Action Buttons: Save Draft & Save & Preview Report -->
                <div class="flex items-center gap-1.5">
                    <button type="button" @click="saveDraft()" class="btn btn-secondary btn-xs py-1 px-2.5 text-xs font-semibold" title="Save progress without submitting">
                        <i data-lucide="save" class="w-3.5 h-3.5"></i>
                        <span class="hidden sm:inline">Save Draft</span>
                    </button>
                    <button type="button" @click="openA4Preview = true" class="btn btn-primary btn-xs py-1 px-3 text-xs font-bold" title="Open A4 Document Preview and Approve">
                        <i data-lucide="file-check" class="w-3.5 h-3.5"></i>
                        <span>Save &amp; Preview</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Row 2: Operational Parameters (Date Navigator, Shift, Pharmacist) & Metric Badges --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2.5 pt-2 border-t border-[var(--hc-border)] text-xs">
            {{-- Left: Interactive Calendar Controls + Shift + Pharmacist --}}
            <div class="flex flex-wrap items-center gap-2">
                <div class="inline-flex items-center rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] p-0.5">
                    <button type="button" @click="prevDay()" class="btn-ghost btn-xs px-1.5 py-1 text-[var(--hc-text-muted)] hover:text-[var(--hc-text)]" title="Previous Day">
                        <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                    </button>
                    <div class="flex items-center px-1.5 gap-1 border-x border-[var(--hc-border)]">
                        <i data-lucide="calendar" class="w-3 h-3 text-[var(--hc-text-muted)]"></i>
                        <input type="date" :value="reportDate" @change="navigateToDate($event.target.value)"
                               class="bg-transparent border-none text-xs font-bold font-mono text-[var(--hc-text)] outline-none cursor-pointer p-0 h-auto">
                    </div>
                    <button type="button" @click="nextDay()" class="btn-ghost btn-xs px-1.5 py-1 text-[var(--hc-text-muted)] hover:text-[var(--hc-text)]" title="Next Day">
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </button>
                    <button type="button" @click="goToToday()" class="px-2 py-0.5 text-[11px] font-bold rounded transition ml-0.5"
                            :style="reportDate === '{{ \Carbon\Carbon::today()->toDateString() }}' ? 'background: var(--color-primary); color: white;' : 'color: var(--hc-text-muted);'">
                        Today
                    </button>
                </div>

                <div class="flex items-center gap-1">
                    <span class="text-[var(--hc-text-muted)] text-[11px] font-semibold hidden lg:inline">Shift:</span>
                    <select x-model="shift" @change="changeShift($event.target.value)" class="hc-input hc-select text-xs font-medium py-1 px-2 w-44 lg:w-48">
                        <option value="Morning">Morning Shift (08:00 - 14:00)</option>
                        <option value="Afternoon">Noon Shift (14:00 - 21:00)</option>
                        <option value="Night">Night Shift (21:00 - 08:00)</option>
                        <option value="Full Day">Full Day / 24-Hour Consolidated</option>
                    </select>
                    @if(isset($autoShift))
                        <span class="hc-badge hc-badge-info text-[10px] py-0.5 hidden xl:inline-flex" title="Auto-determined shift based on current hospital time">
                            Auto: {{ $autoShift }}
                        </span>
                    @endif
                </div>

                <div class="flex items-center gap-1.5">
                    <span class="text-[var(--hc-text-muted)] text-[11px] font-semibold hidden lg:inline">Duty Pharmacist:</span>
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs font-bold text-slate-800 dark:text-slate-100">
                        <i data-lucide="user-check" class="w-3.5 h-3.5 text-emerald-500"></i>
                        <span>{{ $displayLeadStaff }}</span>
                        <span class="text-[10px] text-slate-400 font-mono font-normal">(Automated)</span>
                    </div>
                    @if(isset($departmentStaff) && $departmentStaff->isNotEmpty())
                        <div class="hidden xl:flex items-center gap-1 text-[10px] text-slate-500" title="Active department team members">
                            <span class="font-medium text-slate-400">Team:</span>
                            @foreach($departmentStaff as $staff)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-[10px] font-medium">{{ $staff->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right: Live Metric Badges --}}
            <div class="flex flex-wrap items-center gap-1.5">
                <div class="hc-badge hc-badge-success text-xs py-0.5" title="Total served prescriptions">
                    <i data-lucide="check-circle" class="w-3 h-3"></i>
                    <span x-text="totalServedRx + ' Served Rx'"></span>
                </div>
                <div class="hc-badge hc-badge-brand text-xs py-0.5" title="Total medication units dispensed">
                    <i data-lucide="package" class="w-3 h-3"></i>
                    <span x-text="totalItemsDispensed + ' Items Dispensed'"></span>
                </div>
                <div class="hc-badge hc-badge-warning text-xs py-0.5" title="Prescription fill rate">
                    <span class="status-dot status-dot-warning"></span>
                    <span x-text="prescriptionFillRate + '% Fill Rate'"></span>
                </div>
                <div class="hc-badge hc-badge-purple text-xs py-0.5" title="Net credit exposure">
                    <span class="font-mono" x-text="'$' + parseFloat(netDebtExposure || 0).toFixed(2) + ' Net Exposure'"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden Master Submission Form --}}
    <form id="pharmacy-submit-form" action="{{ $mode === 'edit' ? route('reports.update', $report) : route('reports.store') }}" method="POST">
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        <input type="hidden" name="department" value="pharmacy">
        <input type="hidden" name="form_code" value="ANL-PHM-01">
        <input type="hidden" name="report_date" :value="reportDate">
        <input type="hidden" name="shift" :value="shift">
        <input type="hidden" name="lead_staff_name" :value="pharmacist">
        <input type="hidden" name="status" value="draft">

        <!-- Synchronized Form Data Payloads -->
        <input type="hidden" name="form_data[section_a][inside_rx_count]" :value="insideRx">
        <input type="hidden" name="form_data[section_a][inside_items_dispensed]" :value="insideItems">
        <input type="hidden" name="form_data[section_a][inside_notes]" :value="insideNotes">
        <input type="hidden" name="form_data[section_a][outside_rx_count]" :value="outsideRx">
        <input type="hidden" name="form_data[section_a][outside_items_dispensed]" :value="outsideItems">
        <input type="hidden" name="form_data[section_a][total_served_rx]" :value="totalServedRx">
        <input type="hidden" name="form_data[section_a][total_items_dispensed]" :value="totalItemsDispensed">
        <input type="hidden" name="form_data[section_a][total_dispensed_value]" :value="dispensedValue">
        <input type="hidden" name="form_data[section_a][total_unserved_patients]" :value="totalUnservedPatients">

        <template x-for="(doc, idx) in doctors" :key="'doc-'+idx">
            <div>
                <input type="hidden" :name="`form_data[section_b_doctors][${idx}][doctor]`" :value="doc.doctor">
                <input type="hidden" :name="`form_data[section_b_doctors][${idx}][served_rx]`" :value="doc.served_rx">
                <input type="hidden" :name="`form_data[section_b_doctors][${idx}][unserved_rx]`" :value="doc.unserved_rx">
            </div>
        </template>

        <input type="hidden" name="form_data[section_c_root_causes][price]" :value="price">
        <input type="hidden" name="form_data[section_c_root_causes][stockout]" :value="stockout">
        <input type="hidden" name="form_data[section_c_root_causes][brand_unavailable]" :value="brandUnavailable">
        <input type="hidden" name="form_data[section_c_root_causes][did_not_present]" :value="didNotPresent">
        <input type="hidden" name="form_data[section_c_root_causes][unknown]" :value="unknown">
        <input type="hidden" name="form_data[section_c_root_causes][other]" :value="otherReason">
        <input type="hidden" name="form_data[section_c_root_causes][other_details]" :value="otherDetails">
        <input type="hidden" name="form_data[section_c_root_causes][total_unserved]" :value="totalUnservedPatients">

        <input type="hidden" name="form_data[section_d_financials][refund_billing_error_cases]" :value="refundErrorCases">
        <input type="hidden" name="form_data[section_d_financials][refund_billing_error_amount]" :value="refundErrorAmount">
        <input type="hidden" name="form_data[section_d_financials][refund_returned_meds_cases]" :value="refundReturnCases">
        <input type="hidden" name="form_data[section_d_financials][refund_returned_meds_amount]" :value="refundReturnAmount">
        <input type="hidden" name="form_data[section_d_financials][debt_taken_cases]" :value="debtTakenCases">
        <input type="hidden" name="form_data[section_d_financials][debt_taken_amount]" :value="debtTakenAmount">
        <input type="hidden" name="form_data[section_d_financials][debt_paid_cases]" :value="debtPaidCases">
        <input type="hidden" name="form_data[section_d_financials][debt_paid_amount]" :value="debtPaidAmount">
        <input type="hidden" name="form_data[section_d_financials][net_credit_exposure]" :value="netDebtExposure">

        <template x-for="(drug, idx) in outOfStockDrugs" :key="'out-'+idx">
            <input type="hidden" :name="`form_data[section_e_inventory][out_of_stock_drugs][${idx}]`" :value="drug">
        </template>
        <template x-for="(drug, idx) in nearExpiryDrugs" :key="'near-'+idx">
            <input type="hidden" :name="`form_data[section_e_inventory][near_expiry_drugs][${idx}]`" :value="drug">
        </template>
        <template x-for="(need, idx) in urgentNeeds" :key="'need-'+idx">
            <input type="hidden" :name="`form_data[section_e_inventory][urgent_needs][${idx}]`" :value="need">
        </template>
    </form>

    {{-- ═══════════════════════════════════════════════════════════
         AUTHENTIC A4 CLINICAL PAPER DOCUMENT SHEET (MODE A)
         ═══════════════════════════════════════════════════════════ --}}
    <div class="paper-container" x-show="viewMode === 'paper'">
        <!-- Mobile Pan Guidance Notice -->
        <div class="sm:hidden flex items-center justify-between px-3.5 py-2 mb-3 rounded-md bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] text-[11px] text-[var(--hc-text-muted)] font-medium">
            <span class="flex items-center gap-1.5">
                <i data-lucide="move-horizontal" class="w-3.5 h-3.5"></i>
                <span>Original A4 Clinical Sheet — Pan horizontally to view & fill</span>
            </span>
            <span class="font-mono text-[10px] font-bold uppercase bg-[var(--color-primary)] text-white px-1.5 py-0.5 rounded">A4 Sheet</span>
        </div>

        <div class="paper-sheet">
            {{-- Official Hospital Letterhead Header --}}
            <div class="paper-header">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        @if(!empty($hospital->logo_path))
                            <img src="{{ asset($hospital->logo_path) }}" alt="Logo" class="h-12 w-auto object-contain">
                        @else
                            <div class="w-10 h-10 rounded bg-[var(--color-primary)] text-white flex items-center justify-center font-bold text-base">
                                {{ substr($hospital->hospital_name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <h1 class="text-lg font-black uppercase tracking-wider text-[var(--hc-text)]">
                                {{ $hospital->hospital_name }}
                            </h1>
                            <p class="text-xs font-semibold text-[var(--hc-text-muted)]">
                                PHARMACY DAILY OPERATIONS & RECONCILIATION SUMMARY SHEET
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="inline-block px-2.5 py-1 bg-[var(--hc-text)] text-[var(--hc-surface)] font-mono font-bold text-xs rounded">
                            FORM ID: ANL-PHM-01
                        </div>
                        <div class="text-[10px] text-[var(--hc-text-muted)] font-mono mt-1">REV 2026.1 / ISO-9001 COMPLIANT</div>
                    </div>
                </div>

                {{-- Metadata Bar --}}
                <div class="paper-metadata-bar">
                    <div>
                        <span class="font-bold text-[var(--hc-text)]">REPORT DATE:</span>
                        <input type="date" x-model="reportDate" class="paper-input ml-2 font-mono text-xs">
                    </div>
                    <div>
                        <span class="font-bold text-[var(--hc-text)]">SHIFT:</span>
                        <select x-model="shift" @change="changeShift($event.target.value)" class="paper-input ml-2 font-semibold text-xs">
                            <option value="Morning">Morning Shift (08:00 - 14:00)</option>
                            <option value="Afternoon">Noon Shift (14:00 - 21:00)</option>
                            <option value="Night">Night Shift (21:00 - 08:00)</option>
                            <option value="Full Day">Full Day / 24-Hour Consolidated</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-bold text-[var(--hc-text)]">DUTY PHARMACIST:</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 font-bold text-xs text-slate-900 dark:text-white border border-slate-300 dark:border-slate-700">
                            <i data-lucide="user-check" class="w-3 h-3 text-emerald-500"></i>
                            {{ $displayLeadStaff }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- SECTION A: Daily Prescription Volume & Dispensing Summary --}}
            <div class="paper-section">
                <div class="paper-section-header">
                    <span class="text-xs font-bold uppercase tracking-wider text-[var(--hc-text)]"
                          x-text="$store.app.t('phm_sec_a', 'SECTION A — DAILY PRESCRIPTION VOLUME & DISPENSING SUMMARY')">
                        SECTION A — DAILY PRESCRIPTION VOLUME & DISPENSING SUMMARY
                    </span>
                </div>

                <table class="hc-table">
                    <thead>
                        <tr>
                            <th class="text-left" x-text="$store.app.locale === 'so' ? 'Qaybta Dawada / Isha' : 'Prescription Category / Source'">Prescription Category / Source</th>
                            <th class="text-center w-32">Rx / Patients Count</th>
                            <th class="text-center w-32">Items Dispensed</th>
                            <th class="text-left">Operational Notes & Partner Sources</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="font-semibold text-xs text-[var(--hc-text)]">
                                1. Hospital Prescriptions (OPD / IPD / ER)
                            </td>
                            <td class="text-center">
                                <input type="number" min="0" x-model.number="insideRx" class="paper-input text-center font-bold w-24">
                            </td>
                            <td class="text-center">
                                <input type="number" min="0" x-model.number="insideItems" class="paper-input text-center font-bold w-24">
                            </td>
                            <td>
                                <input type="text" x-model="insideNotes" placeholder="e.g. Ward 2 bulk order included" class="paper-input w-full text-xs">
                            </td>
                        </tr>
                        <tr>
                            <td class="font-semibold text-xs text-[var(--hc-text)]">
                                2. Outside Prescriptions (Walk-in / External)
                            </td>
                            <td class="text-center">
                                <input type="number" min="0" x-model.number="outsideRx" class="paper-input text-center font-bold w-24">
                            </td>
                            <td class="text-center">
                                <input type="number" min="0" x-model.number="outsideItems" class="paper-input text-center font-bold w-24">
                            </td>
                            <td class="text-xs text-[var(--hc-text-muted)]">
                                Direct retail & external clinics
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-[var(--hc-surface-alt)] font-bold text-xs">
                            <td class="text-[var(--hc-text)] uppercase" x-text="$store.app.locale === 'so' ? 'WADARTA DAAWOOYINKA LA BIXIYAY' : 'TOTAL SERVED PRESCRIPTIONS'">TOTAL SERVED PRESCRIPTIONS</td>
                            <td class="text-center font-mono text-sm text-[var(--color-primary)]" x-text="totalServedRx"></td>
                            <td class="text-center font-mono text-sm text-[var(--color-primary)]" x-text="totalItemsDispensed"></td>
                            <td class="text-xs font-mono text-[var(--hc-text)]">
                                Est. Value: $<input type="number" step="0.01" min="0" x-model.number="dispensedValue" class="paper-input w-28 text-right font-bold inline ml-1">
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- SECTION B: Prescribing Doctors Reconciliation --}}
            <div class="paper-section">
                <div class="paper-section-header">
                    <span class="text-xs font-bold uppercase tracking-wider text-[var(--hc-text)]"
                          x-text="$store.app.t('phm_sec_b', 'SECTION B — PRESCRIBING DOCTOR PERFORMANCE & RECONCILIATION')">
                        SECTION B — PRESCRIBING DOCTOR PERFORMANCE & RECONCILIATION
                    </span>
                </div>

                <table class="hc-table">
                    <thead>
                        <tr>
                            <th x-text="$store.app.locale === 'so' ? 'Magaca Dhakhtarka' : 'Prescribing Doctor'">Prescribing Doctor</th>
                            <th class="text-center w-28">Served Rx</th>
                            <th class="text-center w-28">Unserved Rx</th>
                            <th class="text-center w-28">Total Rx</th>
                            <th class="text-right w-28">Fill Rate %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(doc, idx) in doctors" :key="'doc-row-'+idx">
                            <tr>
                                <td class="font-medium text-xs text-[var(--hc-text)]" x-text="doc.doctor"></td>
                                <td class="text-center">
                                    <input type="number" min="0" x-model.number="doc.served_rx" class="paper-input text-center font-bold w-20">
                                </td>
                                <td class="text-center">
                                    <input type="number" min="0" x-model.number="doc.unserved_rx" class="paper-input text-center font-bold w-20">
                                </td>
                                <td class="text-center font-mono text-xs font-semibold text-[var(--hc-text)]"
                                    x-text="parseInt(doc.served_rx || 0) + parseInt(doc.unserved_rx || 0)"></td>
                                <td class="text-right font-mono text-xs font-semibold text-[var(--hc-text-muted)]"
                                    x-text="((parseInt(doc.served_rx || 0) + parseInt(doc.unserved_rx || 0)) > 0 ? ((parseInt(doc.served_rx || 0) / (parseInt(doc.served_rx || 0) + parseInt(doc.unserved_rx || 0))) * 100).toFixed(0) : 0) + '%'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- SECTION C: Root Causes for Unserved Prescriptions --}}
            <div class="paper-section">
                <div class="paper-section-header">
                    <span class="text-xs font-bold uppercase tracking-wider text-[var(--hc-text)]"
                          x-text="$store.app.t('phm_sec_c', 'SECTION C — ROOT CAUSES FOR UNSERVED PRESCRIPTIONS')">
                        SECTION C — ROOT CAUSES FOR UNSERVED PRESCRIPTIONS
                    </span>
                    <span class="text-xs font-mono font-bold text-[var(--hc-danger)]" x-text="totalUnservedPatients + ' Unserved Total'"></span>
                </div>

                <div class="p-3 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    <div class="p-2.5 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)]">
                        <span class="text-[11px] font-bold block text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? '1. Kharashka Sare' : '1. Price Barrier'">1. Price Barrier</span>
                        <div class="text-[10px] text-[var(--hc-text-muted)] mb-1" x-text="$store.app.locale === 'so' ? 'Awood dhaqaale' : 'Cost barrier'">Cost barrier</div>
                        <input type="number" min="0" x-model.number="price" class="paper-input w-full text-center font-bold">
                    </div>
                    <div class="p-2.5 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)]" style="border-color: var(--hc-danger);">
                        <span class="text-[11px] font-bold block text-[var(--hc-danger)]" x-text="$store.app.locale === 'so' ? '2. Daawo La\'aan' : '2. Complete Stockout'">2. Complete Stockout</span>
                        <div class="text-[10px] text-[var(--hc-text-muted)] mb-1" x-text="$store.app.locale === 'so' ? 'Bakhaarka ma yaalo' : 'Zero inventory'">Zero inventory</div>
                        <input type="number" min="0" x-model.number="stockout" class="paper-input w-full text-center font-bold text-[var(--hc-danger)]">
                    </div>
                    <div class="p-2.5 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)]">
                        <span class="text-[11px] font-bold block text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? '3. Brand Ma Yaalo' : '3. Brand Missing'">3. Brand Missing</span>
                        <div class="text-[10px] text-[var(--hc-text-muted)] mb-1" x-text="$store.app.locale === 'so' ? 'Nooc kale la rabay' : 'Brand unavailable'">Brand unavailable</div>
                        <input type="number" min="0" x-model.number="brandUnavailable" class="paper-input w-full text-center font-bold">
                    </div>
                    <div class="p-2.5 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)]">
                        <span class="text-[11px] font-bold block text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? '4. Ma Imaan' : '4. Did Not Present'">4. Did Not Present</span>
                        <div class="text-[10px] text-[var(--hc-text-muted)] mb-1" x-text="$store.app.locale === 'so' ? 'Farmashiyo ma imaan' : 'Patient left'">Patient left</div>
                        <input type="number" min="0" x-model.number="didNotPresent" class="paper-input w-full text-center font-bold">
                    </div>
                    <div class="p-2.5 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)]">
                        <span class="text-[11px] font-bold block text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? '5. Aan La Aqoon' : '5. Unknown Reason'">5. Unknown Reason</span>
                        <div class="text-[10px] text-[var(--hc-text-muted)] mb-1" x-text="$store.app.locale === 'so' ? 'Lama cayimin' : 'Unspecified'">Unspecified</div>
                        <input type="number" min="0" x-model.number="unknown" class="paper-input w-full text-center font-bold">
                    </div>
                    <div class="p-2.5 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)]">
                        <span class="text-[11px] font-bold block text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? '6. Sabab Kale' : '6. Other Reason'">6. Other Reason</span>
                        <div class="text-[10px] text-[var(--hc-text-muted)] mb-1" x-text="$store.app.locale === 'so' ? 'Faahfaahin kale' : 'Other cause'">Other cause</div>
                        <input type="number" min="0" x-model.number="otherReason" class="paper-input w-full text-center font-bold">
                    </div>
                </div>
            </div>

            {{-- SECTION D: Refunds, Debt Taken & Paid Debt --}}
            <div class="paper-section">
                <div class="paper-section-header">
                    <span class="text-xs font-bold uppercase tracking-wider text-[var(--hc-text)]"
                          x-text="$store.app.t('phm_sec_d', 'SECTION D — FINANCIAL OPERATIONS, REFUNDS & DEBT EXPOSURE')">
                        SECTION D — FINANCIAL OPERATIONS, REFUNDS & DEBT EXPOSURE
                    </span>
                    <span class="text-xs font-mono font-bold text-[var(--hc-purple)]" x-text="'Net Credit: $' + parseFloat(netDebtExposure || 0).toFixed(2)"></span>
                </div>

                <table class="hc-table">
                    <thead>
                        <tr>
                            <th x-text="$store.app.locale === 'so' ? 'Nooca Lacag-Bixinta' : 'Transaction Type'">Transaction Type</th>
                            <th class="text-center w-28">Cases Count</th>
                            <th class="text-right w-36">Total Amount ($)</th>
                            <th>Audit Notes & Itemized Breakdown</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-xs text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? 'Lacag khaldantay (Khalad Xisaabeed)' : 'Refund — Billing / Entry Error'">Refund — Billing / Entry Error</td>
                            <td class="text-center"><input type="number" min="0" x-model.number="refundErrorCases" class="paper-input w-20 text-center"></td>
                            <td class="text-right"><input type="number" step="0.01" min="0" x-model.number="refundErrorAmount" class="paper-input w-24 text-right"> $</td>
                            <td class="text-xs text-[var(--hc-text-muted)]">Cashier reversal register matched</td>
                        </tr>
                        <tr>
                            <td class="text-xs text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? 'Dawo lasoo celiyay' : 'Refund — Returned Medication'">Refund — Returned Medication</td>
                            <td class="text-center"><input type="number" min="0" x-model.number="refundReturnCases" class="paper-input w-20 text-center"></td>
                            <td class="text-right"><input type="number" step="0.01" min="0" x-model.number="refundReturnAmount" class="paper-input w-24 text-right"> $</td>
                            <td class="text-xs text-[var(--hc-text-muted)]">Restocked unopened blisters to shelf</td>
                        </tr>
                        <tr>
                            <td class="font-bold text-xs text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? 'Deyn La Qaatay' : 'Debt Extended (Authorized Credit)'">Debt Extended (Authorized Credit)</td>
                            <td class="text-center"><input type="number" min="0" x-model.number="debtTakenCases" class="paper-input w-20 text-center font-bold"></td>
                            <td class="text-right"><input type="number" step="0.01" min="0" x-model.number="debtTakenAmount" class="paper-input w-24 text-right font-bold"> $</td>
                            <td class="text-xs text-[var(--hc-text-muted)]">Authorized emergency debt guarantee on file</td>
                        </tr>
                        <tr>
                            <td class="font-bold text-xs text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? 'Deyn La Soo Celiyay' : 'Debt Recovered (Credit Paid)'">Debt Recovered (Credit Paid)</td>
                            <td class="text-center"><input type="number" min="0" x-model.number="debtPaidCases" class="paper-input w-20 text-center font-bold"></td>
                            <td class="text-right"><input type="number" step="0.01" min="0" x-model.number="debtPaidAmount" class="paper-input w-24 text-right font-bold text-[var(--hc-success)]"> $</td>
                            <td class="text-xs text-[var(--hc-text-muted)]">Previous credit collected and deposited to cash</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- SECTION E: Stockout Reorder Alerts & Near-Expiry Medication --}}
            <div class="paper-section">
                <div class="paper-section-header">
                    <span class="text-xs font-bold uppercase tracking-wider text-[var(--hc-text)]"
                          x-text="$store.app.t('phm_sec_e', 'SECTION E — STOCKOUTS & NEAR-EXPIRY LOGISTICS ALERTS')">
                        SECTION E — STOCKOUTS & NEAR-EXPIRY LOGISTICS ALERTS
                    </span>
                </div>

                <div class="p-3 grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="p-3 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)]">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-[var(--hc-border)]">
                            <span class="text-xs font-bold text-[var(--hc-danger)]">Critical Stockouts</span>
                            <button type="button" @click="addStockout()" class="btn btn-ghost btn-xs text-[10px]" title="Type custom drug">+ Custom</button>
                        </div>
                        <div class="mb-2">
                            <select x-model="stockoutQuickSelect" @change="addQuickStockout()" class="hc-input hc-select text-[11px] w-full py-1">
                                <option value="">+ Select Out-of-Stock Drug...</option>
                                @foreach(($departmentForm?->dropdown_options['stockout_drugs'] ?? []) as $stockoutDrug)
                                    <option value="{{ $stockoutDrug }}">{{ $stockoutDrug }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1">
                            <template x-for="(drug, idx) in outOfStockDrugs" :key="'out-item-'+idx">
                                <div class="flex items-center justify-between text-xs py-1 px-1.5 rounded bg-[var(--hc-surface-alt)]">
                                    <span class="font-medium text-[var(--hc-text)]" x-text="drug"></span>
                                    <button type="button" @click="removeStockout(idx)" class="text-[var(--hc-danger)] font-bold text-xs hover:underline">&times;</button>
                                </div>
                            </template>
                            <div x-show="outOfStockDrugs.length === 0" class="text-xs text-[var(--hc-text-muted)] italic">No stockouts reported.</div>
                        </div>
                    </div>

                    <div class="p-3 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)]">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-[var(--hc-border)]">
                            <span class="text-xs font-bold text-[var(--hc-warning)]">Near-Expiry (&lt;90 Days)</span>
                            <button type="button" @click="addNearExpiry()" class="btn btn-ghost btn-xs text-[10px]" title="Type custom drug">+ Custom</button>
                        </div>
                        <div class="mb-2">
                            <select x-model="nearExpiryQuickSelect" @change="addQuickNearExpiry()" class="hc-input hc-select text-[11px] w-full py-1">
                                <option value="">+ Select Near-Expiry Drug...</option>
                                @foreach(($departmentForm?->dropdown_options['near_expiry_drugs'] ?? []) as $nearDrug)
                                    <option value="{{ $nearDrug }}">{{ $nearDrug }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1">
                            <template x-for="(drug, idx) in nearExpiryDrugs" :key="'near-item-'+idx">
                                <div class="flex items-center justify-between text-xs py-1 px-1.5 rounded bg-[var(--hc-surface-alt)]">
                                    <span class="font-medium text-[var(--hc-text)]" x-text="drug"></span>
                                    <button type="button" @click="removeNearExpiry(idx)" class="text-[var(--hc-danger)] font-bold text-xs hover:underline">&times;</button>
                                </div>
                            </template>
                            <div x-show="nearExpiryDrugs.length === 0" class="text-xs text-[var(--hc-text-muted)] italic">No near-expiry drugs logged.</div>
                        </div>
                    </div>

                    <div class="p-3 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)]">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-[var(--hc-border)]">
                            <span class="text-xs font-bold text-[var(--color-primary)]">Urgent Logistics Needs</span>
                            <button type="button" @click="addUrgentNeed()" class="btn btn-secondary btn-xs">+ Add</button>
                        </div>
                        <div class="space-y-1">
                            <template x-for="(need, idx) in urgentNeeds" :key="'need-item-'+idx">
                                <div class="flex items-center justify-between text-xs py-1 px-1.5 rounded bg-[var(--hc-surface-alt)]">
                                    <span class="font-medium text-[var(--hc-text)]" x-text="need"></span>
                                    <button type="button" @click="removeUrgentNeed(idx)" class="text-[var(--hc-danger)] font-bold text-xs hover:underline">&times;</button>
                                </div>
                            </template>
                            <div x-show="urgentNeeds.length === 0" class="text-xs text-[var(--hc-text-muted)] italic">No urgent needs logged.</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Clinical Verification & Stamp Block --}}
            <div class="pt-4 mt-6 border-t-2 border-[var(--hc-text)] flex flex-col sm:flex-row items-center justify-between text-xs text-[var(--hc-text-muted)]">
                <div>
                    <div>Dispensary Verified By: <strong class="text-[var(--hc-text)]" x-text="pharmacist"></strong></div>
                    <div class="text-[11px] font-mono mt-0.5">Submission Timestamp: {{ now()->format('Y-m-d H:i') }} UTC</div>
                </div>
                <div class="text-right mt-2 sm:mt-0">
                    <div class="text-[var(--hc-success)] font-mono font-bold">OFFICIAL CLINICAL RECORD</div>
                    <div class="text-[10px]">Hospital Analytics Governance Framework</div>
                </div>
            </div>

        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         MODE B: MODERN GUIDED FORM VIEW (Fast Step-by-Step Entry)
         ═══════════════════════════════════════════════════════════ --}}
    <div x-show="viewMode === 'guided'" x-cloak class="space-y-6"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0">

        <!-- Mode B Banner & Quick Overview -->
        <div class="hc-card bg-[var(--hc-surface)] border border-[var(--hc-border)] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-md bg-[var(--color-primary)] text-white flex items-center justify-center font-bold text-sm shrink-0">
                    <i data-lucide="layout-list" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-[var(--hc-text)]">
                        Mode B — Fast Step-by-Step Dispensary Entry
                    </h2>
                    <p class="text-xs text-[var(--hc-text-muted)]">
                        Touch-optimized layout with rapid tally steppers &bull; Fully synchronized with Mode A (A4 Sheet)
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <div class="hc-badge hc-badge-success">
                    <span class="font-bold">Total Served:</span>
                    <span class="font-mono font-bold" x-text="totalServedRx + ' Rx'"></span>
                </div>
                <div class="hc-badge hc-badge-warning">
                    <span class="font-bold">Fill Rate:</span>
                    <span class="font-mono font-bold" x-text="prescriptionFillRate + '%'"></span>
                </div>
                <button type="button" @click="viewMode = 'paper'" class="btn btn-secondary btn-xs">
                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                    <span>Switch to A4 View</span>
                </button>
            </div>
        </div>

        <!-- Section A: Prescription Volume & Dispensing -->
        <div class="hc-card space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[var(--hc-border)]">
                <div class="flex items-center gap-2">
                    <span class="hc-badge hc-badge-brand">SECTION A</span>
                    <h3 class="text-sm font-bold text-[var(--hc-text)]">Daily Prescription Volume &amp; Dispensing Summary</h3>
                </div>
                <div class="text-xs text-[var(--hc-text-muted)] font-mono">
                    Total Items: <strong class="text-[var(--hc-text)]" x-text="totalItemsDispensed"></strong>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Inside Rx Card -->
                <div class="p-4 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-xs text-[var(--hc-text)]">1. Inside Prescriptions (OPD / IPD / ER)</span>
                        <span class="status-dot status-dot-brand"></span>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="hc-label">Rx Count</label>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="insideRx = Math.max(0, parseInt(insideRx || 0) - 1)" class="w-7 h-7 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white transition">-</button>
                                <input type="number" min="0" x-model.number="insideRx" class="hc-input text-center font-bold text-sm w-20">
                                <button type="button" @click="insideRx = parseInt(insideRx || 0) + 1" class="w-7 h-7 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white transition">+</button>
                            </div>
                        </div>
                        <div>
                            <label class="hc-label">Items Dispensed</label>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="insideItems = Math.max(0, parseInt(insideItems || 0) - 1)" class="w-7 h-7 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white transition">-</button>
                                <input type="number" min="0" x-model.number="insideItems" class="hc-input text-center font-bold text-sm w-20">
                                <button type="button" @click="insideItems = parseInt(insideItems || 0) + 1" class="w-7 h-7 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white transition">+</button>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="hc-label">Operational Notes</label>
                        <input type="text" x-model="insideNotes" class="hc-input text-xs" placeholder="e.g. Surge in pediatric antibiotics">
                    </div>
                </div>

                <!-- Outside Rx Card -->
                <div class="p-4 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-xs text-[var(--hc-text)]">2. Outside Prescriptions (Community / Walk-In)</span>
                        <span class="status-dot status-dot-success"></span>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="hc-label">Rx Count</label>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="outsideRx = Math.max(0, parseInt(outsideRx || 0) - 1)" class="w-7 h-7 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white transition">-</button>
                                <input type="number" min="0" x-model.number="outsideRx" class="hc-input text-center font-bold text-sm w-20">
                                <button type="button" @click="outsideRx = parseInt(outsideRx || 0) + 1" class="w-7 h-7 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white transition">+</button>
                            </div>
                        </div>
                        <div>
                            <label class="hc-label">Items Dispensed</label>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="outsideItems = Math.max(0, parseInt(outsideItems || 0) - 1)" class="w-7 h-7 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white transition">-</button>
                                <input type="number" min="0" x-model.number="outsideItems" class="hc-input text-center font-bold text-sm w-20">
                                <button type="button" @click="outsideItems = parseInt(outsideItems || 0) + 1" class="w-7 h-7 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white transition">+</button>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="hc-label">Total Dispensed Value ($ USD)</label>
                        <input type="number" step="0.01" min="0" x-model.number="dispensedValue" class="hc-input text-xs font-bold" placeholder="0.00">
                    </div>
                </div>
            </div>
        </div>

        <!-- Section B: Doctors Breakdown -->
        <div class="hc-card space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[var(--hc-border)]">
                <div class="flex items-center gap-2">
                    <span class="hc-badge hc-badge-purple">SECTION B</span>
                    <h3 class="text-sm font-bold text-[var(--hc-text)]">Prescribing Doctors Reconciliation</h3>
                </div>
                <div class="text-xs text-[var(--hc-text-muted)]">
                    Audit served vs unserved by clinician
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <template x-for="(doc, idx) in doctors" :key="'guided-doc-'+idx">
                    <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-2">
                        <div class="flex items-center justify-between pb-1 border-b border-[var(--hc-border)]">
                            <span class="font-bold text-xs text-[var(--hc-text)]" x-text="doc.doctor"></span>
                            <span class="text-[10px] font-mono text-[var(--hc-text-muted)]" x-text="'#' + (idx+1)"></span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[var(--hc-success)] font-semibold">Served:</span>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="doc.served_rx = Math.max(0, parseInt(doc.served_rx || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">-</button>
                                <input type="number" min="0" x-model.number="doc.served_rx" class="hc-input text-center font-bold text-xs w-14 py-0.5">
                                <button type="button" @click="doc.served_rx = parseInt(doc.served_rx || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">+</button>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[var(--hc-danger)] font-semibold">Unserved:</span>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="doc.unserved_rx = Math.max(0, parseInt(doc.unserved_rx || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-danger)] hover:text-white">-</button>
                                <input type="number" min="0" x-model.number="doc.unserved_rx" class="hc-input text-center font-bold text-xs w-14 py-0.5">
                                <button type="button" @click="doc.unserved_rx = parseInt(doc.unserved_rx || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-danger)] hover:text-white">+</button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Section C: Root Causes -->
        <div class="hc-card space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[var(--hc-border)]">
                <div class="flex items-center gap-2">
                    <span class="hc-badge hc-badge-warning">SECTION C</span>
                    <h3 class="text-sm font-bold text-[var(--hc-text)]" x-text="$store.app.t('phm_sec_c', 'Root Causes for Unserved Prescriptions')">Root Causes for Unserved Prescriptions</h3>
                </div>
                <div class="text-xs font-bold text-[var(--hc-warning)]">
                    Total: <span x-text="totalUnservedPatients"></span> unserved
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-1.5 text-center">
                    <span class="text-xs font-bold block text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? '1. Kharashka Sare' : '1. Price Barrier'">1. Price Barrier</span>
                    <span class="text-[10px] text-[var(--hc-text-muted)] block" x-text="$store.app.locale === 'so' ? 'Awood dhaqaale' : 'Cost barrier'">Cost barrier</span>
                    <div class="flex items-center justify-center gap-1 mt-1">
                        <button type="button" @click="price = Math.max(0, parseInt(price || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="price" class="hc-input text-center font-bold text-xs w-12 py-0.5">
                        <button type="button" @click="price = parseInt(price || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">+</button>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-1.5 text-center">
                    <span class="text-xs font-bold block text-[var(--hc-danger)]" x-text="$store.app.locale === 'so' ? '2. Daawo La\'aan' : '2. Complete Stockout'">2. Complete Stockout</span>
                    <span class="text-[10px] text-[var(--hc-text-muted)] block" x-text="$store.app.locale === 'so' ? 'Bakhaarka ma yaalo' : 'Zero inventory'">Zero inventory</span>
                    <div class="flex items-center justify-center gap-1 mt-1">
                        <button type="button" @click="stockout = Math.max(0, parseInt(stockout || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-danger)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="stockout" class="hc-input text-center font-bold text-xs w-12 py-0.5">
                        <button type="button" @click="stockout = parseInt(stockout || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-danger)] hover:text-white">+</button>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-1.5 text-center">
                    <span class="text-xs font-bold block text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? '3. Brand Ma Yaalo' : '3. Brand Missing'">3. Brand Missing</span>
                    <span class="text-[10px] text-[var(--hc-text-muted)] block" x-text="$store.app.locale === 'so' ? 'Nooc kale la rabay' : 'Brand unavailable'">Brand unavailable</span>
                    <div class="flex items-center justify-center gap-1 mt-1">
                        <button type="button" @click="brandUnavailable = Math.max(0, parseInt(brandUnavailable || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="brandUnavailable" class="hc-input text-center font-bold text-xs w-12 py-0.5">
                        <button type="button" @click="brandUnavailable = parseInt(brandUnavailable || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">+</button>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-1.5 text-center">
                    <span class="text-xs font-bold block text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? '4. Ma Imaan' : '4. Did Not Present'">4. Did Not Present</span>
                    <span class="text-[10px] text-[var(--hc-text-muted)] block" x-text="$store.app.locale === 'so' ? 'Farmashiyo ma imaan' : 'Patient left'">Patient left</span>
                    <div class="flex items-center justify-center gap-1 mt-1">
                        <button type="button" @click="didNotPresent = Math.max(0, parseInt(didNotPresent || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="didNotPresent" class="hc-input text-center font-bold text-xs w-12 py-0.5">
                        <button type="button" @click="didNotPresent = parseInt(didNotPresent || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">+</button>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-1.5 text-center">
                    <span class="text-xs font-bold block text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? '5. Aan La Aqoon' : '5. Unknown Reason'">5. Unknown Reason</span>
                    <span class="text-[10px] text-[var(--hc-text-muted)] block" x-text="$store.app.locale === 'so' ? 'Lama cayimin' : 'Unspecified'">Unspecified</span>
                    <div class="flex items-center justify-center gap-1 mt-1">
                        <button type="button" @click="unknown = Math.max(0, parseInt(unknown || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="unknown" class="hc-input text-center font-bold text-xs w-12 py-0.5">
                        <button type="button" @click="unknown = parseInt(unknown || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">+</button>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-1.5 text-center">
                    <span class="text-xs font-bold block text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? '6. Sabab Kale' : '6. Other Reason'">6. Other Reason</span>
                    <span class="text-[10px] text-[var(--hc-text-muted)] block" x-text="$store.app.locale === 'so' ? 'Faahfaahin kale' : 'Other cause'">Other cause</span>
                    <div class="flex items-center justify-center gap-1 mt-1">
                        <button type="button" @click="otherReason = Math.max(0, parseInt(otherReason || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="otherReason" class="hc-input text-center font-bold text-xs w-12 py-0.5">
                        <button type="button" @click="otherReason = parseInt(otherReason || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">+</button>
                    </div>
                </div>
            </div>

            <div x-show="otherReason > 0" class="pt-2">
                <label class="hc-label">Sabab Kale (Other Reason Details)</label>
                <input type="text" x-model="otherDetails" class="hc-input text-xs" placeholder="Describe specific reason for unserved prescription...">
            </div>
        </div>

        <!-- Section D: Financials, Refunds & Debt -->
        <div class="hc-card space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[var(--hc-border)]">
                <div class="flex items-center gap-2">
                    <span class="hc-badge hc-badge-purple">SECTION D</span>
                    <h3 class="text-sm font-bold text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? 'Lacag Celin & Deyn' : 'Financials & Credit Exposure'">Financials &amp; Credit Exposure</h3>
                </div>
                <div class="text-xs font-mono font-bold text-[var(--hc-purple)]" x-text="'Net Credit: $' + parseFloat(netDebtExposure || 0).toFixed(2)"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-2">
                    <span class="text-xs font-bold block text-[var(--hc-text)]">Refund (Billing Error)</span>
                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[var(--hc-text-muted)]">Cases:</span>
                            <input type="number" min="0" x-model.number="refundErrorCases" class="hc-input text-center font-bold text-xs w-20 py-0.5">
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[var(--hc-text-muted)]">Amount ($):</span>
                            <input type="number" step="0.01" min="0" x-model.number="refundErrorAmount" class="hc-input text-right font-bold text-xs w-24 py-0.5">
                        </div>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-2">
                    <span class="text-xs font-bold block text-[var(--hc-text)]">Refund (Returned Meds)</span>
                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[var(--hc-text-muted)]">Cases:</span>
                            <input type="number" min="0" x-model.number="refundReturnCases" class="hc-input text-center font-bold text-xs w-20 py-0.5">
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[var(--hc-text-muted)]">Amount ($):</span>
                            <input type="number" step="0.01" min="0" x-model.number="refundReturnAmount" class="hc-input text-right font-bold text-xs w-24 py-0.5">
                        </div>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-2">
                    <span class="text-xs font-bold block text-[var(--hc-danger)]">Debt Taken (Deyn La Qaatay)</span>
                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[var(--hc-text-muted)]">Cases:</span>
                            <input type="number" min="0" x-model.number="debtTakenCases" class="hc-input text-center font-bold text-xs w-20 py-0.5">
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[var(--hc-text-muted)]">Amount ($):</span>
                            <input type="number" step="0.01" min="0" x-model.number="debtTakenAmount" class="hc-input text-right font-bold text-xs w-24 py-0.5">
                        </div>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-2">
                    <span class="text-xs font-bold block text-[var(--hc-success)]">Debt Paid (Deyn La Bixiyay)</span>
                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[var(--hc-text-muted)]">Cases:</span>
                            <input type="number" min="0" x-model.number="debtPaidCases" class="hc-input text-center font-bold text-xs w-20 py-0.5">
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[var(--hc-text-muted)]">Amount ($):</span>
                            <input type="number" step="0.01" min="0" x-model.number="debtPaidAmount" class="hc-input text-right font-bold text-xs w-24 py-0.5">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section E: Stockouts & Near-Expiry Reorders -->
        <div class="hc-card space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[var(--hc-border)]">
                <div class="flex items-center gap-2">
                    <span class="hc-badge hc-badge-danger">SECTION E</span>
                    <h3 class="text-sm font-bold text-[var(--hc-text)]">Stockout Reorder Alerts &amp; Near-Expiry Medication</h3>
                </div>
                <div class="text-xs text-[var(--hc-text-muted)]">
                    Inventory logistics alerts
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Critical Stockouts -->
                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-2">
                    <div class="flex items-center justify-between pb-1 border-b border-[var(--hc-border)]">
                        <span class="text-xs font-bold text-[var(--hc-danger)]">Critical Stockouts</span>
                        <button type="button" @click="addStockout()" class="btn btn-ghost btn-xs text-[10px]" title="Type custom drug">+ Custom</button>
                    </div>
                    <div>
                        <select x-model="stockoutQuickSelect" @change="addQuickStockout()" class="hc-input hc-select text-[11px] w-full py-1">
                            <option value="">+ Select Out-of-Stock Drug...</option>
                            @foreach(($departmentForm?->dropdown_options['stockout_drugs'] ?? []) as $stockoutDrug)
                                <option value="{{ $stockoutDrug }}">{{ $stockoutDrug }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <template x-for="(drug, idx) in outOfStockDrugs" :key="'guided-out-'+idx">
                            <div class="flex items-center justify-between text-xs py-1 px-2 rounded bg-[var(--hc-surface)] border border-[var(--hc-border)]">
                                <span class="font-medium text-[var(--hc-text)]" x-text="drug"></span>
                                <button type="button" @click="removeStockout(idx)" class="text-[var(--hc-danger)] font-bold hover:underline">&times;</button>
                            </div>
                        </template>
                        <div x-show="outOfStockDrugs.length === 0" class="text-xs text-[var(--hc-text-muted)] italic">No stockouts reported.</div>
                    </div>
                </div>

                <!-- Near Expiry -->
                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-2">
                    <div class="flex items-center justify-between pb-1 border-b border-[var(--hc-border)]">
                        <span class="text-xs font-bold text-[var(--hc-warning)]">Near Expiry (&lt;90 Days)</span>
                        <button type="button" @click="addNearExpiry()" class="btn btn-ghost btn-xs text-[10px]" title="Type custom drug">+ Custom</button>
                    </div>
                    <div>
                        <select x-model="nearExpiryQuickSelect" @change="addQuickNearExpiry()" class="hc-input hc-select text-[11px] w-full py-1">
                            <option value="">+ Select Near-Expiry Drug...</option>
                            @foreach(($departmentForm?->dropdown_options['near_expiry_drugs'] ?? []) as $nearDrug)
                                <option value="{{ $nearDrug }}">{{ $nearDrug }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <template x-for="(drug, idx) in nearExpiryDrugs" :key="'guided-near-'+idx">
                            <div class="flex items-center justify-between text-xs py-1 px-2 rounded bg-[var(--hc-surface)] border border-[var(--hc-border)]">
                                <span class="font-medium text-[var(--hc-text)]" x-text="drug"></span>
                                <button type="button" @click="removeNearExpiry(idx)" class="text-[var(--hc-danger)] font-bold hover:underline">&times;</button>
                            </div>
                        </template>
                        <div x-show="nearExpiryDrugs.length === 0" class="text-xs text-[var(--hc-text-muted)] italic">No near-expiry drugs logged.</div>
                    </div>
                </div>

                <!-- Urgent Needs -->
                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-2">
                    <div class="flex items-center justify-between pb-1 border-b border-[var(--hc-border)]">
                        <span class="text-xs font-bold text-[var(--color-primary)]">Urgent Logistics Needs</span>
                        <button type="button" @click="addUrgentNeed()" class="btn btn-secondary btn-xs">+ Add</button>
                    </div>
                    <div class="space-y-1">
                        <template x-for="(need, idx) in urgentNeeds" :key="'guided-need-'+idx">
                            <div class="flex items-center justify-between text-xs py-1 px-2 rounded bg-[var(--hc-surface)] border border-[var(--hc-border)]">
                                <span class="font-medium text-[var(--hc-text)]" x-text="need"></span>
                                <button type="button" @click="removeUrgentNeed(idx)" class="text-[var(--hc-danger)] font-bold hover:underline">&times;</button>
                            </div>
                        </template>
                        <div x-show="urgentNeeds.length === 0" class="text-xs text-[var(--hc-text-muted)] italic">No urgent needs logged.</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ═══════════════════════════════════════════════════════════
         A4 CLINICAL REPORT PREVIEW & VERIFICATION APPROVAL MODAL
         ═══════════════════════════════════════════════════════════ --}}
    <div x-show="openA4Preview" x-cloak
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-2 sm:p-4 z-50 text-left"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        <div @click.away="openA4Preview = false"
             class="bg-[var(--hc-surface)] rounded-xl max-w-5xl w-full border border-[var(--hc-border)] shadow-2xl flex flex-col max-h-[92vh]">

            <!-- Modal Header -->
            <div class="px-6 py-4 bg-[var(--hc-surface)] border-b border-[var(--hc-border)] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-md bg-[var(--hc-success)] text-white flex items-center justify-center font-bold shrink-0">
                        <i data-lucide="file-check-2" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="hc-badge hc-badge-success font-mono">FORM: ANL-PHM-01</span>
                            <span class="text-xs text-[var(--hc-text-muted)] font-mono">&bull; Date: <strong class="text-[var(--hc-text)]" x-text="reportDate"></strong></span>
                            <span class="text-xs text-[var(--hc-text-muted)] font-mono">&bull; Shift: <strong class="text-[var(--hc-text)]" x-text="shift"></strong></span>
                        </div>
                        <h2 class="text-base font-bold text-[var(--hc-text)] mt-0.5">
                            Official A4 Report Verification &amp; Final Approval
                        </h2>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="openA4Preview = false" class="btn btn-secondary btn-sm">
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                        <span>Back to Edit</span>
                    </button>
                    <button type="button" @click="submitAndApprove()" class="btn btn-primary btn-sm font-bold">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span>Approve &amp; Submit Official Report</span>
                    </button>
                    <button type="button" @click="openA4Preview = false" class="btn btn-ghost btn-sm text-[var(--hc-text-muted)]">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body: Real-time A4 Paper Document Preview -->
            <div class="p-4 sm:p-6 overflow-y-auto bg-[var(--hc-surface-alt)] flex-1">
                <div class="paper-container mb-4">
                    <div class="paper-sheet shadow-md">
                        
                        {{-- Hospital Header --}}
                        <div class="paper-header">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded bg-[var(--color-primary)] text-white flex items-center justify-center font-bold text-base">
                                        {{ substr($hospital->hospital_name ?? 'K', 0, 1) }}
                                    </div>
                                    <div>
                                        <h1 class="text-lg font-black uppercase tracking-wider text-[var(--hc-text)]">
                                            {{ $hospital->hospital_name ?? 'KAAFI HOSPITAL' }}
                                        </h1>
                                        <p class="text-xs font-semibold text-[var(--hc-text-muted)]">
                                            PHARMACY DAILY OPERATIONS &amp; RECONCILIATION SUMMARY SHEET
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="inline-block px-2.5 py-1 bg-[var(--hc-text)] text-[var(--hc-surface)] font-mono font-bold text-xs rounded">
                                        FORM ID: ANL-PHM-01
                                    </div>
                                    <div class="text-[10px] text-[var(--hc-text-muted)] font-mono mt-1">REV 2026.1 / AUDIT READY</div>
                                </div>
                            </div>

                            <div class="paper-metadata-bar">
                                <div><span class="font-bold text-[var(--hc-text)]">REPORT DATE:</span> <span class="font-mono text-xs font-bold" x-text="reportDate"></span></div>
                                <div><span class="font-bold text-[var(--hc-text)]">SHIFT:</span> <span class="font-semibold text-xs" x-text="shift"></span></div>
                                <div><span class="font-bold text-[var(--hc-text)]">DUTY PHARMACIST:</span> <span class="font-semibold text-xs" x-text="pharmacist"></span></div>
                            </div>
                        </div>

                        <!-- Section A Preview Summary -->
                        <div class="p-4 border-b border-[var(--hc-border)] space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs uppercase tracking-wider text-[var(--hc-text)]">SECTION A: PRESCRIPTION VOLUME &amp; DISPENSARY</span>
                                <span class="font-mono text-xs font-bold text-[var(--color-primary)]" x-text="'Fill Rate: ' + prescriptionFillRate + '%'"></span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)]">
                                    <span class="text-[10px] text-[var(--hc-text-muted)] block">Total Served Rx</span>
                                    <span class="font-bold text-sm font-mono text-[var(--hc-success)]" x-text="totalServedRx"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)]">
                                    <span class="text-[10px] text-[var(--hc-text-muted)] block">Items Dispensed</span>
                                    <span class="font-bold text-sm font-mono text-[var(--hc-text)]" x-text="totalItemsDispensed"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)]">
                                    <span class="text-[10px] text-[var(--hc-text-muted)] block">Dispensed Value</span>
                                    <span class="font-bold text-sm font-mono text-[var(--hc-text)]" x-text="'$' + parseFloat(dispensedValue || 0).toFixed(2)"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)]">
                                    <span class="text-[10px] text-[var(--hc-text-muted)] block">Unserved Patients</span>
                                    <span class="font-bold text-sm font-mono text-[var(--hc-danger)]" x-text="totalUnservedPatients"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Section B & C Preview Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x border-b border-[var(--hc-border)]">
                            <!-- Doctors Balancing -->
                            <div class="p-4 space-y-2">
                                <span class="font-bold text-xs uppercase tracking-wider text-[var(--hc-text)]">SECTION B: DOCTOR BALANCING</span>
                                <div class="space-y-1 text-xs">
                                    <template x-for="doc in doctors" :key="'prev-doc-'+doc.doctor">
                                        <div class="flex items-center justify-between py-0.5 border-b border-dashed border-[var(--hc-border)]">
                                            <span class="font-medium text-[var(--hc-text)]" x-text="doc.doctor"></span>
                                            <div class="font-mono text-xs">
                                                <span class="text-[var(--hc-success)] font-bold" x-text="doc.served_rx + ' served'"></span>
                                                <span class="text-[var(--hc-text-muted)]">/</span>
                                                <span class="text-[var(--hc-danger)]" x-text="doc.unserved_rx + ' unserved'"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Root Causes -->
                            <div class="p-4 space-y-2">
                                <span class="font-bold text-xs uppercase tracking-wider text-[var(--hc-text)]">SECTION C: UNSERVED ROOT CAUSES</span>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div class="p-2 rounded bg-[var(--hc-surface-alt)] flex items-center justify-between">
                                        <span>Stockout:</span>
                                        <span class="font-bold font-mono text-[var(--hc-danger)]" x-text="stockout"></span>
                                    </div>
                                    <div class="p-2 rounded bg-[var(--hc-surface-alt)] flex items-center justify-between">
                                        <span>Price / Financial:</span>
                                        <span class="font-bold font-mono text-[var(--hc-warning)]" x-text="price"></span>
                                    </div>
                                    <div class="p-2 rounded bg-[var(--hc-surface-alt)] flex items-center justify-between">
                                        <span>Brand Unavailable:</span>
                                        <span class="font-bold font-mono" x-text="brandUnavailable"></span>
                                    </div>
                                    <div class="p-2 rounded bg-[var(--hc-surface-alt)] flex items-center justify-between">
                                        <span>Did Not Present:</span>
                                        <span class="font-bold font-mono" x-text="didNotPresent"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section D Financials -->
                        <div class="p-4 border-b border-[var(--hc-border)] flex flex-wrap items-center justify-between gap-4 text-xs">
                            <span class="font-bold uppercase tracking-wider text-[var(--hc-text)]">SECTION D: FINANCIAL RECONCILIATION</span>
                            <div class="flex items-center gap-4 font-mono">
                                <span>Refunds: <strong class="text-[var(--hc-danger)]" x-text="'$' + (parseFloat(refundErrorAmount||0) + parseFloat(refundReturnAmount||0)).toFixed(2)"></strong></span>
                                <span>Debt Taken: <strong class="text-[var(--hc-warning)]" x-text="'$' + parseFloat(debtTakenAmount||0).toFixed(2)"></strong></span>
                                <span>Debt Paid: <strong class="text-[var(--hc-success)]" x-text="'$' + parseFloat(debtPaidAmount||0).toFixed(2)"></strong></span>
                                <span>Net Exposure: <strong class="text-[var(--hc-purple)]" x-text="'$' + parseFloat(netDebtExposure||0).toFixed(2)"></strong></span>
                            </div>
                        </div>

                        <!-- Certification Footer -->
                        <div class="p-4 bg-[var(--hc-surface-alt)] flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-[var(--hc-text-muted)]">
                            <div class="flex items-center gap-2">
                                <i data-lucide="shield-check" class="w-4 h-4 text-[var(--hc-success)]"></i>
                                <span>Official Clinical Document &bull; Verified by Duty Pharmacist</span>
                            </div>
                            <span class="font-mono text-[11px] font-bold text-[var(--color-primary)]">Ready for Official Audit Submission</span>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Sticky Modal Footer -->
            <div class="px-6 py-3.5 bg-[var(--hc-surface)] border-t border-[var(--hc-border)] flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0 text-xs">
                <div class="flex items-center gap-2 text-[var(--hc-text-muted)]">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[var(--hc-success)]"></i>
                    <span>All changes are automatically saved. Submitting finalizes this report for clinical audit.</span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="openA4Preview = false" class="btn btn-secondary">
                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                        <span>Edit Form</span>
                    </button>
                    <button type="button" @click="saveDraft()" class="btn btn-secondary">
                        <i data-lucide="save" class="w-3.5 h-3.5"></i>
                        <span>Save as Draft</span>
                    </button>
                    <button type="button" @click="submitAndApprove()" class="btn btn-primary font-bold">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span>Approve &amp; Submit Official Report</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
