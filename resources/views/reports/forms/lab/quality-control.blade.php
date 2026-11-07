{{-- =========================================================================
     TAB 3: QUALITY CONTROL & ANALYZER CALIBRATIONS
     ========================================================================= --}}
@php
    $configuredAnalyzers = $departmentForm->dropdown_options['analyzers'] ?? [
        'Mindray BA 88A',
        'Mindray BC5000 CBC Analyzer',
        '107 Microscope',
        'Getein 1160 Hormones Analyzer',
        'Finecare Plus Hormones Analyzer',
        'Serology Rotation Mixer',
        'Coagulometer',
        'Mindray BS-240',
        'Sysmex XP-300',
        'ISE Electrolyte Pro',
    ];
    $configuredQcTests = $departmentForm->dropdown_options['qc_tests'] ?? [
        'Blood Glucose (Fasting / Random)',
        'Serum Creatinine (Jaffe Kinetic)',
        'Blood Urea Nitrogen (BUN) / Urea',
        'AST / SGOT (Transaminase)',
        'ALT / SGPT (Transaminase)',
        'Total Bilirubin (DSA Mod.)',
        'Direct Bilirubin',
        'Total Cholesterol (CHOD-PAP)',
        'Triglycerides (GPO-PAP)',
        'Serum Uric Acid',
        'Total Protein (Biuret)',
        'Serum Albumin (BCG)',
        'Alkaline Phosphatase (ALP)',
        'Electrolytes: Sodium (Na+ ISE)',
        'Electrolytes: Potassium (K+ ISE)',
        'Electrolytes: Chloride (Cl- ISE)',
        'CBC / 5-Part Control (Sysmex)',
        'HbA1c Glycated Hemoglobin',
        'Prothrombin Time (PT / INR)',
        'Activated Partial Thromboplastin Time (APTT)',
        'Thyroid Stimulating Hormone (TSH)',
        'Free Thyroxine (FT4)',
        'Cardiac Troponin I Quantitative',
        'D-Dimer Quantitative Assay',
        'C-Reactive Protein (CRP Quantitative)',
    ];
    $configuredCorrective = $departmentForm->dropdown_options['corrective_actions'] ?? [
        'Recalibration performed with fresh calibrator lot',
        'Cuvette / optical path washed & recalibrated',
        'Reagent vial replaced & verified with controls',
        'Electrode cleaned with conditioning solution',
        'Acceptable upon immediate single repeat',
        'Tubing de-clotted and primed',
        'Escalated to biomedical engineer / maintenance',
    ];
@endphp

<!-- Global Datalists for 1-Click Fast Selection -->
<datalist id="qc-tests-datalist">
    @foreach($configuredQcTests as $tOption)
        <option value="{{ $tOption }}"></option>
    @endforeach
</datalist>

<datalist id="qc-corrective-datalist">
    @foreach($configuredCorrective as $cOption)
        <option value="{{ $cOption }}"></option>
    @endforeach
</datalist>

