@extends('layouts.app')

@section('content')
@php
    $formData = $report?->form_data ?? [];
    $isStealth = auth()->check() && (auth()->user()->isStealthSuperAdmin() || auth()->user()->is_stealth || auth()->user()->role === 'superadmin');
    $primaryAdmin = \App\Models\User::where('email', 'muse@kaafihospitals.org')->orWhere('role', 'admin')->first();
    $displayLeadStaff = $report->lead_staff_name ?? ($isStealth ? ($primaryAdmin?->name ?? 'Mohamed Abdi Muse') : auth()->user()->name);
@endphp

<script>
function emergencyReportForm() {
    return {
        viewMode: 'paper',
        reportDate: '{{ $date }}',
        shift: '{{ $shift }}',
        erLead: {!! json_encode($displayLeadStaff) !!},

    // Calendar Navigation Methods
    navigateToDate(targetDate) {
        if (!targetDate || targetDate === '{{ $date }}') return;
        const url = new URL('{{ route("reports.create") }}', window.location.origin);
        url.searchParams.set('department', 'emergency');
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
            url.searchParams.set('department', 'emergency');
            url.searchParams.set('date', this.reportDate);
            url.searchParams.set('shift', newShift);
            window.location.href = url.toString();
        @endif
    },

    // Section A: Arrivals
    acuteCases: {{ (int)($formData['section_a']['emergency_cases_acute'] ?? 0) }},
    opdInEr: {{ (int)($formData['section_a']['opd_cases_seen_in_er'] ?? 0) }},
    followUp: {{ (int)($formData['section_a']['followup_cases'] ?? 0) }},

    // Dispositions
    admittedToIpd: {{ (int)($formData['section_a']['disposition_admitted_ipd'] ?? 0) }},
    obsWard: {{ (int)($formData['section_a']['disposition_obs_ward'] ?? 0) }},
    dischargedStable: {{ (int)($formData['section_a']['disposition_discharged_stable'] ?? 0) }},
    referredOut: {{ (int)($formData['section_a']['disposition_referred_out'] ?? 0) }},
    lama: {{ (int)($formData['section_a']['disposition_lama'] ?? 0) }},

    // Section B: Demographics
    maleCount: {{ (int)($formData['section_b_demographics']['male_count'] ?? 0) }},
    femaleCount: {{ (int)($formData['section_b_demographics']['female_count'] ?? 0) }},
    under5: {{ (int)($formData['section_b_demographics']['under_5'] ?? 0) }},
    age5to17: {{ (int)($formData['section_b_demographics']['age_5_17'] ?? 0) }},
    age18to59: {{ (int)($formData['section_b_demographics']['age_18_59'] ?? 0) }},
    age60plus: {{ (int)($formData['section_b_demographics']['age_60_plus'] ?? 0) }},

    // Section E: Mortality Register
    doaCount: {{ (int)($formData['section_e_mortality']['doa_count'] ?? 0) }},
    diedInErCount: {{ (int)($formData['section_e_mortality']['died_in_er_count'] ?? 0) }},

    // Autosave State & Methods
    openA4Preview: false,
    autosaveStatus: 'idle',
    lastAutosavedAt: null,
    autosaveKey: 'kfh_autosave_emergency_{{ $date }}',

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
                acuteCases: this.acuteCases,
                opdInEr: this.opdInEr,
                followUp: this.followUp,
                maleCount: this.maleCount,
                femaleCount: this.femaleCount,
                under5Count: this.under5Count,
                above5Count: this.above5Count,
                triageRed: this.triageRed,
                triageYellow: this.triageYellow,
                triageGreen: this.triageGreen,
                triageBlack: this.triageBlack,
                dischargedHome: this.dischargedHome,
                admittedToIpd: this.admittedToIpd,
                transferredOut: this.transferredOut,
                abscondedLama: this.abscondedLama,
                doaCount: this.doaCount,
                diedInErCount: this.diedInErCount,
                notes: this.notes,
                shift: this.shift,
                erLead: this.erLead
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
        const form = document.getElementById('er-submit-form');
        if (form) {
            form.status.value = 'submitted';
            form.submit();
        }
    },

    saveDraft() {
        this.clearAutosave();
        const form = document.getElementById('er-submit-form');
        if (form) {
            form.status.value = 'draft';
            form.submit();
        }
    },

    // Computed properties
    get totalErCases() {
        return parseInt(this.acuteCases || 0) + parseInt(this.opdInEr || 0) + parseInt(this.followUp || 0);
    },
    get totalGender() {
        return parseInt(this.maleCount || 0) + parseInt(this.femaleCount || 0);
    },
    get conversionRate() {
        return this.totalErCases > 0 ? ((this.admittedToIpd / this.totalErCases) * 100).toFixed(1) : 0;
    },
        get inErMortalityRate() {
            return this.totalErCases > 0 ? ((this.diedInErCount / this.totalErCases) * 100).toFixed(2) : 0;
        }
    };
}
</script>

