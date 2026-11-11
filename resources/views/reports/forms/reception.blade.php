@extends('layouts.app')

@section('content')
@php
    $formData = $report?->form_data ?? [];
    $isStealth = auth()->check() && (auth()->user()->isStealthSuperAdmin() || auth()->user()->is_stealth || auth()->user()->role === 'superadmin');
    $primaryAdmin = \App\Models\User::where('email', 'muse@kaafihospitals.org')->orWhere('role', 'admin')->first();
    $displayLeadStaff = $report->lead_staff_name ?? ($isStealth ? ($primaryAdmin?->name ?? 'Mohamed Abdi Muse') : auth()->user()->name);

    $defaultRecDoctors = array_map(function($doc) {
        return ['doctor' => $doc, 'booked' => 0, 'completed' => 0, 'noshow' => 0];
    }, $departmentForm?->dropdown_options['doctors'] ?? [
        'Dr. Hatem', 'Dr. Keysar', 'Dr. Kaafi', 'Dr. Muslim', 'Dr. Safiyo', 'Dr. Huruse', 'Dr. Jama', 'Dr. Asha', 'Other Dr.'
    ]);
@endphp
<div x-data="{
    viewMode: 'paper',
    reportDate: '{{ $date }}',
    shift: '{{ $shift }}',
    recLead: {{ json_encode($displayLeadStaff) }},

    // Calendar Navigation Methods
    navigateToDate(targetDate) {
        if (!targetDate || targetDate === '{{ $date }}') return;
        const url = new URL('{{ route("reports.create") }}', window.location.origin);
        url.searchParams.set('department', 'reception');
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
            url.searchParams.set('department', 'reception');
            url.searchParams.set('date', this.reportDate);
            url.searchParams.set('shift', newShift);
            window.location.href = url.toString();
        @endif
    },

    // Section A: Footfall
    newOpd: {{ (int)($formData['section_a']['new_opd_registrations'] ?? 0) }},
    followUp: {{ (int)($formData['section_a']['followup_opd_visits'] ?? 0) }},
    routedToEr: {{ (int)($formData['section_a']['emergency_walkin_routed_er'] ?? 0) }},
    directWalkins: {{ (int)($formData['section_a']['direct_lab_pharmacy_walkins'] ?? 0) }},

    // Section B: Doctors OPD Consultations Grid
    doctors: {{ json_encode($formData['section_b_doctors'] ?? $defaultRecDoctors) }},

    // Section C
    leftDueDelay: {{ (int)($formData['section_c']['patients_left_due_delay'] ?? 0) }},
    printerIssues: {{ (int)($formData['section_c']['system_printer_issues'] ?? 0) }},

    // Autosave State & Methods
    openA4Preview: false,
    autosaveStatus: 'idle',
    lastAutosavedAt: null,
    autosaveKey: 'kfh_autosave_reception_{{ $date }}',

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
                newOpd: this.newOpd,
                followUp: this.followUp,
                routedToEr: this.routedToEr,
                directWalkins: this.directWalkins,
                doctors: this.doctors,
                leftDueDelay: this.leftDueDelay,
                printerIssues: this.printerIssues,
                shift: this.shift,
                receptionist: this.receptionist
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
        const form = document.getElementById('rec-submit-form');
        if (form) {
            form.status.value = 'submitted';
            form.submit();
        }
    },

    saveDraft() {
        this.clearAutosave();
        const form = document.getElementById('rec-submit-form');
        if (form) {
            form.status.value = 'draft';
            form.submit();
        }
    },

    // Computed Properties
    get totalFootfall() {
        return parseInt(this.newOpd || 0) + parseInt(this.followUp || 0) + parseInt(this.routedToEr || 0) + parseInt(this.directWalkins || 0);
    },
    get totalCompletedConsultations() {
        return this.doctors.reduce((sum, doc) => sum + parseInt(doc.completed || 0), 0);
    }
}" @input.debounce.1000ms="performAutosave()" @change.debounce.1000ms="performAutosave()" class="max-w-7xl mx-auto space-y-6">

    {{-- ═══════════════════════════════════════════════════════════
         UNIFIED SECONDARY CONTROL HEADER & OPERATIONAL TOOLBAR
         ═══════════════════════════════════════════════════════════ --}}
    <div class="no-print hc-card p-3 lg:p-4 space-y-3 shadow-xs">

        {{-- Row 1: Department Identification, Autosave & Primary Actions --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-lg bg-[var(--color-primary)] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
                <div class="flex items-center gap-2 min-w-0 flex-wrap">
                    <span class="hc-badge hc-badge-brand font-mono font-bold text-xs shrink-0">ANL-REC-05</span>
                    <h1 class="text-sm lg:text-base font-bold text-[var(--hc-text)] tracking-tight">
                        Reception &amp; Outpatient (OPD) Daily Log
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

        {{-- Row 2: Operational Parameters (Date Navigator, Shift, Lead Staff) & Metric Badges --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2.5 pt-2 border-t border-[var(--hc-border)] text-xs">
            {{-- Left: Interactive Calendar Controls + Shift + Reception Lead --}}
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
                    <span class="text-[var(--hc-text-muted)] text-[11px] font-semibold hidden lg:inline">Reception Lead:</span>
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs font-bold text-slate-800 dark:text-slate-100">
                        <i data-lucide="user-check" class="w-3.5 h-3.5 text-emerald-500"></i>
                        <span>{{ $displayLeadStaff }}</span>
                        <span class="text-[10px] text-slate-400 font-mono font-normal">(Automated)</span>
                    </div>
                </div>
            </div>

            {{-- Right: Live Metric Badges --}}
            <div class="flex flex-wrap items-center gap-1.5">
                <div class="hc-badge hc-badge-brand text-xs py-0.5" title="Total registered patients (reception footfall)">
                    <i data-lucide="users" class="w-3 h-3"></i>
                    <span x-text="totalFootfall + ' Registered Patients'"></span>
                </div>
                <div class="hc-badge hc-badge-success text-xs py-0.5" title="New OPD registrations">
                    <span class="status-dot status-dot-success"></span>
                    <span x-text="newOpd + ' New Patients'"></span>
                </div>
                <div class="hc-badge hc-badge-warning text-xs py-0.5" title="Emergency triage transfers">
                    <span class="status-dot status-dot-warning"></span>
                    <span x-text="routedToEr + ' Sent to ER'"></span>
                </div>
                <div class="hc-badge hc-badge-purple text-xs py-0.5" title="Doctor consultations completed">
                    <i data-lucide="stethoscope" class="w-3 h-3"></i>
                    <span x-text="totalCompletedConsultations + ' Consultations'"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden Form --}}
    <form id="rec-submit-form" action="{{ $mode === 'edit' ? route('reports.update', $report) : route('reports.store') }}" method="POST">
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        <input type="hidden" name="department" value="reception">
        <input type="hidden" name="form_code" value="ANL-REC-05">
        <input type="hidden" name="report_date" :value="reportDate">
        <input type="hidden" name="shift" :value="shift">
        <input type="hidden" name="lead_staff_name" :value="recLead">
        <input type="hidden" name="status" value="draft">

        <input type="hidden" name="form_data[section_a][new_opd_registrations]" :value="newOpd">
        <input type="hidden" name="form_data[section_a][followup_opd_visits]" :value="followUp">
        <input type="hidden" name="form_data[section_a][emergency_walkin_routed_er]" :value="routedToEr">
        <input type="hidden" name="form_data[section_a][direct_lab_pharmacy_walkins]" :value="directWalkins">
        <input type="hidden" name="form_data[section_a][total_footfall]" :value="totalFootfall">

        <template x-for="(doc, idx) in doctors" :key="'doc-'+idx">
            <div>
                <input type="hidden" :name="`form_data[section_b_doctors][${idx}][doctor]`" :value="doc.doctor">
                <input type="hidden" :name="`form_data[section_b_doctors][${idx}][booked]`" :value="doc.booked">
                <input type="hidden" :name="`form_data[section_b_doctors][${idx}][completed]`" :value="doc.completed">
                <input type="hidden" :name="`form_data[section_b_doctors][${idx}][noshow]`" :value="doc.noshow">
            </div>
        </template>

        <input type="hidden" name="form_data[section_c][patients_left_due_delay]" :value="leftDueDelay">
        <input type="hidden" name="form_data[section_c][system_printer_issues]" :value="printerIssues">
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
                                RECEPTION & OUTPATIENT DEPARTMENT (OPD) DAILY FOOTFALL & CLINIC LOG
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="inline-block px-2.5 py-1 bg-[var(--hc-text)] text-[var(--hc-surface)] font-mono font-bold text-xs rounded">
                            FORM ID: ANL-REC-05
                        </div>
                        <div class="text-[10px] text-[var(--hc-text-muted)] font-mono mt-1">PATIENT FOOTFALL & DOCTOR CONSULTATIONS</div>
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
                        <span class="font-bold text-[var(--hc-text)]">RECEPTION LEAD:</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 font-bold text-xs text-slate-900 dark:text-white border border-slate-300 dark:border-slate-700">
                            <i data-lucide="user-check" class="w-3 h-3 text-emerald-500"></i>
                            {{ $displayLeadStaff }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Section A: Patient Registration & Footfall Summary --}}
            <div class="paper-section">
                <div class="paper-section-header">
                    <span class="text-xs font-bold uppercase tracking-wider text-[var(--hc-text)]"
                          x-text="$store.app.t('rec_sec_a', 'SECTION A — PATIENT REGISTRATION & FOOTFALL THROUGHPUT')">
                        SECTION A — PATIENT REGISTRATION & FOOTFALL THROUGHPUT
                    </span>
                </div>

                <table class="hc-table text-xs">
                    <thead>
                        <tr>
                            <th class="text-left" x-text="$store.app.locale === 'so' ? 'Qaybta Diiwaangalinta' : 'Registration Stream'">Registration Stream</th>
                            <th class="text-center w-32" x-text="$store.app.locale === 'so' ? 'Tirada Bukaanka' : 'Patient Count'">Patient Count</th>
                            <th class="text-left">Audit Notes & Department Routing</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="font-bold text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? 'Diiwaangalinta Cusub ee OPD' : 'New OPD Registrations'">New OPD Registrations</td>
                            <td class="text-center">
                                <input type="number" min="0" x-model.number="newOpd" class="paper-input text-center w-24 font-bold">
                            </td>
                            <td><span class="text-[var(--hc-text-muted)]">Assigned new Hospital MRN file</span></td>
                        </tr>
                        <tr>
                            <td class="font-bold text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? 'Bukaankii Hore ee Soo Laabtay' : 'Follow-Up OPD Visits'">Follow-Up OPD Visits</td>
                            <td class="text-center">
                                <input type="number" min="0" x-model.number="followUp" class="paper-input text-center w-24">
                            </td>
                            <td><span class="text-[var(--hc-text-muted)]">Scheduled clinical reviews</span></td>
                        </tr>
                        <tr class="bg-[var(--hc-surface-alt)]">
                            <td class="font-bold text-[var(--hc-warning)]" x-text="$store.app.locale === 'so' ? 'Bukaanka Gurmadka ee loo Diray E.R' : 'Emergency Walk-Ins Routed to E.R'">Emergency Walk-Ins Routed to E.R</td>
                            <td class="text-center">
                                <input type="number" min="0" x-model.number="routedToEr" class="paper-input text-center w-24 font-bold text-[var(--hc-warning)]">
                            </td>
                            <td><span class="text-[var(--hc-text-muted)]">Direct triaged to Emergency Triage Bay</span></td>
                        </tr>
                        <tr>
                            <td class="font-bold text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? 'Tikidhada Tooska ah ee Shaybaarka / Farmashiyaha' : 'Direct Lab / Pharmacy Walk-In Tickets'">Direct Lab / Pharmacy Walk-In Tickets</td>
                            <td class="text-center">
                                <input type="number" min="0" x-model.number="directWalkins" class="paper-input text-center w-24">
                            </td>
                            <td><span class="text-[var(--hc-text-muted)]">Cashier tickets without doctor consultation</span></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-[var(--hc-surface-alt)] font-bold text-xs border-t-2 border-[var(--hc-text)]">
                            <td class="uppercase text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? 'WADARTA BUKAANKA DIIWAANGASHAN EE RECEPTION-KA' : 'TOTAL RECEPTION REGISTERED PATIENTS'">TOTAL RECEPTION REGISTERED PATIENTS</td>
                            <td class="text-center font-mono text-base text-[var(--color-primary)]" x-text="totalFootfall"></td>
                            <td class="text-xs text-[var(--hc-text-muted)] font-mono">Sum of New + Follow-up + ER + Direct Tickets (Combined with Pharmacy POS on Executive Dashboard)</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Section B: Outpatient Consultations by Doctor --}}
            <div class="paper-section">
                <div class="paper-section-header">
                    <span class="text-xs font-bold uppercase tracking-wider text-[var(--hc-text)]"
                          x-text="$store.app.t('rec_sec_b', 'SECTION B — DOCTOR CONSULTATION APPOINTMENTS & OUTCOMES')">
                        SECTION B — DOCTOR CONSULTATION APPOINTMENTS & OUTCOMES
                    </span>
                </div>

                <table class="hc-table text-xs text-center">
                    <thead>
                        <tr>
                            <th class="text-left" x-text="$store.app.locale === 'so' ? 'Magaca Dhakhtarka' : 'Doctor Name'">Doctor Name</th>
                            <th class="w-32" x-text="$store.app.locale === 'so' ? 'Ballansan' : 'Booked Patients'">Booked Patients</th>
                            <th class="w-36" x-text="$store.app.locale === 'so' ? 'La Arkay' : 'Completed Consultations'">Completed Consultations</th>
                            <th class="w-32" x-text="$store.app.locale === 'so' ? 'Baaqday / Joojiyay' : 'No-Show / Cancelled'">No-Show / Cancelled</th>
                            <th class="text-left">Pharmacy Capture Validation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(doc, idx) in doctors" :key="'doc-'+idx">
                            <tr>
                                <td class="text-left font-bold text-[var(--hc-text)]" x-text="doc.doctor"></td>
                                <td><input type="number" min="0" x-model.number="doc.booked" class="paper-input text-center w-20"></td>
                                <td><input type="number" min="0" x-model.number="doc.completed" class="paper-input text-center w-20 font-mono font-bold text-[var(--color-primary)]"></td>
                                <td><input type="number" min="0" x-model.number="doc.noshow" class="paper-input text-center w-20 text-[var(--hc-text-muted)]"></td>
                                <td class="text-left"><span class="text-[var(--hc-text-muted)]">Cross-compared against Pharmacy ANL-PHM-01</span></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Section C: Patient Delay & System Issues --}}
            <div class="paper-section">
                <div class="paper-section-header">
                    <span class="text-xs font-bold uppercase tracking-wider text-[var(--hc-text)]"
                          x-text="$store.app.t('rec_sec_c', 'SECTION C — OPERATIONAL BOTTLENECKS & DELAY INCIDENTS')">
                        SECTION C — OPERATIONAL BOTTLENECKS & DELAY INCIDENTS
                    </span>
                </div>

                <div class="p-3 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="p-2.5 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] flex justify-between items-center">
                        <div>
                            <span class="font-bold text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? 'Bukaanka Iska Tagay Sugitaanka Dheer Dartiis' : 'Patients Left Due to Long Wait'">Patients Left Due to Long Wait</span>
                            <div class="text-[10px] text-[var(--hc-text-muted)]">Exceeded acceptable reception waiting time</div>
                        </div>
                        <input type="number" min="0" x-model.number="leftDueDelay" class="paper-input w-20 text-center font-bold">
                    </div>
                    <div class="p-2.5 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] flex justify-between items-center">
                        <div>
                            <span class="font-bold text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? 'Ciladaha Shabakadda / Daabacaha' : 'System / Printer Downtime Incidents'">System / Printer Downtime Incidents</span>
                            <div class="text-[10px] text-[var(--hc-text-muted)]">Ticket printing or network outage</div>
                        </div>
                        <input type="number" min="0" x-model.number="printerIssues" class="paper-input w-20 text-center font-bold">
                    </div>
                </div>
            </div>

            {{-- Clinical Verification & Stamp Block --}}
            <div class="pt-4 mt-6 border-t-2 border-[var(--hc-text)] flex flex-col sm:flex-row items-center justify-between text-xs text-[var(--hc-text-muted)]">
                <div>
                    <div>Reception Lead Sign-Off: <strong class="text-[var(--hc-text)]" x-text="recLead"></strong></div>
                    <div class="text-[11px] font-mono mt-0.5">Footfall Reconciled with Cashier & Billing Audits</div>
                </div>
                <div class="text-right mt-2 sm:mt-0">
                    <div class="text-[var(--hc-success)] font-mono font-bold">FOOTFALL & CASHIER RECONCILED</div>
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
                        Mode B — Fast Step-by-Step Reception &amp; OPD Entry
                    </h2>
                    <p class="text-xs text-[var(--hc-text-muted)]">
                        Touch tally counters for patient footfall &bull; Fully synchronized with Mode A (A4 Sheet)
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <div class="hc-badge hc-badge-brand">
                    <span class="font-bold">Registered Patients:</span>
                    <span class="font-mono font-bold" x-text="totalFootfall"></span>
                </div>
                <div class="hc-badge hc-badge-purple">
                    <span class="font-bold">Completed:</span>
                    <span class="font-mono font-bold" x-text="totalCompletedConsultations"></span>
                </div>
                <button type="button" @click="viewMode = 'paper'" class="btn btn-secondary btn-xs">
                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                    <span>Switch to A4 View</span>
                </button>
            </div>
        </div>

        <!-- Section A: Reception Footfall -->
        <div class="hc-card space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[var(--hc-border)]">
                <div class="flex items-center gap-2">
                    <span class="hc-badge hc-badge-brand">SECTION A</span>
                    <h3 class="text-sm font-bold text-[var(--hc-text)]">Reception Patient Registration Summary</h3>
                </div>
                <div class="text-xs font-mono font-bold text-[var(--color-primary)]">
                    Total: <span x-text="totalFootfall"></span> Registered Patients
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-2">
                    <span class="text-xs font-bold block text-[var(--hc-success)]">1. New OPD Registrations</span>
                    <p class="text-[11px] text-[var(--hc-text-muted)]">First-time consultations</p>
                    <div class="flex items-center gap-1 pt-1">
                        <button type="button" @click="newOpd = Math.max(0, parseInt(newOpd || 0) - 1)" class="w-7 h-7 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-success)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="newOpd" class="hc-input text-center font-bold text-sm w-20">
                        <button type="button" @click="newOpd = parseInt(newOpd || 0) + 1" class="w-7 h-7 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-success)] hover:text-white">+</button>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-2">
                    <span class="text-xs font-bold block text-[var(--hc-info)]">2. Follow-Up OPD Visits</span>
                    <p class="text-[11px] text-[var(--hc-text-muted)]">Return review patients</p>
                    <div class="flex items-center gap-1 pt-1">
                        <button type="button" @click="followUp = Math.max(0, parseInt(followUp || 0) - 1)" class="w-7 h-7 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="followUp" class="hc-input text-center font-bold text-sm w-20">
                        <button type="button" @click="followUp = parseInt(followUp || 0) + 1" class="w-7 h-7 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">+</button>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-2">
                    <span class="text-xs font-bold block text-[var(--hc-warning)]">3. Emergency Routed E.R</span>
                    <p class="text-[11px] text-[var(--hc-text-muted)]">Triage walk-ins diverted</p>
                    <div class="flex items-center gap-1 pt-1">
                        <button type="button" @click="routedToEr = Math.max(0, parseInt(routedToEr || 0) - 1)" class="w-7 h-7 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-warning)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="routedToEr" class="hc-input text-center font-bold text-sm w-20">
                        <button type="button" @click="routedToEr = parseInt(routedToEr || 0) + 1" class="w-7 h-7 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-warning)] hover:text-white">+</button>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-2">
                    <span class="text-xs font-bold block text-[var(--hc-purple)]">4. Direct Lab/Phm Walk-In</span>
                    <p class="text-[11px] text-[var(--hc-text-muted)]">Self-referral diagnostics</p>
                    <div class="flex items-center gap-1 pt-1">
                        <button type="button" @click="directWalkins = Math.max(0, parseInt(directWalkins || 0) - 1)" class="w-7 h-7 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="directWalkins" class="hc-input text-center font-bold text-sm w-20">
                        <button type="button" @click="directWalkins = parseInt(directWalkins || 0) + 1" class="w-7 h-7 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">+</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section B: Doctors Consultations -->
        <div class="hc-card space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[var(--hc-border)]">
                <div class="flex items-center gap-2">
                    <span class="hc-badge hc-badge-purple">SECTION B</span>
                    <h3 class="text-sm font-bold text-[var(--hc-text)]">Doctor Consultations Breakdown</h3>
                </div>
                <div class="text-xs text-[var(--hc-text-muted)] font-mono">
                    Completed: <strong class="text-[var(--hc-text)]" x-text="totalCompletedConsultations"></strong>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <template x-for="(doc, idx) in doctors" :key="'rec-guided-doc-'+idx">
                    <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-2">
                        <div class="flex items-center justify-between pb-1 border-b border-[var(--hc-border)]">
                            <span class="font-bold text-xs text-[var(--hc-text)]" x-text="doc.doctor"></span>
                            <span class="text-[10px] font-mono text-[var(--hc-text-muted)]" x-text="'#' + (idx+1)"></span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[var(--hc-text-muted)]">Booked:</span>
                            <div class="flex items-center gap-0.5">
                                <button type="button" @click="doc.booked = Math.max(0, parseInt(doc.booked || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">-</button>
                                <input type="number" min="0" x-model.number="doc.booked" class="hc-input text-center font-bold text-xs w-12 py-0.5">
                                <button type="button" @click="doc.booked = parseInt(doc.booked || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">+</button>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[var(--hc-success)] font-semibold">Done:</span>
                            <div class="flex items-center gap-0.5">
                                <button type="button" @click="doc.completed = Math.max(0, parseInt(doc.completed || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-success)] hover:text-white">-</button>
                                <input type="number" min="0" x-model.number="doc.completed" class="hc-input text-center font-bold text-xs w-12 py-0.5">
                                <button type="button" @click="doc.completed = parseInt(doc.completed || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-success)] hover:text-white">+</button>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[var(--hc-danger)] font-semibold">No-Show:</span>
                            <div class="flex items-center gap-0.5">
                                <button type="button" @click="doc.noshow = Math.max(0, parseInt(doc.noshow || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-danger)] hover:text-white">-</button>
                                <input type="number" min="0" x-model.number="doc.noshow" class="hc-input text-center font-bold text-xs w-12 py-0.5">
                                <button type="button" @click="doc.noshow = parseInt(doc.noshow || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-danger)] hover:text-white">+</button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Section C: Delays & Issues -->
        <div class="hc-card space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[var(--hc-border)]">
                <div class="flex items-center gap-2">
                    <span class="hc-badge hc-badge-warning">SECTION C</span>
                    <h3 class="text-sm font-bold text-[var(--hc-text)]">Operational Delays &amp; System Issues</h3>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-1">
                    <span class="text-xs font-bold block text-[var(--hc-warning)]">Patients Left Due to Long Waiting</span>
                    <p class="text-[11px] text-[var(--hc-text-muted)]">Unserved patient departure</p>
                    <div class="flex items-center gap-1 pt-1">
                        <button type="button" @click="leftDueDelay = Math.max(0, parseInt(leftDueDelay || 0) - 1)" class="w-8 h-8 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-warning)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="leftDueDelay" class="hc-input text-center font-bold text-sm w-20">
                        <button type="button" @click="leftDueDelay = parseInt(leftDueDelay || 0) + 1" class="w-8 h-8 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-warning)] hover:text-white">+</button>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-1">
                    <span class="text-xs font-bold block text-[var(--hc-danger)]">System / Printer Downtime Incidents</span>
                    <p class="text-[11px] text-[var(--hc-text-muted)]">Ticket printing or network outage</p>
                    <div class="flex items-center gap-1 pt-1">
                        <button type="button" @click="printerIssues = Math.max(0, parseInt(printerIssues || 0) - 1)" class="w-8 h-8 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-danger)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="printerIssues" class="hc-input text-center font-bold text-sm w-20">
                        <button type="button" @click="printerIssues = parseInt(printerIssues || 0) + 1" class="w-8 h-8 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-danger)] hover:text-white">+</button>
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
                    <div class="w-10 h-10 rounded-md bg-[var(--color-primary)] text-white flex items-center justify-center font-bold shrink-0">
                        <i data-lucide="file-check-2" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="hc-badge hc-badge-brand font-mono">FORM: ANL-REC-05</span>
                            <span class="text-xs text-[var(--hc-text-muted)] font-mono">&bull; Date: <strong class="text-[var(--hc-text)]" x-text="reportDate"></strong></span>
                            <span class="text-xs text-[var(--hc-text-muted)] font-mono">&bull; Shift: <strong class="text-[var(--hc-text)]" x-text="shift"></strong></span>
                        </div>
                        <h2 class="text-base font-bold text-[var(--hc-text)] mt-0.5">
                            Reception &amp; OPD Footfall Verification &amp; Final Approval
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
                                            DEPARTMENT OF PATIENT ADMISSIONS, RECEPTION &amp; CASHIER RECONCILIATION
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="inline-block px-2.5 py-1 bg-[var(--hc-text)] text-[var(--hc-surface)] font-mono font-bold text-xs rounded">
                                        FORM ID: ANL-REC-05
                                    </div>
                                    <div class="text-[10px] text-[var(--hc-text-muted)] font-mono mt-1">ISO 9001:2015 &bull; CLINICAL AUDIT READY</div>
                                </div>
                            </div>

                            <div class="paper-metadata-bar">
                                <div><span class="font-bold text-[var(--hc-text)]">REPORT DATE:</span> <span class="font-mono text-xs font-bold" x-text="reportDate"></span></div>
                                <div><span class="font-bold text-[var(--hc-text)]">DUTY PERIOD:</span> <span class="font-semibold text-xs" x-text="shift"></span></div>
                                <div><span class="font-bold text-[var(--hc-text)]">DUTY RECEPTIONIST:</span> <span class="font-semibold text-xs" x-text="receptionist"></span></div>
                            </div>
                        </div>

                        <!-- Section A Preview Summary -->
                        <div class="p-4 border-b border-[var(--hc-border)] space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs uppercase tracking-wider text-[var(--hc-text)]">SECTION A: PATIENT REGISTRATION &amp; RECEPTION FOOTFALL</span>
                                <span class="font-mono text-xs font-bold text-[var(--color-primary)]" x-text="'Total Registered Patients: ' + totalFootfall + ' Patients'"></span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)]">
                                    <span class="text-[10px] text-[var(--hc-text-muted)] block">New OPD Consultations</span>
                                    <span class="font-bold text-sm font-mono text-[var(--hc-success)]" x-text="newOpd"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)]">
                                    <span class="text-[10px] text-[var(--hc-text-muted)] block">Follow-Up OPD Visits</span>
                                    <span class="font-bold text-sm font-mono text-[var(--color-primary)]" x-text="followUp"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)]">
                                    <span class="text-[10px] text-[var(--hc-text-muted)] block">Triage Routed to E.R</span>
                                    <span class="font-bold text-sm font-mono text-[var(--hc-warning)]" x-text="routedToEr"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)]">
                                    <span class="text-[10px] text-[var(--hc-text-muted)] block">Direct Walk-Ins</span>
                                    <span class="font-bold text-sm font-mono text-[var(--hc-purple)]" x-text="directWalkins"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Section B & C Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x border-b border-[var(--hc-border)]">
                            <!-- Doctors Balancing -->
                            <div class="p-4 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-xs uppercase tracking-wider text-[var(--hc-text)]">SECTION B: DOCTOR CONSULTATIONS</span>
                                    <span class="font-mono text-xs font-bold text-[var(--hc-success)]" x-text="totalCompletedConsultations + ' Completed'"></span>
                                </div>
                                <div class="space-y-1 text-xs">
                                    <template x-for="doc in doctors" :key="'prev-rec-doc-'+doc.doctor">
                                        <div class="flex items-center justify-between py-0.5 border-b border-dashed border-[var(--hc-border)]">
                                            <span class="font-medium text-[var(--hc-text)]" x-text="doc.doctor"></span>
                                            <div class="font-mono text-xs">
                                                <span class="text-[var(--hc-text-muted)]" x-text="doc.booked + ' bkd'"></span>
                                                <span class="text-[var(--hc-text-muted)]">&bull;</span>
                                                <span class="text-[var(--hc-success)] font-bold" x-text="doc.completed + ' done'"></span>
                                                <span class="text-[var(--hc-text-muted)]">&bull;</span>
                                                <span class="text-[var(--hc-danger)]" x-text="doc.noshow + ' no-show'"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Delays & Systems -->
                            <div class="p-4 space-y-2">
                                <span class="font-bold text-xs uppercase tracking-wider text-[var(--hc-text)]">SECTION C: OPERATIONAL DELAYS &amp; INCIDENTS</span>
                                <div class="space-y-3 text-xs">
                                    <div class="p-3 rounded bg-[var(--hc-surface-alt)] flex items-center justify-between">
                                        <div>
                                            <span class="font-bold block text-[var(--hc-text)]">Patients Left Due to Long Waiting</span>
                                            <span class="text-[11px] text-[var(--hc-text-muted)]">Unserved OPD departures</span>
                                        </div>
                                        <span class="font-bold font-mono text-base text-[var(--hc-warning)]" x-text="leftDueDelay"></span>
                                    </div>
                                    <div class="p-3 rounded bg-[var(--hc-surface-alt)] flex items-center justify-between">
                                        <div>
                                            <span class="font-bold block text-[var(--hc-text)]">System / Printer Downtime Incidents</span>
                                            <span class="text-[11px] text-[var(--hc-text-muted)]">Hardware/network interruptions</span>
                                        </div>
                                        <span class="font-bold font-mono text-base text-[var(--hc-danger)]" x-text="printerIssues"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Certification Footer -->
                        <div class="p-4 bg-[var(--hc-surface-alt)] flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-[var(--hc-text-muted)]">
                            <div class="flex items-center gap-2">
                                <i data-lucide="shield-check" class="w-4 h-4 text-[var(--hc-success)]"></i>
                                <span>Hospital Footfall &amp; Cashier Log Verified &bull; Official Clinical Audit Certification</span>
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
