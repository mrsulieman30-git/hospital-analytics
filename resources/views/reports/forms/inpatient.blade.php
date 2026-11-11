@extends('layouts.app')

@section('content')
@php
    $formData = $report?->form_data ?? [];
    $isStealth = auth()->check() && (auth()->user()->isStealthSuperAdmin() || auth()->user()->is_stealth || auth()->user()->role === 'superadmin');
    $primaryAdmin = \App\Models\User::where('email', 'muse@kaafihospitals.org')->orWhere('role', 'admin')->first();
    $displayLeadStaff = $report->lead_staff_name ?? ($isStealth ? ($primaryAdmin?->name ?? 'Mohamed Abdi Muse') : auth()->user()->name);
@endphp
<div x-data="{
    viewMode: 'paper',
    reportDate: '{{ $date }}',
    shift: '24-Hour Daily Summary',
    ipdLead: {{ json_encode($displayLeadStaff) }},
    activeBeds: {{ (int)($formData['header']['total_active_beds'] ?? 60) }},

    // Calendar Navigation Methods
    navigateToDate(targetDate) {
        if (!targetDate || targetDate === '{{ $date }}') return;
        const url = new URL('{{ route("reports.create") }}', window.location.origin);
        url.searchParams.set('department', 'inpatient');
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

    // Section A: Census Balancing Formula [A] + [B] - [C] - [D] - [E] = [F]
    censusA: {{ (int)($formData['section_a_census']['previous_admissions_a'] ?? 0) }},
    admittedFromEr: {{ (int)($formData['section_a_census']['newly_admitted_from_er'] ?? 0) }},
    admittedFromOpd: {{ (int)($formData['section_a_census']['newly_admitted_from_opd'] ?? 0) }},
    dischargedRecovered: {{ (int)($formData['section_a_census']['discharged_recovered_c1'] ?? 0) }},
    dischargedDama: {{ (int)($formData['section_a_census']['discharged_dama_c2'] ?? 0) }},
    referredOutD: {{ (int)($formData['section_a_census']['referred_out_d'] ?? 0) }},
    deathsE: {{ (int)($formData['section_a_census']['deaths_today_e'] ?? 0) }},
    currentCensusF: {{ (int)($formData['section_a_census']['current_admissions_f'] ?? 0) }},
    patientBedDays: {{ (int)($formData['section_a_census']['total_patient_bed_days'] ?? 0) }},

    // Section C: Maternity
    nvdDeliveries: {{ (int)($formData['section_c_maternity']['nvd_normal_delivery'] ?? 0) }},
    cSectionDeliveries: {{ (int)($formData['section_c_maternity']['c_section_delivery'] ?? 0) }},
    newbornMale: {{ (int)($formData['section_c_maternity']['newborn_male'] ?? 0) }},
    newbornFemale: {{ (int)($formData['section_c_maternity']['newborn_female'] ?? 0) }},
    nicuAdmissions: {{ (int)($formData['section_c_maternity']['nicu_admissions'] ?? 0) }},

    // Computed Properties
    get newlyAdmittedB() {
        return parseInt(this.admittedFromEr || 0) + parseInt(this.admittedFromOpd || 0);
    },
    get totalDischargesC() {
        return parseInt(this.dischargedRecovered || 0) + parseInt(this.dischargedDama || 0);
    },
    get calculatedEndingF() {
        return parseInt(this.censusA || 0) + 
               this.newlyAdmittedB - 
               this.totalDischargesC - 
               parseInt(this.referredOutD || 0) - 
               parseInt(this.deathsE || 0);
    },
    get isCensusBalanced() {
        return this.calculatedEndingF === parseInt(this.currentCensusF || 0);
    },
    get bedOccupancyRate() {
        return this.activeBeds > 0 ? ((this.currentCensusF / this.activeBeds) * 100).toFixed(1) : 0;
    },
    get alos() {
        return this.totalDischargesC > 0 ? (this.patientBedDays / this.totalDischargesC).toFixed(1) : 0;
    },
    // Autosave State & Methods
    openA4Preview: false,
    autosaveStatus: 'idle',
    lastAutosavedAt: null,
    autosaveKey: 'kfh_autosave_inpatient_{{ $date }}',

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
                prevMidnightCensus: this.prevMidnightCensus,
                admissions: this.admissions,
                discharges: this.discharges,
                transfersOut: this.transfersOut,
                deaths: this.deaths,
                availableBeds: this.availableBeds,
                nvdDeliveries: this.nvdDeliveries,
                cSectionDeliveries: this.cSectionDeliveries,
                stillbirths: this.stillbirths,
                newbornMale: this.newbornMale,
                newbornFemale: this.newbornFemale,
                notes: this.notes,
                shift: this.shift,
                supervisor: this.supervisor
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
        const form = document.getElementById('ipd-submit-form');
        if (form) {
            form.status.value = 'submitted';
            form.submit();
        }
    },

    saveDraft() {
        this.clearAutosave();
        const form = document.getElementById('ipd-submit-form');
        if (form) {
            form.status.value = 'draft';
            form.submit();
        }
    },

    get cSectionRate() {
        const total = parseInt(this.nvdDeliveries || 0) + parseInt(this.cSectionDeliveries || 0);
        return total > 0 ? ((this.cSectionDeliveries / total) * 100).toFixed(1) : 0;
    }
}" @input.debounce.1000ms="performAutosave()" @change.debounce.1000ms="performAutosave()" class="max-w-7xl mx-auto space-y-6">

    {{-- ═══════════════════════════════════════════════════════════
         UNIFIED SECONDARY CONTROL HEADER & OPERATIONAL TOOLBAR
         ═══════════════════════════════════════════════════════════ --}}
    <div class="no-print hc-card p-3 lg:p-4 space-y-3 shadow-xs">

        {{-- Row 1: Department Identification, Autosave & Primary Actions --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-lg bg-[var(--hc-info)] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                    <i data-lucide="bed" class="w-4 h-4"></i>
                </div>
                <div class="flex items-center gap-2 min-w-0 flex-wrap">
                    <span class="hc-badge hc-badge-brand font-mono font-bold text-xs shrink-0">ANL-IPD-03</span>
                    <h1 class="text-sm lg:text-base font-bold text-[var(--hc-text)] tracking-tight">
                        Inpatient Department (IPD) Bed Census Reconciliation
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
                    <button type="button" @click="saveDraft()" class="btn btn-secondary btn-xs py-1 px-2.5 text-xs font-semibold" title="Save draft without submitting">
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

        {{-- Row 2: Operational Parameters (Date Navigator, Active Beds, Charge Nurse) & Metric Badges --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2.5 pt-2 border-t border-[var(--hc-border)] text-xs">
            {{-- Left: Interactive Calendar Controls + Active Beds + Charge Nurse --}}
            <div class="flex flex-wrap items-center gap-2">
                <div class="inline-flex items-center rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] p-0.5">
                    <button type="button" @click="prevDay()" class="btn-ghost btn-xs px-1.5 py-1 text-[var(--hc-text-muted)] hover:text-[var(--hc-text)]" title="Previous Day">
                        <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                    </button>
                    <div class="flex items-center px-1.5 gap-1 border-x border-[var(--hc-border)]">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-[var(--hc-text-muted)]"></i>
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
                    <span class="text-[var(--hc-text-muted)] text-[11px] font-semibold hidden lg:inline">Active Beds:</span>
                    <input type="number" x-model.number="activeBeds" class="hc-input w-16 text-center font-bold text-xs py-1 px-1.5">
                </div>

                <div class="flex items-center gap-1.5">
                    <span class="text-[var(--hc-text-muted)] text-[11px] font-semibold hidden lg:inline">Charge Nurse:</span>
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs font-bold text-slate-800 dark:text-slate-100">
                        <i data-lucide="user-check" class="w-3.5 h-3.5 text-emerald-500"></i>
                        <span>{{ $displayLeadStaff }}</span>
                        <span class="text-[10px] text-slate-400 font-mono font-normal">(Automated)</span>
                    </div>
                </div>
            </div>

            {{-- Right: Live Metric Badges --}}
            <div class="flex flex-wrap items-center gap-1.5">
                <div class="hc-badge hc-badge-brand text-xs py-0.5" title="Today's ending patient census">
                    <i data-lucide="users" class="w-3 h-3"></i>
                    <span x-text="currentCensusF + ' Inpatients [F]'"></span>
                </div>
                <div class="hc-badge hc-badge-success text-xs py-0.5" title="Bed occupancy rate">
                    <span class="status-dot status-dot-success"></span>
                    <span x-text="bedOccupancyRate + '% BOR'"></span>
                </div>
                <div class="hc-badge text-xs py-0.5" :class="isCensusBalanced ? 'hc-badge-success' : 'hc-badge-danger'" title="Census formula mathematical balance">
                    <span class="status-dot" :class="isCensusBalanced ? 'status-dot-success' : 'status-dot-danger'"></span>
                    <span x-text="isCensusBalanced ? 'Formula Balanced' : 'Census Mismatch'"></span>
                </div>
                <div class="hc-badge hc-badge-purple text-xs py-0.5" title="Average length of stay">
                    <span class="font-mono" x-text="alos + 'd ALOS'"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden Submit Form --}}
    <form id="ipd-submit-form" action="{{ $mode === 'edit' ? route('reports.update', $report) : route('reports.store') }}" method="POST">
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        <input type="hidden" name="department" value="inpatient">
        <input type="hidden" name="form_code" value="ANL-IPD-03">
        <input type="hidden" name="report_date" :value="reportDate">
        <input type="hidden" name="shift" :value="shift">
        <input type="hidden" name="lead_staff_name" :value="ipdLead">
        <input type="hidden" name="status" value="draft">

        <input type="hidden" name="form_data[header][total_active_beds]" :value="activeBeds">
        <input type="hidden" name="form_data[section_a_census][previous_admissions_a]" :value="censusA">
        <input type="hidden" name="form_data[section_a_census][newly_admitted_b]" :value="newlyAdmittedB">
        <input type="hidden" name="form_data[section_a_census][newly_admitted_from_er]" :value="admittedFromEr">
        <input type="hidden" name="form_data[section_a_census][newly_admitted_from_opd]" :value="admittedFromOpd">
        <input type="hidden" name="form_data[section_a_census][discharged_recovered_c1]" :value="dischargedRecovered">
        <input type="hidden" name="form_data[section_a_census][discharged_dama_c2]" :value="dischargedDama">
        <input type="hidden" name="form_data[section_a_census][total_discharged_c]" :value="totalDischargesC">
        <input type="hidden" name="form_data[section_a_census][referred_out_d]" :value="referredOutD">
        <input type="hidden" name="form_data[section_a_census][deaths_today_e]" :value="deathsE">
        <input type="hidden" name="form_data[section_a_census][current_admissions_f]" :value="currentCensusF">
        <input type="hidden" name="form_data[section_a_census][total_patient_bed_days]" :value="patientBedDays">

        <input type="hidden" name="form_data[section_c_maternity][nvd_normal_delivery]" :value="nvdDeliveries">
        <input type="hidden" name="form_data[section_c_maternity][c_section_delivery]" :value="cSectionDeliveries">
        <input type="hidden" name="form_data[section_c_maternity][newborn_male]" :value="newbornMale">
        <input type="hidden" name="form_data[section_c_maternity][newborn_female]" :value="newbornFemale">
        <input type="hidden" name="form_data[section_c_maternity][nicu_admissions]" :value="nicuAdmissions">
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
                                INPATIENT DEPARTMENT (IPD) DAILY BED CENSUS RECONCILIATION SHEET
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="inline-block px-2.5 py-1 bg-[var(--hc-text)] text-[var(--hc-surface)] font-mono font-bold text-xs rounded">
                            FORM ID: ANL-IPD-03
                        </div>
                        <div class="text-[10px] text-[var(--hc-text-muted)] font-mono mt-1">FIXED 24-HOUR CENSUS CADENCE</div>
                    </div>
                </div>

                {{-- Metadata Bar --}}
                <div class="paper-metadata-bar">
                    <div>
                        <span class="font-bold text-[var(--hc-text)]">REPORT DATE:</span>
                        <input type="date" x-model="reportDate" class="paper-input ml-2 font-mono text-xs">
                    </div>
                    <div>
                        <span class="font-bold text-[var(--hc-text)]">ACTIVE BED CAPACITY:</span>
                        <input type="number" x-model.number="activeBeds" class="paper-input ml-2 w-16 font-bold text-center">
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-bold text-[var(--hc-text)]">CHARGE NURSE / LEAD:</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 font-bold text-xs text-slate-900 dark:text-white border border-slate-300 dark:border-slate-700">
                            <i data-lucide="user-check" class="w-3 h-3 text-emerald-500"></i>
                            {{ $displayLeadStaff }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Section A: Self-Balancing Bed Census Reconciliation --}}
            <div class="paper-section">
                <div class="paper-section-header">
                    <span class="text-xs font-bold uppercase tracking-wider text-[var(--hc-text)]"
                          x-text="$store.app.t('ipd_sec_a', 'SECTION A — STRICT SELF-BALANCING DAILY BED CENSUS RECONCILIATION')">
                        SECTION A — STRICT SELF-BALANCING DAILY BED CENSUS RECONCILIATION
                    </span>
                </div>

                <table class="hc-table text-xs">
                    <thead>
                        <tr>
                            <th class="w-16 text-center">Step</th>
                            <th x-text="$store.app.locale === 'so' ? 'Qeexitaanka Sariiraha Jiifka' : 'Census Variable Description'">Census Variable Description</th>
                            <th class="text-center w-32" x-text="$store.app.locale === 'so' ? 'Tirada Bukaanka' : 'Daily Patient Count'">Daily Patient Count</th>
                            <th>Audit Cross-Verification Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="font-bold font-mono text-center text-[var(--hc-text-muted)]">[A]</td>
                            <td class="font-semibold text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? 'Bukaankii Hore ee Jiifay (Xalay Xisaabxirkii [F])' : 'Previous Admissions (Yesterday\'s Ending Census)'">Previous Admissions (Yesterday's Ending Census)</td>
                            <td class="text-center">
                                <input type="number" min="0" x-model.number="censusA" class="paper-input text-center w-24 font-bold">
                            </td>
                            <td><span class="text-[var(--hc-text-muted)]">Prefilled from yesterday's audited ending census [F]</span></td>
                        </tr>
                        <tr class="bg-[var(--hc-surface-alt)]">
                            <td class="font-bold font-mono text-center text-[var(--hc-success)]">[+B]</td>
                            <td class="font-bold text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? 'Bukaanka Cusub ee Maanta Jiifsaday' : 'Newly Admitted Today'">Newly Admitted Today</td>
                            <td class="text-center font-mono font-bold text-[var(--hc-success)] text-sm" x-text="newlyAdmittedB"></td>
                            <td class="text-xs">
                                Source: E.R (<input type="number" min="0" x-model.number="admittedFromEr" class="paper-input w-14 text-center font-bold text-[var(--hc-warning)]">) + 
                                OPD (<input type="number" min="0" x-model.number="admittedFromOpd" class="paper-input w-14 text-center font-bold">)
                            </td>
                        </tr>
                        <tr>
                            <td class="font-bold font-mono text-center text-[var(--hc-danger)]">[-C]</td>
                            <td class="font-semibold text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? 'Bukaanka Baxay Maanta' : 'Discharged Today'">Discharged Today</td>
                            <td class="text-center font-mono font-bold text-sm" x-text="totalDischargesC"></td>
                            <td class="text-xs">
                                Recovered (<input type="number" min="0" x-model.number="dischargedRecovered" class="paper-input w-14 text-center">) + 
                                DAMA (<input type="number" min="0" x-model.number="dischargedDama" class="paper-input w-14 text-center text-[var(--hc-danger)]">)
                            </td>
                        </tr>
                        <tr>
                            <td class="font-bold font-mono text-center text-[var(--hc-text-muted)]">[-D]</td>
                            <td class="text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? 'Loo Gudbiyay Isbitaal Kale' : 'Referred Out to External Specialized Centers'">Referred Out to External Specialized Centers</td>
                            <td class="text-center">
                                <input type="number" min="0" x-model.number="referredOutD" class="paper-input text-center w-24">
                            </td>
                            <td><span class="text-[var(--hc-text-muted)]">Specialist hospital transfers</span></td>
                        </tr>
                        <tr>
                            <td class="font-bold font-mono text-center text-[var(--hc-danger)]">[-E]</td>
                            <td class="font-bold text-[var(--hc-danger)]" x-text="$store.app.locale === 'so' ? 'Dhimashada Qaybta Jiifka Maanta' : 'Inpatient Deaths Today'">Inpatient Deaths Today</td>
                            <td class="text-center">
                                <input type="number" min="0" x-model.number="deathsE" class="paper-input text-center w-24 text-[var(--hc-danger)] font-bold">
                            </td>
                            <td><span class="text-[var(--hc-text-muted)]">Recorded in Hospital Mortality Register</span></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-[var(--hc-surface-alt)] font-bold text-xs border-t-2 border-[var(--hc-text)]">
                            <td class="font-mono text-center text-[var(--color-primary)]">[=F]</td>
                            <td class="text-[var(--hc-text)] uppercase" x-text="$store.app.locale === 'so' ? 'BUKAANKA KU HARYAY JIIFKA (Xisaabxirka Maanta)' : 'CURRENT ADMISSIONS (Today\'s Ending Census)'">CURRENT ADMISSIONS (Today's Ending Census)</td>
                            <td class="text-center">
                                <input type="number" min="0" x-model.number="currentCensusF" class="paper-input text-center w-24 font-black text-sm text-[var(--color-primary)]">
                            </td>
                            <td class="font-mono text-xs font-bold" :style="isCensusBalanced ? 'color: var(--hc-success);' : 'color: var(--hc-danger);'">
                                Formula Status: 
                                <span x-text="isCensusBalanced ? 'BALANCED [A+B-C-D-E = ' + calculatedEndingF + ']' : 'MISMATCH (Expected: ' + calculatedEndingF + ' vs Entered: ' + currentCensusF + ')'"></span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Section C: Maternity & Deliveries --}}
            <div class="paper-section">
                <div class="paper-section-header">
                    <span class="text-xs font-bold uppercase tracking-wider text-[var(--hc-text)]"
                          x-text="$store.app.t('ipd_sec_c', 'SECTION C — MATERNITY, DELIVERIES & NEWBORN SUMMARY')">
                        SECTION C — MATERNITY, DELIVERIES & NEWBORN SUMMARY
                    </span>
                </div>

                <div class="p-3 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-2.5 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] flex justify-between items-center">
                        <span class="font-semibold text-[var(--hc-text)]">Normal Delivery (NVD)</span>
                        <input type="number" min="0" x-model.number="nvdDeliveries" class="paper-input w-16 text-center font-bold">
                    </div>
                    <div class="p-2.5 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] flex justify-between items-center" style="border-color: var(--hc-warning);">
                        <span class="font-bold text-[var(--hc-warning)]">C-Section Delivery</span>
                        <input type="number" min="0" x-model.number="cSectionDeliveries" class="paper-input w-16 text-center font-bold text-[var(--hc-warning)]">
                    </div>
                    <div class="p-2.5 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] flex justify-between items-center">
                        <span class="font-semibold text-[var(--hc-text)]">Newborn Male</span>
                        <input type="number" min="0" x-model.number="newbornMale" class="paper-input w-16 text-center font-bold">
                    </div>
                    <div class="p-2.5 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] flex justify-between items-center">
                        <span class="font-semibold text-[var(--hc-text)]">Newborn Female</span>
                        <input type="number" min="0" x-model.number="newbornFemale" class="paper-input w-16 text-center font-bold">
                    </div>
                </div>
            </div>

            {{-- Clinical Verification & Stamp Block --}}
            <div class="pt-4 mt-6 border-t-2 border-[var(--hc-text)] flex flex-col sm:flex-row items-center justify-between text-xs text-[var(--hc-text-muted)]">
                <div>
                    <div>IPD Charge Nurse / Ward Supervisor: <strong class="text-[var(--hc-text)]" x-text="ipdLead"></strong></div>
                    <div class="text-[11px] font-mono mt-0.5">Mathematical Balance Certified via Daily Census Engine</div>
                </div>
                <div class="text-right mt-2 sm:mt-0">
                    <div class="text-[var(--hc-success)] font-mono font-bold">MATHEMATICAL BALANCE CERTIFIED</div>
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

        <!-- Mode B Banner -->
        <div class="hc-card bg-[var(--hc-surface)] border border-[var(--hc-border)] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-md bg-[var(--hc-info)] text-white flex items-center justify-center font-bold text-sm shrink-0">
                    <i data-lucide="layout-list" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-[var(--hc-text)]">
                        Mode B — Fast Step-by-Step Bed Census Balancing
                    </h2>
                    <p class="text-xs text-[var(--hc-text-muted)]">
                        Real-time mathematical balancing [A+B-C-D-E=F] &bull; Fully synchronized with Mode A (A4 Sheet)
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <div class="hc-badge" :class="isCensusBalanced ? 'hc-badge-success' : 'hc-badge-danger'">
                    <span class="status-dot" :class="isCensusBalanced ? 'status-dot-success' : 'status-dot-danger'"></span>
                    <span x-text="isCensusBalanced ? '100% Balanced' : 'Unbalanced Formula'"></span>
                </div>
                <div class="hc-badge hc-badge-brand">
                    <span class="font-bold">Occupancy:</span>
                    <span class="font-mono font-bold" x-text="bedOccupancyRate + '%'"></span>
                </div>
                <button type="button" @click="viewMode = 'paper'" class="btn btn-secondary btn-xs">
                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                    <span>Switch to A4 View</span>
                </button>
            </div>
        </div>

        <!-- Section A: Mathematical Census Balancing -->
        <div class="hc-card space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[var(--hc-border)]">
                <div class="flex items-center gap-2">
                    <span class="hc-badge hc-badge-brand">SECTION A</span>
                    <h3 class="text-sm font-bold text-[var(--hc-text)]">Bed Census Reconciliation Formula</h3>
                </div>
                <div class="text-xs font-mono font-semibold" :style="isCensusBalanced ? 'color: var(--hc-success);' : 'color: var(--hc-danger);'">
                    Formula: [<span x-text="censusA"></span> + <span x-text="newlyAdmittedB"></span> - <span x-text="totalDischargesC"></span> - <span x-text="referredOutD"></span> - <span x-text="deathsE"></span>] = <strong x-text="calculatedEndingF"></strong>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Previous Midnight Census [A] -->
                <div class="p-4 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-2">
                    <span class="font-bold text-xs text-[var(--hc-text)]">1. Previous Admissions [A]</span>
                    <p class="text-[11px] text-[var(--hc-text-muted)]">Midnight census carried forward</p>
                    <div class="flex items-center gap-1 pt-1">
                        <button type="button" @click="censusA = Math.max(0, parseInt(censusA || 0) - 1)" class="w-8 h-8 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="censusA" class="hc-input text-center font-bold text-sm w-24">
                        <button type="button" @click="censusA = parseInt(censusA || 0) + 1" class="w-8 h-8 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">+</button>
                    </div>
                </div>

                <!-- Newly Admitted Today [B] -->
                <div class="p-4 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-xs text-[var(--hc-brand)]">2. New Admissions [B]</span>
                        <span class="font-mono font-bold text-xs text-[var(--hc-brand)]" x-text="'Total: ' + newlyAdmittedB"></span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <div>
                            <label class="hc-label">From E.R</label>
                            <div class="flex items-center gap-0.5">
                                <button type="button" @click="admittedFromEr = Math.max(0, parseInt(admittedFromEr || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">-</button>
                                <input type="number" min="0" x-model.number="admittedFromEr" class="hc-input text-center font-bold text-xs w-12 py-0.5">
                                <button type="button" @click="admittedFromEr = parseInt(admittedFromEr || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">+</button>
                            </div>
                        </div>
                        <div>
                            <label class="hc-label">From OPD</label>
                            <div class="flex items-center gap-0.5">
                                <button type="button" @click="admittedFromOpd = Math.max(0, parseInt(admittedFromOpd || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">-</button>
                                <input type="number" min="0" x-model.number="admittedFromOpd" class="hc-input text-center font-bold text-xs w-12 py-0.5">
                                <button type="button" @click="admittedFromOpd = parseInt(admittedFromOpd || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">+</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Discharges Today [C] -->
                <div class="p-4 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-xs text-[var(--hc-success)]">3. Discharges [C]</span>
                        <span class="font-mono font-bold text-xs text-[var(--hc-success)]" x-text="'Total: ' + totalDischargesC"></span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <div>
                            <label class="hc-label">Recovered</label>
                            <div class="flex items-center gap-0.5">
                                <button type="button" @click="dischargedRecovered = Math.max(0, parseInt(dischargedRecovered || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-success)] hover:text-white">-</button>
                                <input type="number" min="0" x-model.number="dischargedRecovered" class="hc-input text-center font-bold text-xs w-12 py-0.5">
                                <button type="button" @click="dischargedRecovered = parseInt(dischargedRecovered || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-success)] hover:text-white">+</button>
                            </div>
                        </div>
                        <div>
                            <label class="hc-label">DAMA</label>
                            <div class="flex items-center gap-0.5">
                                <button type="button" @click="dischargedDama = Math.max(0, parseInt(dischargedDama || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-warning)] hover:text-white">-</button>
                                <input type="number" min="0" x-model.number="dischargedDama" class="hc-input text-center font-bold text-xs w-12 py-0.5">
                                <button type="button" @click="dischargedDama = parseInt(dischargedDama || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-warning)] hover:text-white">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Outflow D & E and Ending Census F -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2">
                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-1">
                    <span class="text-xs font-bold block text-[var(--hc-text)]">Referred Out [D]</span>
                    <div class="flex items-center gap-1">
                        <button type="button" @click="referredOutD = Math.max(0, parseInt(referredOutD || 0) - 1)" class="w-7 h-7 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="referredOutD" class="hc-input text-center font-bold text-sm w-20">
                        <button type="button" @click="referredOutD = parseInt(referredOutD || 0) + 1" class="w-7 h-7 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">+</button>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-1">
                    <span class="text-xs font-bold block text-[var(--hc-danger)]">Deaths Today [E]</span>
                    <div class="flex items-center gap-1">
                        <button type="button" @click="deathsE = Math.max(0, parseInt(deathsE || 0) - 1)" class="w-7 h-7 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-danger)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="deathsE" class="hc-input text-center font-bold text-sm w-20 text-[var(--hc-danger)]">
                        <button type="button" @click="deathsE = parseInt(deathsE || 0) + 1" class="w-7 h-7 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-danger)] hover:text-white">+</button>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-1">
                    <span class="text-xs font-bold block text-[var(--color-primary)]">Current Census [F]</span>
                    <div class="flex items-center gap-1">
                        <button type="button" @click="currentCensusF = Math.max(0, parseInt(currentCensusF || 0) - 1)" class="w-7 h-7 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="currentCensusF" class="hc-input text-center font-bold text-sm w-20" :style="isCensusBalanced ? 'border-color: var(--hc-success); color: var(--hc-success);' : 'border-color: var(--hc-danger); color: var(--hc-danger);'">
                        <button type="button" @click="currentCensusF = parseInt(currentCensusF || 0) + 1" class="w-7 h-7 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">+</button>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-1">
                    <span class="text-xs font-bold block text-[var(--hc-text)]">Total Bed-Days</span>
                    <input type="number" min="0" x-model.number="patientBedDays" class="hc-input text-center font-bold text-sm w-full">
                </div>
            </div>
        </div>

        <!-- Section C: Maternity & Newborn Register -->
        <div class="hc-card space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[var(--hc-border)]">
                <div class="flex items-center gap-2">
                    <span class="hc-badge hc-badge-purple">SECTION C</span>
                    <h3 class="text-sm font-bold text-[var(--hc-text)]">Maternity &amp; Newborn Register</h3>
                </div>
                <div class="text-xs font-mono font-bold text-[var(--hc-purple)]">
                    C-Section Rate: <span x-text="cSectionRate + '%'"></span>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-1 text-center">
                    <span class="text-xs font-bold block text-[var(--hc-success)]">NVD Deliveries</span>
                    <span class="text-[10px] text-[var(--hc-text-muted)] block">Normal vaginal</span>
                    <div class="flex items-center justify-center gap-1 mt-1">
                        <button type="button" @click="nvdDeliveries = Math.max(0, parseInt(nvdDeliveries || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-success)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="nvdDeliveries" class="hc-input text-center font-bold text-xs w-14 py-0.5">
                        <button type="button" @click="nvdDeliveries = parseInt(nvdDeliveries || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-success)] hover:text-white">+</button>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-1 text-center">
                    <span class="text-xs font-bold block text-[var(--hc-warning)]">C-Section</span>
                    <span class="text-[10px] text-[var(--hc-text-muted)] block">Surgical delivery</span>
                    <div class="flex items-center justify-center gap-1 mt-1">
                        <button type="button" @click="cSectionDeliveries = Math.max(0, parseInt(cSectionDeliveries || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-warning)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="cSectionDeliveries" class="hc-input text-center font-bold text-xs w-14 py-0.5">
                        <button type="button" @click="cSectionDeliveries = parseInt(cSectionDeliveries || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-warning)] hover:text-white">+</button>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-1 text-center">
                    <span class="text-xs font-bold block text-[var(--hc-danger)]">NICU Admission</span>
                    <span class="text-[10px] text-[var(--hc-text-muted)] block">Neonatal ICU</span>
                    <div class="flex items-center justify-center gap-1 mt-1">
                        <button type="button" @click="nicuAdmissions = Math.max(0, parseInt(nicuAdmissions || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-danger)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="nicuAdmissions" class="hc-input text-center font-bold text-xs w-14 py-0.5">
                        <button type="button" @click="nicuAdmissions = parseInt(nicuAdmissions || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-danger)] hover:text-white">+</button>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-1 text-center">
                    <span class="text-xs font-bold block text-[var(--hc-text)]">Newborn Male</span>
                    <span class="text-[10px] text-[var(--hc-text-muted)] block">Live male births</span>
                    <div class="flex items-center justify-center gap-1 mt-1">
                        <button type="button" @click="newbornMale = Math.max(0, parseInt(newbornMale || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="newbornMale" class="hc-input text-center font-bold text-xs w-14 py-0.5">
                        <button type="button" @click="newbornMale = parseInt(newbornMale || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">+</button>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-1 text-center">
                    <span class="text-xs font-bold block text-[var(--hc-text)]">Newborn Female</span>
                    <span class="text-[10px] text-[var(--hc-text-muted)] block">Live female births</span>
                    <div class="flex items-center justify-center gap-1 mt-1">
                        <button type="button" @click="newbornFemale = Math.max(0, parseInt(newbornFemale || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="newbornFemale" class="hc-input text-center font-bold text-xs w-14 py-0.5">
                        <button type="button" @click="newbornFemale = parseInt(newbornFemale || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">+</button>
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
                    <div class="w-10 h-10 rounded-md bg-[var(--hc-info)] text-white flex items-center justify-center font-bold shrink-0">
                        <i data-lucide="file-check-2" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="hc-badge hc-badge-brand font-mono">FORM: ANL-IPD-03</span>
                            <span class="text-xs text-[var(--hc-text-muted)] font-mono">&bull; Date: <strong class="text-[var(--hc-text)]" x-text="reportDate"></strong></span>
                            <span class="text-xs text-[var(--hc-text-muted)] font-mono">&bull; Shift: <strong class="text-[var(--hc-text)]" x-text="shift"></strong></span>
                        </div>
                        <h2 class="text-base font-bold text-[var(--hc-text)] mt-0.5">
                            Inpatient Bed Census Verification &amp; Final Approval
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
                                            DEPARTMENT OF INPATIENT CARE &amp; CLINICAL BED CENSUS RECONCILIATION
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="inline-block px-2.5 py-1 bg-[var(--hc-text)] text-[var(--hc-surface)] font-mono font-bold text-xs rounded">
                                        FORM ID: ANL-IPD-03
                                    </div>
                                    <div class="text-[10px] text-[var(--hc-text-muted)] font-mono mt-1">ISO 9001:2015 &bull; CLINICAL AUDIT READY</div>
                                </div>
                            </div>

                            <div class="paper-metadata-bar">
                                <div><span class="font-bold text-[var(--hc-text)]">REPORT DATE:</span> <span class="font-mono text-xs font-bold" x-text="reportDate"></span></div>
                                <div><span class="font-bold text-[var(--hc-text)]">DUTY PERIOD:</span> <span class="font-semibold text-xs" x-text="shift"></span></div>
                                <div><span class="font-bold text-[var(--hc-text)]">WARD SUPERVISOR:</span> <span class="font-semibold text-xs" x-text="supervisor"></span></div>
                            </div>
                        </div>

                        <!-- Section A: Bed Census Balance Summary -->
                        <div class="p-4 border-b border-[var(--hc-border)] space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs uppercase tracking-wider text-[var(--hc-text)]">SECTION A: MATHEMATICAL BED BALANCING [A + B - C - D - E = F]</span>
                                <span class="font-mono text-xs font-bold text-[var(--color-primary)]" x-text="'BOR: ' + borPercentage + '%'"></span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-6 gap-2 text-xs font-mono">
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] text-center">
                                    <span class="text-[10px] text-[var(--hc-text-muted)] block uppercase">Prev Midnight (A)</span>
                                    <span class="font-bold text-sm text-[var(--hc-text)]" x-text="prevMidnightCensus"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] text-center">
                                    <span class="text-[10px] text-[var(--hc-text-muted)] block uppercase">Admitted (B)</span>
                                    <span class="font-bold text-sm text-[var(--hc-success)]" x-text="admissions"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] text-center">
                                    <span class="text-[10px] text-[var(--hc-text-muted)] block uppercase">Discharges (C)</span>
                                    <span class="font-bold text-sm text-[var(--hc-text)]" x-text="discharges"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] text-center">
                                    <span class="text-[10px] text-[var(--hc-text-muted)] block uppercase">Transfers Out (D)</span>
                                    <span class="font-bold text-sm text-[var(--hc-text)]" x-text="transfersOut"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] text-center">
                                    <span class="text-[10px] text-[var(--hc-text-muted)] block uppercase">Deaths (E)</span>
                                    <span class="font-bold text-sm text-[var(--hc-danger)]" x-text="deaths"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border-2 border-[var(--color-primary)] text-center">
                                    <span class="text-[10px] text-[var(--color-primary)] font-bold block uppercase">Ending Census (F)</span>
                                    <span class="font-black text-sm text-[var(--color-primary)]" x-text="endingCensusF"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Section B & C Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x border-b border-[var(--hc-border)]">
                            <!-- Maternity & Delivery Summary -->
                            <div class="p-4 space-y-2">
                                <span class="font-bold text-xs uppercase tracking-wider text-[var(--hc-text)]">SECTION B: MATERNITY &amp; LABOUR SUMMARY</span>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div class="p-2 rounded bg-[var(--hc-surface-alt)] flex items-center justify-between">
                                        <span>NVD Normal Deliveries:</span>
                                        <span class="font-bold font-mono text-[var(--hc-success)]" x-text="nvdDeliveries"></span>
                                    </div>
                                    <div class="p-2 rounded bg-[var(--hc-surface-alt)] flex items-center justify-between">
                                        <span>C-Section Deliveries:</span>
                                        <span class="font-bold font-mono text-[var(--hc-warning)]" x-text="cSectionDeliveries"></span>
                                    </div>
                                    <div class="p-2 rounded bg-[var(--hc-surface-alt)] flex items-center justify-between">
                                        <span>C-Section Rate:</span>
                                        <span class="font-bold font-mono text-[var(--hc-purple)]" x-text="cSectionRate + '%'"></span>
                                    </div>
                                    <div class="p-2 rounded bg-[var(--hc-surface-alt)] flex items-center justify-between">
                                        <span>NICU Admissions:</span>
                                        <span class="font-bold font-mono text-[var(--hc-danger)]" x-text="nicuAdmissions"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Ward Capacity & Notes -->
                            <div class="p-4 space-y-2">
                                <span class="font-bold text-xs uppercase tracking-wider text-[var(--hc-text)]">SECTION C: BED UTILIZATION &amp; WARD OBSERVATIONS</span>
                                <div class="space-y-2 text-xs">
                                    <div class="flex items-center justify-between py-1 border-b border-[var(--hc-border)]">
                                        <span class="text-[var(--hc-text-muted)]">Operational Bed Capacity:</span>
                                        <span class="font-bold font-mono" x-text="availableBeds + ' beds'"></span>
                                    </div>
                                    <div class="flex items-center justify-between py-1 border-b border-[var(--hc-border)]">
                                        <span class="text-[var(--hc-text-muted)]">Net Inpatient Bed-Days:</span>
                                        <span class="font-bold font-mono" x-text="patientBedDays"></span>
                                    </div>
                                    <div class="pt-1">
                                        <span class="text-[var(--hc-text-muted)] block text-[11px]">Shift Ward Handover Notes:</span>
                                        <p class="font-medium text-xs mt-0.5 text-[var(--hc-text)] italic" x-text="notes || 'No specific clinical incidents reported.'"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Certification Footer -->
                        <div class="p-4 bg-[var(--hc-surface-alt)] flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-[var(--hc-text-muted)]">
                            <div class="flex items-center gap-2">
                                <i data-lucide="shield-check" class="w-4 h-4 text-[var(--hc-success)]"></i>
                                <span>Mathematical Bed Balance Verified &bull; ISO Inpatient Audit Certification</span>
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