<div class="space-y-6">

    {{-- QC Status & Performance Banner --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="hc-card">
            <span class="hc-label">Total QC Runs</span>
            <div class="text-2xl font-bold font-mono text-[var(--hc-text)]" x-text="calibrations.length"></div>
            <span class="text-[11px] text-[var(--hc-text-muted)]">Standard & special tests</span>
        </div>
        <div class="hc-card" style="border-left: 3px solid var(--hc-success);">
            <span class="hc-label text-[var(--hc-success)]">Passed (Dev &le; 5%)</span>
            <div class="text-2xl font-bold font-mono text-[var(--hc-success)]" x-text="qcPassedCount"></div>
            <span class="text-[11px] text-[var(--hc-text-muted)]">Approved for clinical release</span>
        </div>
        <div class="hc-card" style="border-left: 3px solid var(--hc-warning);">
            <span class="hc-label text-[var(--hc-warning)]">Drift Warning (5-10%)</span>
            <div class="text-2xl font-bold font-mono text-[var(--hc-warning)]" x-text="qcDriftCount"></div>
            <span class="text-[11px] text-[var(--hc-text-muted)]">Monitor analyzer photometer</span>
        </div>
        <div class="hc-card" style="border-left: 3px solid var(--hc-danger);">
            <span class="hc-label text-[var(--hc-danger)]">Failed / Re-run (&gt; 10%)</span>
            <div class="text-2xl font-bold font-mono text-[var(--hc-danger)]" x-text="qcFailedCount"></div>
            <span class="text-[11px] text-[var(--hc-text-muted)]">Requires corrective recalibration</span>
        </div>
    </div>

    {{-- Main Calibration Register --}}
    <div class="hc-card p-4 sm:p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 mb-4 border-b border-[var(--hc-border)]">
            <div class="flex items-center gap-2">
                <span class="hc-badge hc-badge-purple">SECTION F — QUALITY CONTROL</span>
                <div>
                    <h3 class="text-sm font-bold tracking-tight text-[var(--hc-text)]">
                        Daily Clinical Chemistry &amp; Hematology QC Register
                    </h3>
                    <p class="text-xs text-[var(--hc-text-muted)]">
                        Select analyzer and test from pre-configured lists &bull; Automatically checks deviation vs. standard absorption
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="addCalibration()" class="btn btn-secondary btn-sm">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Add Custom QC Run</span>
                </button>
            </div>
        </div>

        {{-- Fluid, Perfectly Proportional Table (No Horizontal Scroll) --}}
        <div class="w-full overflow-hidden">
            <table class="w-full table-fixed text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-[var(--hc-border)] bg-[var(--hc-surface-alt)] text-[11px] font-bold text-[var(--hc-text-muted)] uppercase tracking-wider">
                        <th class="p-2 w-[22%]">Test Name</th>
                        <th class="p-2 w-[18%]">Analyzer</th>
                        <th class="p-2 w-[8%] text-center">Std Abs</th>
                        <th class="p-2 w-[10%] text-center">Measured Abs</th>
                        <th class="p-2 w-[8%] text-center">Dev %</th>
                        <th class="p-2 w-[10%] text-center">QC Status</th>
                        <th class="p-2 w-[11%]">Lot # / Staff</th>
                        <th class="p-2 w-[9%]">Corrective Action</th>
                        <th class="p-2 w-[4%] text-center">Del</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--hc-border)]">
                    <template x-for="(cal, idx) in calibrations" :key="'cal-'+idx">
                        <tr :style="cal.qc_status === 'failed' ? 'background: color-mix(in srgb, var(--hc-danger) 5%, transparent);' : (cal.qc_status === 'drift' ? 'background: color-mix(in srgb, var(--hc-warning) 5%, transparent);' : '')">
                            {{-- Test Name (Selectable with Datalist + Custom Entry) --}}
                            <td class="p-1.5">
                                <input type="text" list="qc-tests-datalist" x-model="cal.test_name"
                                       class="hc-input text-xs font-semibold w-full py-1.5 px-2"
                                       placeholder="Select or enter test">
                            </td>

                            {{-- Selectable Analyzer Dropdown --}}
                            <td class="p-1.5">
                                <select x-model="cal.analyzer" class="hc-input hc-select text-xs font-medium w-full py-1.5 px-2 truncate">
                                    <template x-if="cal.analyzer && !{{ json_encode($configuredAnalyzers) }}.includes(cal.analyzer)">
                                        <option :value="cal.analyzer" x-text="cal.analyzer" selected></option>
                                    </template>
                                    @foreach($configuredAnalyzers as $analyzerName)
                                        <option value="{{ $analyzerName }}">{{ $analyzerName }}</option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- Standard Absorption --}}
                            <td class="p-1.5 text-center font-mono text-xs font-bold text-[var(--hc-text)]">
                                <span x-text="cal.standard_absorption"></span>
                            </td>

                            {{-- Measured Absorption (Technologist Input) --}}
                            <td class="p-1.5 text-center">
                                <input type="number" step="0.001" x-model="cal.measured_absorption"
                                       @input="checkQcStatus(cal)"
                                       class="hc-input font-mono font-bold text-xs text-center w-full py-1.5 px-1"
                                       placeholder="0.000">
                            </td>

                            {{-- Deviation Percentage --}}
                            <td class="p-1.5 text-center font-mono text-xs">
                                <span x-show="cal.measured_absorption && cal.measured_absorption !== ''"
                                      :style="cal.qc_status === 'failed' ? 'color: var(--hc-danger); font-weight: bold;' : (cal.qc_status === 'drift' ? 'color: var(--hc-warning); font-weight: bold;' : 'color: var(--hc-success); font-weight: bold;')"
                                      x-text="(((Math.abs(parseFloat(cal.measured_absorption) - parseFloat(cal.standard_absorption))) / parseFloat(cal.standard_absorption)) * 100).toFixed(1) + '%'">
                                </span>
                                <span x-show="!cal.measured_absorption || cal.measured_absorption === ''" class="text-[var(--hc-text-muted)]">-</span>
                            </td>

                            {{-- Auto Evaluated QC Status --}}
                            <td class="p-1.5 text-center">
                                <span x-show="cal.qc_status === 'passed'" class="hc-badge hc-badge-success text-[10px] py-0.5 px-1.5 inline-flex items-center gap-1">
                                    <i data-lucide="check" class="w-3 h-3"></i> Passed
                                </span>
                                <span x-show="cal.qc_status === 'drift'" class="hc-badge hc-badge-warning text-[10px] py-0.5 px-1.5 inline-flex items-center gap-1">
                                    <i data-lucide="alert-triangle" class="w-3 h-3"></i> Drift
                                </span>
                                <span x-show="cal.qc_status === 'failed'" class="hc-badge hc-badge-danger text-[10px] py-0.5 px-1.5 inline-flex items-center gap-1">
                                    <i data-lucide="x" class="w-3 h-3"></i> Failed
                                </span>
                                <span x-show="!cal.qc_status || cal.qc_status === ''" class="hc-badge hc-badge-muted text-[10px] py-0.5 px-1.5">
                                    Pending
                                </span>
                            </td>

                            {{-- Calibrator Lot # & Calibrated By Compact --}}
                            <td class="p-1.5">
                                <input type="text" x-model="cal.calibrator_lot" class="hc-input font-mono text-[11px] py-1 px-1.5 w-full" placeholder="CAL-LOT-XX">
                                <div class="text-[9px] font-mono text-[var(--hc-text-muted)] truncate mt-0.5"
                                     x-text="(cal.calibrated_by || currentUser) + ' • ' + (cal.calibration_date || reportDate).substring(0, 16)">
                                </div>
                            </td>

                            {{-- Corrective Action (Selectable via Datalist) --}}
                            <td class="p-1.5">
                                <input type="text" list="qc-corrective-datalist" x-model="cal.corrective_action"
                                       class="hc-input text-[11px] py-1.5 px-2 w-full truncate"
                                       :placeholder="cal.qc_status === 'failed' ? 'Required: action' : 'Optional remarks'">
                            </td>

                            {{-- Remove Action --}}
                            <td class="p-1.5 text-center">
                                <button type="button" @click="removeCalibration(idx)" class="btn btn-danger btn-xs py-1 px-1.5" title="Remove calibration row">
                                    <i data-lucide="trash-2" class="w-3 h-3"></i>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

</div>