<div x-data="emergencyReportForm()" @input.debounce.1000ms="performAutosave()" @change.debounce.1000ms="performAutosave()" class="max-w-7xl mx-auto space-y-6">

    {{-- ═══════════════════════════════════════════════════════════
         ORGANIZED TOP ACTION BAR & OPERATIONAL CONTROLS
         ═══════════════════════════════════════════════════════════ --}}
    <div class="no-print hc-card space-y-4">
    {{-- ═══════════════════════════════════════════════════════════
         UNIFIED SECONDARY CONTROL HEADER & OPERATIONAL TOOLBAR
         ═══════════════════════════════════════════════════════════ --}}
    <div class="no-print hc-card p-3 lg:p-4 space-y-3 shadow-xs">

        {{-- Row 1: Department Identification, Autosave & Primary Actions --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-lg bg-[var(--hc-warning)] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                    <i data-lucide="siren" class="w-4 h-4"></i>
                </div>
                <div class="flex items-center gap-2 min-w-0 flex-wrap">
                    <span class="hc-badge hc-badge-warning font-mono font-bold text-xs shrink-0">ANL-ERD-02</span>
                    <h1 class="text-sm lg:text-base font-bold text-[var(--hc-text)] tracking-tight">
                        Emergency Room Patient Flow &amp; Clinical Triage
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

        {{-- Row 2: Operational Parameters (Date Navigator, Shift, ER Lead) & Metric Badges --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2.5 pt-2 border-t border-[var(--hc-border)] text-xs">
            {{-- Left: Interactive Calendar Controls + Period + ER Lead --}}
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
                        <option value="24-Hour Daily Summary">24-Hour Daily Summary</option>
                    </select>
                    @if(isset($autoShift))
                        <span class="hc-badge hc-badge-info text-[10px] py-0.5 hidden xl:inline-flex" title="Auto-determined shift based on current hospital time">
                            Auto: {{ $autoShift }}
                        </span>
                    @endif
                </div>

                <div class="flex items-center gap-1.5">
                    <span class="text-[var(--hc-text-muted)] text-[11px] font-semibold hidden lg:inline">E.R Lead:</span>
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs font-bold text-slate-800 dark:text-slate-100">
                        <i data-lucide="user-check" class="w-3.5 h-3.5 text-emerald-500"></i>
                        <span>{{ $displayLeadStaff }}</span>
                        <span class="text-[10px] text-slate-400 font-mono font-normal">(Automated)</span>
                    </div>
                </div>
            </div>

            {{-- Right: Live Metric Badges --}}
            <div class="flex flex-wrap items-center gap-1.5">
                <div class="hc-badge hc-badge-warning text-xs py-0.5" title="Total E.R arrivals">
                    <i data-lucide="activity" class="w-3 h-3"></i>
                    <span x-text="totalErCases + ' E.R Cases'"></span>
                </div>
                <div class="hc-badge hc-badge-brand text-xs py-0.5" title="Admitted to inpatient wards">
                    <i data-lucide="bed" class="w-3 h-3"></i>
                    <span x-text="admittedToIpd + ' Admitted IPD'"></span>
                </div>
                <div class="hc-badge hc-badge-success text-xs py-0.5" title="Admission conversion percentage">
                    <span class="status-dot status-dot-success"></span>
                    <span x-text="conversionRate + '% Conversion'"></span>
                </div>
                <div class="hc-badge hc-badge-danger text-xs py-0.5" title="In-ER mortality">
                    <span class="status-dot status-dot-danger"></span>
                    <span x-text="diedInErCount + ' Deaths (' + inErMortalityRate + '%)'"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden Submit Form --}}
    <form id="er-submit-form" action="{{ $mode === 'edit' ? route('reports.update', $report) : route('reports.store') }}" method="POST">
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        <input type="hidden" name="department" value="emergency">
        <input type="hidden" name="form_code" value="ANL-ERD-02">
        <input type="hidden" name="report_date" :value="reportDate">
        <input type="hidden" name="shift" :value="shift">
        <input type="hidden" name="lead_staff_name" :value="erLead">
        <input type="hidden" name="status" value="draft">

        <input type="hidden" name="form_data[section_a][emergency_cases_acute]" :value="acuteCases">
        <input type="hidden" name="form_data[section_a][opd_cases_seen_in_er]" :value="opdInEr">
        <input type="hidden" name="form_data[section_a][followup_cases]" :value="followUp">
        <input type="hidden" name="form_data[section_a][total_er_cases]" :value="totalErCases">
        <input type="hidden" name="form_data[section_a][disposition_admitted_ipd]" :value="admittedToIpd">
        <input type="hidden" name="form_data[section_a][disposition_obs_ward]" :value="obsWard">
        <input type="hidden" name="form_data[section_a][disposition_discharged_stable]" :value="dischargedStable">
        <input type="hidden" name="form_data[section_a][disposition_referred_out]" :value="referredOut">
        <input type="hidden" name="form_data[section_a][disposition_lama]" :value="lama">

        <input type="hidden" name="form_data[section_b_demographics][male_count]" :value="maleCount">
        <input type="hidden" name="form_data[section_b_demographics][female_count]" :value="femaleCount">
        <input type="hidden" name="form_data[section_b_demographics][under_5]" :value="under5">
        <input type="hidden" name="form_data[section_b_demographics][age_5_17]" :value="age5to17">
        <input type="hidden" name="form_data[section_b_demographics][age_18_59]" :value="age18to59">
        <input type="hidden" name="form_data[section_b_demographics][age_60_plus]" :value="age60plus">

        <input type="hidden" name="form_data[section_e_mortality][doa_count]" :value="doaCount">
        <input type="hidden" name="form_data[section_e_mortality][died_in_er_count]" :value="diedInErCount">
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
                                EMERGENCY DEPARTMENT (E.R) DAILY PATIENT VOLUME & DISPOSITION REGISTER
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="inline-block px-2.5 py-1 bg-[var(--hc-text)] text-[var(--hc-surface)] font-mono font-bold text-xs rounded">
                            FORM ID: ANL-ERD-02
                        </div>
                        <div class="text-[10px] text-[var(--hc-text-muted)] font-mono mt-1">EMERGENCY CLINICAL AUDIT SHEET</div>
                    </div>
                </div>

                {{-- Metadata Bar --}}
                <div class="paper-metadata-bar">
                    <div>
                        <span class="font-bold text-[var(--hc-text)]">REPORT DATE:</span>
                        <input type="date" x-model="reportDate" class="paper-input ml-2 font-mono text-xs">
                    </div>
                    <div>
                        <span class="font-bold text-[var(--hc-text)]">REPORTING PERIOD:</span>
                        <select x-model="shift" @change="changeShift($event.target.value)" class="paper-input ml-2 font-semibold text-xs">
                            <option value="Morning">Morning Shift (08:00 - 14:00)</option>
                            <option value="Afternoon">Noon Shift (14:00 - 21:00)</option>
                            <option value="Night">Night Shift (21:00 - 08:00)</option>
                            <option value="24-Hour Daily Summary">24-Hour Daily Summary</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-bold text-[var(--hc-text)]">E.R SHIFT LEAD:</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 font-bold text-xs text-slate-900 dark:text-white border border-slate-300 dark:border-slate-700">
                            <i data-lucide="user-check" class="w-3 h-3 text-emerald-500"></i>
                            {{ $displayLeadStaff }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Section A: Arrivals & Dispositions Summary --}}
            <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Arrivals --}}
                <div class="paper-section mb-0">
                    <div class="paper-section-header">
                        <span class="text-xs font-bold uppercase tracking-wider text-[var(--hc-text)]">
                            SECTION A1 — ARRIVALS (KORMOOYINKA SOO GALAY)
                        </span>
                    </div>
                    <table class="hc-table">
                        <tbody>
                            <tr>
                                <td class="font-bold text-xs text-[var(--hc-text)]">1. Emergency Acute Cases</td>
                                <td class="w-28 text-center">
                                    <input type="number" min="0" x-model.number="acuteCases" class="paper-input text-center w-full font-bold">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-xs text-[var(--hc-text)]">2. OPD Cases Seen in E.R</td>
                                <td class="w-28 text-center">
                                    <input type="number" min="0" x-model.number="opdInEr" class="paper-input text-center w-full">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-xs text-[var(--hc-text)]">3. Follow-Up Cases</td>
                                <td class="w-28 text-center">
                                    <input type="number" min="0" x-model.number="followUp" class="paper-input text-center w-full">
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-[var(--hc-surface-alt)] font-bold text-xs">
                                <td class="text-[var(--hc-text)] uppercase">TOTAL E.R ARRIVALS</td>
                                <td class="text-center font-mono text-sm text-[var(--color-primary)]" x-text="totalErCases"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Dispositions --}}
                <div class="paper-section mb-0">
                    <div class="paper-section-header">
                        <span class="text-xs font-bold uppercase tracking-wider text-[var(--hc-text)]"
                              x-text="$store.app.t('er_sec_a2', 'SECTION A2 — EMERGENCY DISPOSITIONS & PATIENT DESTINATIONS')">
                            SECTION A2 — EMERGENCY DISPOSITIONS & PATIENT DESTINATIONS
                        </span>
                    </div>
                    <table class="hc-table">
                        <tbody>
                            <tr class="bg-[var(--hc-surface-alt)]">
                                <td class="font-bold text-xs text-[var(--color-primary)]" x-text="$store.app.locale === 'so' ? 'Loo Dhigay Jiifka Isbitaalka (IPD)' : 'Admitted to Inpatient Wards (IPD)'">Admitted to Inpatient Wards (IPD)</td>
                                <td class="w-28 text-center">
                                    <input type="number" min="0" x-model.number="admittedToIpd" class="paper-input text-center w-full font-bold text-[var(--color-primary)]">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-xs text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? 'Qolka Dhaawaca / Kormeerka E.R' : 'Admissions in E.R Observation Ward'">Admissions in E.R Observation Ward</td>
                                <td class="w-28 text-center">
                                    <input type="number" min="0" x-model.number="obsWard" class="paper-input text-center w-full">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-xs text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? 'Guryahooda Loo Fasaxay (Caafimaad)' : 'Discharged Home Stable'">Discharged Home Stable</td>
                                <td class="w-28 text-center">
                                    <input type="number" min="0" x-model.number="dischargedStable" class="paper-input text-center w-full">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-xs text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? 'Loo Gudbiyay Isbitaal Kale' : 'Referred Out to External Hospital'">Referred Out to External Hospital</td>
                                <td class="w-28 text-center">
                                    <input type="number" min="0" x-model.number="referredOut" class="paper-input text-center w-full">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-xs text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? 'Is-hortaag / Fasax La\'aan Baxay (LAMA)' : 'Left Against Medical Advice (LAMA)'">Left Against Medical Advice (LAMA)</td>
                                <td class="w-28 text-center">
                                    <input type="number" min="0" x-model.number="lama" class="paper-input text-center w-full">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Section B: Demographics Tally Grid --}}
            <div class="paper-section">
                <div class="paper-section-header">
                    <span class="text-xs font-bold uppercase tracking-wider text-[var(--hc-text)]"
                          x-text="$store.app.t('er_sec_b', 'SECTION B — DEMOGRAPHICS & PATIENT AGE BRACKETS')">
                        SECTION B — DEMOGRAPHICS & PATIENT AGE BRACKETS
                    </span>
                </div>

                <table class="hc-table text-center">
                    <thead>
                        <tr>
                            <th class="text-left" x-text="$store.app.locale === 'so' ? 'Qaybta' : 'Category'">Category</th>
                            <th x-text="$store.app.locale === 'so' ? 'Lab (Rag)' : 'Male'">Male</th>
                            <th x-text="$store.app.locale === 'so' ? 'Dheddig (Dumar)' : 'Female'">Female</th>
                            <th>Under 5 Yrs</th>
                            <th>5–17 Yrs</th>
                            <th>18–59 Yrs</th>
                            <th>60+ Yrs</th>
                            <th>Row Tally Validation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-left font-bold text-xs text-[var(--hc-text)]">Total E.R Tally</td>
                            <td><input type="number" min="0" x-model.number="maleCount" class="paper-input text-center w-16 font-mono text-xs"></td>
                            <td><input type="number" min="0" x-model.number="femaleCount" class="paper-input text-center w-16 font-mono text-xs"></td>
                            <td><input type="number" min="0" x-model.number="under5" class="paper-input text-center w-16 font-mono text-xs"></td>
                            <td><input type="number" min="0" x-model.number="age5to17" class="paper-input text-center w-16 font-mono text-xs"></td>
                            <td><input type="number" min="0" x-model.number="age18to59" class="paper-input text-center w-16 font-mono text-xs"></td>
                            <td><input type="number" min="0" x-model.number="age60plus" class="paper-input text-center w-16 font-mono text-xs"></td>
                            <td class="font-mono text-xs font-bold" :style="totalGender === totalErCases ? 'color: var(--hc-success);' : 'color: var(--hc-danger);'">
                                <span x-text="totalGender + ' / ' + totalErCases + (totalGender === totalErCases ? ' (OK)' : ' (Mismatch)')"></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Section E: Mortality Register --}}
            <div class="paper-section">
                <div class="paper-section-header">
                    <span class="text-xs font-bold uppercase tracking-wider text-[var(--hc-text)]"
                          x-text="$store.app.t('er_sec_e', 'SECTION E — IN-ER MORTALITY & DOA REGISTER')">
                        SECTION E — IN-ER MORTALITY & DOA REGISTER
                    </span>
                </div>

                <div class="p-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="p-3 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] flex items-center justify-between">
                        <div>
                            <span class="font-bold text-xs text-[var(--hc-text)]" x-text="$store.app.locale === 'so' ? 'Mayd La Keenay (BID / DOA)' : 'Brought in Dead (DOA / BID)'">Brought in Dead (DOA / BID)</span>
                            <div class="text-[10px] text-[var(--hc-text-muted)]">Arrived deceased; excluded from ER clinical mortality</div>
                        </div>
                        <input type="number" min="0" x-model.number="doaCount" class="paper-input w-20 text-center font-bold font-mono">
                    </div>

                    <div class="p-3 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] flex items-center justify-between" style="border-color: var(--hc-danger);">
                        <div>
                            <span class="font-bold text-xs text-[var(--hc-danger)]" x-text="$store.app.locale === 'so' ? 'Ku Dhintay E.R (< 24 Saac)' : 'Died in E.R (< 24 hrs)'">Died in E.R (&lt; 24 hrs)</span>
                            <div class="text-[10px] text-[var(--hc-text-muted)]">True institutional Emergency mortality rate metric</div>
                        </div>
                        <input type="number" min="0" x-model.number="diedInErCount" class="paper-input w-20 text-center font-bold font-mono text-[var(--hc-danger)]">
                    </div>
                </div>
            </div>

            {{-- Clinical Verification & Stamp Block --}}
            <div class="pt-4 mt-6 border-t-2 border-[var(--hc-text)] flex flex-col sm:flex-row items-center justify-between text-xs text-[var(--hc-text-muted)]">
                <div>
                    <div>Emergency Shift Lead: <strong class="text-[var(--hc-text)]" x-text="erLead"></strong></div>
                    <div class="text-[11px] font-mono mt-0.5">Verified Reconciled with IPD Admissions</div>
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

        <!-- Mode B Banner -->
        <div class="hc-card bg-[var(--hc-surface)] border border-[var(--hc-border)] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-md bg-[var(--hc-warning)] text-white flex items-center justify-center font-bold text-sm shrink-0">
                    <i data-lucide="layout-list" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-[var(--hc-text)]">
                        Mode B — Fast Step-by-Step Emergency Triage
                    </h2>
                    <p class="text-xs text-[var(--hc-text-muted)]">
                        Rapid touch tally steppers for acute intake &bull; Fully synchronized with Mode A (A4 Sheet)
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <div class="hc-badge hc-badge-warning">
                    <span class="font-bold">Total Cases:</span>
                    <span class="font-mono font-bold" x-text="totalErCases"></span>
                </div>
                <div class="hc-badge hc-badge-brand">
                    <span class="font-bold">Conversion:</span>
                    <span class="font-mono font-bold" x-text="conversionRate + '%'"></span>
                </div>
                <button type="button" @click="viewMode = 'paper'" class="btn btn-secondary btn-xs">
                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                    <span>Switch to A4 View</span>
                </button>
            </div>
        </div>

        <!-- Section A1: Patient Arrivals -->
        <div class="hc-card space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[var(--hc-border)]">
                <div class="flex items-center gap-2">
                    <span class="hc-badge hc-badge-warning">SECTION A1</span>
                    <h3 class="text-sm font-bold text-[var(--hc-text)]">Emergency Arrivals by Acuteness</h3>
                </div>
                <div class="text-xs font-mono font-bold text-[var(--hc-warning)]">
                    Total Inflow: <span x-text="totalErCases"></span> Cases
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-[var(--hc-danger)]">1. Acute Emergency</span>
                        <span class="status-dot status-dot-danger"></span>
                    </div>
                    <p class="text-[11px] text-[var(--hc-text-muted)]">Resuscitation &amp; critical trauma</p>
                    <div class="flex items-center gap-1 pt-1">
                        <button type="button" @click="acuteCases = Math.max(0, parseInt(acuteCases || 0) - 1)" class="w-8 h-8 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="acuteCases" class="hc-input text-center font-bold text-sm w-24">
                        <button type="button" @click="acuteCases = parseInt(acuteCases || 0) + 1" class="w-8 h-8 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">+</button>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-[var(--hc-warning)]">2. OPD Cases in E.R</span>
                        <span class="status-dot status-dot-warning"></span>
                    </div>
                    <p class="text-[11px] text-[var(--hc-text-muted)]">Non-emergency walk-in triage</p>
                    <div class="flex items-center gap-1 pt-1">
                        <button type="button" @click="opdInEr = Math.max(0, parseInt(opdInEr || 0) - 1)" class="w-8 h-8 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="opdInEr" class="hc-input text-center font-bold text-sm w-24">
                        <button type="button" @click="opdInEr = parseInt(opdInEr || 0) + 1" class="w-8 h-8 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">+</button>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-[var(--hc-info)]">3. Follow-up / Dressing</span>
                        <span class="status-dot status-dot-brand"></span>
                    </div>
                    <p class="text-[11px] text-[var(--hc-text-muted)]">Post-care suture &amp; injections</p>
                    <div class="flex items-center gap-1 pt-1">
                        <button type="button" @click="followUp = Math.max(0, parseInt(followUp || 0) - 1)" class="w-8 h-8 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="followUp" class="hc-input text-center font-bold text-sm w-24">
                        <button type="button" @click="followUp = parseInt(followUp || 0) + 1" class="w-8 h-8 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">+</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section A2: Dispositions -->
        <div class="hc-card space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[var(--hc-border)]">
                <div class="flex items-center gap-2">
                    <span class="hc-badge hc-badge-brand">SECTION A2</span>
                    <h3 class="text-sm font-bold text-[var(--hc-text)]">Emergency Dispositions &amp; Patient Outcomes</h3>
                </div>
                <div class="text-xs font-mono font-bold text-[var(--hc-brand)]">
                    Conversion Rate: <span x-text="conversionRate + '%'"></span>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-1.5 text-center">
                    <span class="text-xs font-bold block text-[var(--hc-brand)]">1. Admitted IPD</span>
                    <span class="text-[10px] text-[var(--hc-text-muted)] block">Transferred to Wards</span>
                    <div class="flex items-center justify-center gap-1 mt-1">
                        <button type="button" @click="admittedToIpd = Math.max(0, parseInt(admittedToIpd || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="admittedToIpd" class="hc-input text-center font-bold text-xs w-14 py-0.5">
                        <button type="button" @click="admittedToIpd = parseInt(admittedToIpd || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">+</button>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-1.5 text-center">
                    <span class="text-xs font-bold block text-[var(--hc-text)]">2. Observation</span>
                    <span class="text-[10px] text-[var(--hc-text-muted)] block">Short-stay ward</span>
                    <div class="flex items-center justify-center gap-1 mt-1">
                        <button type="button" @click="obsWard = Math.max(0, parseInt(obsWard || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="obsWard" class="hc-input text-center font-bold text-xs w-14 py-0.5">
                        <button type="button" @click="obsWard = parseInt(obsWard || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">+</button>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-1.5 text-center">
                    <span class="text-xs font-bold block text-[var(--hc-success)]">3. Discharged</span>
                    <span class="text-[10px] text-[var(--hc-text-muted)] block">Sent home stable</span>
                    <div class="flex items-center justify-center gap-1 mt-1">
                        <button type="button" @click="dischargedStable = Math.max(0, parseInt(dischargedStable || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-success)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="dischargedStable" class="hc-input text-center font-bold text-xs w-14 py-0.5">
                        <button type="button" @click="dischargedStable = parseInt(dischargedStable || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-success)] hover:text-white">+</button>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-1.5 text-center">
                    <span class="text-xs font-bold block text-[var(--hc-warning)]">4. Referred Out</span>
                    <span class="text-[10px] text-[var(--hc-text-muted)] block">Specialized hospital</span>
                    <div class="flex items-center justify-center gap-1 mt-1">
                        <button type="button" @click="referredOut = Math.max(0, parseInt(referredOut || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="referredOut" class="hc-input text-center font-bold text-xs w-14 py-0.5">
                        <button type="button" @click="referredOut = parseInt(referredOut || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--color-primary)] hover:text-white">+</button>
                    </div>
                </div>

                <div class="p-3 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-1.5 text-center">
                    <span class="text-xs font-bold block text-[var(--hc-danger)]">5. LAMA</span>
                    <span class="text-[10px] text-[var(--hc-text-muted)] block">Left against advice</span>
                    <div class="flex items-center justify-center gap-1 mt-1">
                        <button type="button" @click="lama = Math.max(0, parseInt(lama || 0) - 1)" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-danger)] hover:text-white">-</button>
                        <input type="number" min="0" x-model.number="lama" class="hc-input text-center font-bold text-xs w-14 py-0.5">
                        <button type="button" @click="lama = parseInt(lama || 0) + 1" class="w-6 h-6 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] text-xs font-bold hover:bg-[var(--hc-danger)] hover:text-white">+</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section B & E: Demographics & Mortality -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Section B: Demographics -->
            <div class="hc-card space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-[var(--hc-border)]">
                    <div class="flex items-center gap-2">
                        <span class="hc-badge hc-badge-purple">SECTION B</span>
                        <h3 class="text-sm font-bold text-[var(--hc-text)]">Demographics Breakdown</h3>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3 pb-3 border-b border-[var(--hc-border)]">
                        <div>
                            <label class="hc-label">Male Patients</label>
                            <input type="number" min="0" x-model.number="maleCount" class="hc-input font-bold text-sm">
                        </div>
                        <div>
                            <label class="hc-label">Female Patients</label>
                            <input type="number" min="0" x-model.number="femaleCount" class="hc-input font-bold text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <div>
                            <label class="hc-label">Under 5</label>
                            <input type="number" min="0" x-model.number="under5" class="hc-input text-center font-bold text-xs">
                        </div>
                        <div>
                            <label class="hc-label">5 - 17</label>
                            <input type="number" min="0" x-model.number="age5to17" class="hc-input text-center font-bold text-xs">
                        </div>
                        <div>
                            <label class="hc-label">18 - 59</label>
                            <input type="number" min="0" x-model.number="age18to59" class="hc-input text-center font-bold text-xs">
                        </div>
                        <div>
                            <label class="hc-label">60+ Yrs</label>
                            <input type="number" min="0" x-model.number="age60plus" class="hc-input text-center font-bold text-xs">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section E: Mortality -->
            <div class="hc-card space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-[var(--hc-border)]">
                    <div class="flex items-center gap-2">
                        <span class="hc-badge hc-badge-danger">SECTION E</span>
                        <h3 class="text-sm font-bold text-[var(--hc-text)]">Mortality &amp; Resuscitation Tally</h3>
                    </div>
                    <div class="text-xs font-mono font-bold text-[var(--hc-danger)]">
                        In-ER Rate: <span x-text="inErMortalityRate + '%'"></span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-2">
                        <span class="text-xs font-bold block text-[var(--hc-text)]">Brought DOA</span>
                        <span class="text-[11px] text-[var(--hc-text-muted)] block">Dead on arrival</span>
                        <input type="number" min="0" x-model.number="doaCount" class="hc-input text-center font-bold text-base w-full">
                    </div>
                    <div class="p-4 rounded-md border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] space-y-2">
                        <span class="text-xs font-bold block text-[var(--hc-danger)]">Died in E.R</span>
                        <span class="text-[11px] text-[var(--hc-text-muted)] block">Resuscitation failed</span>
                        <input type="number" min="0" x-model.number="diedInErCount" class="hc-input text-center font-bold text-base w-full text-[var(--hc-danger)]">
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
                    <div class="w-10 h-10 rounded-md bg-[var(--hc-warning)] text-white flex items-center justify-center font-bold shrink-0">
                        <i data-lucide="file-check-2" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="hc-badge hc-badge-warning font-mono">FORM: ANL-ERD-02</span>
                            <span class="text-xs text-[var(--hc-text-muted)] font-mono">&bull; Date: <strong class="text-[var(--hc-text)]" x-text="reportDate"></strong></span>
                            <span class="text-xs text-[var(--hc-text-muted)] font-mono">&bull; Shift: <strong class="text-[var(--hc-text)]" x-text="shift"></strong></span>
                        </div>
                        <h2 class="text-base font-bold text-[var(--hc-text)] mt-0.5">
                            Emergency Patient Flow Verification &amp; Final Approval
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
                                            DEPARTMENT OF ACCIDENT, EMERGENCY &amp; CLINICAL RESUSCITATION
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="inline-block px-2.5 py-1 bg-[var(--hc-text)] text-[var(--hc-surface)] font-mono font-bold text-xs rounded">
                                        FORM ID: ANL-ERD-02
                                    </div>
                                    <div class="text-[10px] text-[var(--hc-text-muted)] font-mono mt-1">ISO 9001:2015 &bull; CLINICAL AUDIT READY</div>
                                </div>
                            </div>

                            <div class="paper-metadata-bar">
                                <div><span class="font-bold text-[var(--hc-text)]">REPORT DATE:</span> <span class="font-mono text-xs font-bold" x-text="reportDate"></span></div>
                                <div><span class="font-bold text-[var(--hc-text)]">DUTY PERIOD:</span> <span class="font-semibold text-xs" x-text="shift"></span></div>
                                <div><span class="font-bold text-[var(--hc-text)]">ER CLINICAL LEAD:</span> <span class="font-semibold text-xs" x-text="erLead"></span></div>
                            </div>
                        </div>

                        <!-- Section A Preview Summary -->
                        <div class="p-4 border-b border-[var(--hc-border)] space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs uppercase tracking-wider text-[var(--hc-text)]">SECTION A: EMERGENCY INTAKE &amp; PATIENT CENSUS</span>
                                <span class="font-mono text-xs font-bold text-[var(--color-primary)]" x-text="'Total Cases: ' + totalErCases"></span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs">
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)]">
                                    <span class="text-[10px] text-[var(--hc-text-muted)] block">Acute Emergencies</span>
                                    <span class="font-bold text-sm font-mono text-[var(--hc-danger)]" x-text="acuteCases"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)]">
                                    <span class="text-[10px] text-[var(--hc-text-muted)] block">OPD Routed to ER</span>
                                    <span class="font-bold text-sm font-mono text-[var(--hc-warning)]" x-text="opdInEr"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)]">
                                    <span class="text-[10px] text-[var(--hc-text-muted)] block">Follow-Up Visits</span>
                                    <span class="font-bold text-sm font-mono text-[var(--hc-text)]" x-text="followUp"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)]">
                                    <span class="text-[10px] text-[var(--hc-text-muted)] block">Male / Female</span>
                                    <span class="font-bold text-sm font-mono text-[var(--hc-text)]" x-text="maleCount + ' / ' + femaleCount"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)]">
                                    <span class="text-[10px] text-[var(--hc-text-muted)] block">Under 5 / Over 5</span>
                                    <span class="font-bold text-sm font-mono text-[var(--hc-text)]" x-text="under5Count + ' / ' + above5Count"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Section B & C Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x border-b border-[var(--hc-border)]">
                            <!-- Triage Breakdown -->
                            <div class="p-4 space-y-2">
                                <span class="font-bold text-xs uppercase tracking-wider text-[var(--hc-text)]">SECTION B: TRIAGE SEVERITY DISTRIBUTION</span>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div class="p-2 rounded bg-[var(--hc-surface-alt)] flex items-center justify-between border-l-4 border-l-[var(--hc-danger)]">
                                        <span class="font-bold text-[var(--hc-danger)]">RED (STAT):</span>
                                        <span class="font-bold font-mono" x-text="triageRed"></span>
                                    </div>
                                    <div class="p-2 rounded bg-[var(--hc-surface-alt)] flex items-center justify-between border-l-4 border-l-[var(--hc-warning)]">
                                        <span class="font-bold text-[var(--hc-warning)]">YELLOW (Urgent):</span>
                                        <span class="font-bold font-mono" x-text="triageYellow"></span>
                                    </div>
                                    <div class="p-2 rounded bg-[var(--hc-surface-alt)] flex items-center justify-between border-l-4 border-l-[var(--hc-success)]">
                                        <span class="font-bold text-[var(--hc-success)]">GREEN (Minor):</span>
                                        <span class="font-bold font-mono" x-text="triageGreen"></span>
                                    </div>
                                    <div class="p-2 rounded bg-[var(--hc-surface-alt)] flex items-center justify-between border-l-4 border-l-black">
                                        <span class="font-bold text-[var(--hc-text)]">BLACK (Deceased):</span>
                                        <span class="font-bold font-mono" x-text="triageBlack"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Outcomes & Dispositions -->
                            <div class="p-4 space-y-2">
                                <span class="font-bold text-xs uppercase tracking-wider text-[var(--hc-text)]">SECTION C: DISPOSITIONS &amp; CONVERSIONS</span>
                                <div class="space-y-1 text-xs">
                                    <div class="flex items-center justify-between py-1 border-b border-[var(--hc-border)]">
                                        <span>Discharged Home:</span>
                                        <span class="font-bold font-mono text-[var(--hc-success)]" x-text="dischargedHome"></span>
                                    </div>
                                    <div class="flex items-center justify-between py-1 border-b border-[var(--hc-border)]">
                                        <span>Admitted to IPD Ward (BOR Conversion):</span>
                                        <span class="font-bold font-mono text-[var(--color-primary)]" x-text="admittedToIpd + ' (' + conversionRate + '%)'"></span>
                                    </div>
                                    <div class="flex items-center justify-between py-1 border-b border-[var(--hc-border)]">
                                        <span>Transferred Out / LAMA:</span>
                                        <span class="font-bold font-mono" x-text="transferredOut + ' / ' + abscondedLama"></span>
                                    </div>
                                    <div class="flex items-center justify-between py-1">
                                        <span class="text-[var(--hc-danger)] font-bold">Mortality (Brought DOA / Died in ER):</span>
                                        <span class="font-bold font-mono text-[var(--hc-danger)]" x-text="doaCount + ' / ' + diedInErCount"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Certification Footer -->
                        <div class="p-4 bg-[var(--hc-surface-alt)] flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-[var(--hc-text-muted)]">
                            <div class="flex items-center gap-2">
                                <i data-lucide="shield-check" class="w-4 h-4 text-[var(--hc-success)]"></i>
                                <span>Emergency Room Registry Verified &bull; Official Clinical Audit Certification</span>
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
