{{-- =========================================================================
     TAB 2: LAB OPERATIONS & SHIFT HANDOVER
     ========================================================================= --}}
@php
    $labAnalyzers = $departmentForm->dropdown_options['analyzers'] ?? [
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
    $labDelayReasons = $departmentForm->dropdown_options['delay_reasons'] ?? [
        'Reagent lot calibration in progress',
        'Hemolyzed sample requiring recollection',
        'Lipemic / clotted sample recollection',
        'Specimen dilution required due to high concentration',
        'Routine maintenance / wash cycle in progress',
        'Confirmatory manual microscopy required',
        'Awaiting clinical doctor clarification',
    ];
    $labActions = [
        'Call ward nurse for specimen redraw',
        'Dilute 1:5 with saline and re-run on analyzer',
        'Dispatch specimen to referral partner lab',
        'Prioritize on immediate next batch run',
        'Doctor contacted for urgent clinical confirmation',
    ];
    $equipmentCommonIssues = [
        'Sample probe needle clot alarm',
        'Photometer absorption baseline drift',
        'Optical lamp intensity calibration error',
        'Reagent aspiration air bubble trapped',
        'Waste drain sensor blockage alert',
        'Power fluctuation / system cold reboot performed',
    ];
    $equipmentCommonActions = [
        'Probe de-proteinized with hypochlorite & back-flushed',
        'Cuvette wheel washed and photometer zeroed',
        'Reagent line primed & fluid tubing de-bubbled',
        'Electrode conditioned and 2-point calibration run',
        'Routine weekly decontamination completed',
    ];
@endphp

<!-- Fast Select Datalists for Operational Fields -->
<datalist id="lab-delay-reasons-datalist">
    @foreach($labDelayReasons as $reason)
        <option value="{{ $reason }}"></option>
    @endforeach
</datalist>

<datalist id="lab-actions-datalist">
    @foreach($labActions as $act)
        <option value="{{ $act }}"></option>
    @endforeach
</datalist>

<datalist id="lab-equipment-issues-datalist">
    @foreach($equipmentCommonIssues as $iss)
        <option value="{{ $iss }}"></option>
    @endforeach
</datalist>

<datalist id="lab-troubleshoot-actions-datalist">
    @foreach($equipmentCommonActions as $act)
        <option value="{{ $act }}"></option>
    @endforeach
</datalist>

<div class="space-y-6">

    {{-- Shift Handover & Operational Notes Banner --}}
    <div class="hc-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 mb-4 border-b border-[var(--hc-border)]">
            <div class="flex items-center gap-2">
                <span class="hc-badge hc-badge-purple">SECTION E — LAB OPERATIONS</span>
                <div>
                    <h3 class="text-sm font-bold tracking-tight text-[var(--hc-text)]">
                        Shift Handover &amp; Technologist Operational Notes
                    </h3>
                    <p class="text-xs text-[var(--hc-text-muted)]">
                        Critical communications between outgoing and incoming laboratory teams
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-[var(--hc-text-muted)]">Urgency Level:</span>
                <select x-model="operationalUrgency" class="hc-input hc-select text-xs font-bold w-40"
                        :style="operationalUrgency === 'critical' ? 'color: var(--hc-danger);' : (operationalUrgency === 'attention' ? 'color: var(--hc-warning);' : 'color: var(--hc-success);')">
                    <option value="routine">Routine / Normal</option>
                    <option value="attention">Needs Attention</option>
                    <option value="critical">Urgent / Critical Action</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="hc-label">
                    <i data-lucide="message-square" class="w-3.5 h-3.5 inline mr-1"></i>
                    Shift Handover Summary &amp; Pending Floor Events
                </label>
                <textarea x-model="shiftHandoverNotes" class="hc-textarea" rows="4"
                          placeholder="Document all incomplete runs, patient redraws, abnormal trends, or floor instructions for the next shift..."></textarea>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="hc-label">
                        <i data-lucide="user-check" class="w-3.5 h-3.5 inline mr-1"></i>
                        Incoming Shift Technologist (Relieving Staff)
                    </label>
                    <input type="text" x-model="incomingStaff" class="hc-input font-medium"
                           placeholder="Enter name of incoming lab technologist...">
                </div>
                <div>
                    <label class="hc-label">
                        <i data-lucide="shield" class="w-3.5 h-3.5 inline mr-1"></i>
                        Lab Supervisor / Manager Review Remarks
                    </label>
                    <input type="text" x-model="managerNotes" class="hc-input font-medium"
                           placeholder="Manager validation or follow-up directive...">
                </div>
            </div>
        </div>
    </div>

    {{-- Incomplete & Pending Tests Register --}}
    <div class="hc-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 mb-4 border-b border-[var(--hc-border)]">
            <div class="flex items-center gap-2">
                <span class="hc-badge hc-badge-warning">INCOMPLETE TESTS</span>
                <div>
                    <h3 class="text-sm font-bold tracking-tight text-[var(--hc-text)]">
                        Incomplete, Pending &amp; Redraw Register
                    </h3>
                    <p class="text-xs text-[var(--hc-text-muted)]">
                        Track unreleased tests with 1-click selectable reasons and actions
                    </p>
                </div>
            </div>
            <button type="button" @click="addIncompleteTest()" class="btn btn-secondary btn-sm">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Log Incomplete Test</span>
            </button>
        </div>

        <template x-if="incompleteTests.length === 0">
            <div class="p-8 text-center border border-dashed border-[var(--hc-border)] rounded-md bg-[var(--hc-surface-alt)]">
                <i data-lucide="check-circle-2" class="w-8 h-8 mx-auto text-[var(--hc-success)] mb-2"></i>
                <p class="text-xs font-semibold text-[var(--hc-text)]">Zero Pending Tests: All ordered diagnostic tests completed and authorized.</p>
                <p class="text-[11px] text-[var(--hc-text-muted)] mt-1">Click "Log Incomplete Test" if any sample requires redraw, rerun, or partner referral.</p>
            </div>
        </template>

        <template x-if="incompleteTests.length > 0">
            <div class="overflow-x-auto">
                <table class="hc-table">
                    <thead>
                        <tr>
                            <th class="w-32">Patient ID (P#)</th>
                            <th class="w-36">Sample ID (RL#)</th>
                            <th class="w-36">Patient Name</th>
                            <th class="w-40">Test &amp; Bench</th>
                            <th>Reason for Delay (Selectable)</th>
                            <th class="w-36">Current Status</th>
                            <th class="w-44">Action Required</th>
                            <th class="w-14 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(inc, idx) in incompleteTests" :key="'inc-'+idx">
                            <tr>
                                {{-- Patient ID with P- Default --}}
                                <td>
                                    <input type="text" x-model="inc.patient_id" @input="onPatientIdInput(inc)"
                                           class="hc-input font-mono text-xs font-bold" placeholder="P-XXXX">
                                </td>

                                {{-- Sample ID with RL- default and 1-Click Sync Button --}}
                                <td>
                                    <div class="space-y-1">
                                        <input type="text" x-model="inc.sample_id" @input="onSampleIdInput(inc)"
                                               class="hc-input font-mono text-xs font-bold" placeholder="RL-XXXX">
                                        <div class="flex items-center gap-1">
                                            <button type="button" @click="syncSampleId(inc)"
                                                    class="btn btn-ghost btn-xs text-[10px] py-0 px-1"
                                                    title="Use Patient ID as Sample ID">
                                                <span>= Use Pt ID</span>
                                            </button>
                                            <button type="button" @click="restoreRlSampleId(inc)"
                                                    class="btn btn-ghost btn-xs text-[10px] py-0 px-1"
                                                    title="Generate RL- Sample ID">
                                                <span>RL- ID</span>
                                            </button>
                                        </div>
                                    </div>
                                </td>

                                {{-- Patient Name --}}
                                <td>
                                    <input type="text" x-model="inc.patient_name" class="hc-input text-xs font-medium"
                                           placeholder="Full Name">
                                </td>

                                {{-- Test Requested & Bench --}}
                                <td>
                                    <input type="text" list="qc-tests-datalist" x-model="inc.test_name" class="hc-input text-xs font-semibold mb-1"
                                           placeholder="Select/Type Test">
                                    <select x-model="inc.bench" class="hc-input hc-select text-[11px] py-1">
                                        <option value="Biochemistry">Biochemistry</option>
                                        <option value="Hematology">Hematology</option>
                                        <option value="Microbiology">Microbiology</option>
                                        <option value="Serology">Serology</option>
                                        <option value="Hormones">Hormones</option>
                                        <option value="Blood Bank">Blood Bank</option>
                                    </select>
                                </td>

                                {{-- Reason for Delay (Selectable via Datalist) --}}
                                <td>
                                    <input type="text" list="lab-delay-reasons-datalist" x-model="inc.reason" class="hc-input text-xs"
                                           placeholder="Click to select delay reason">
                                </td>

                                {{-- Status Dropdown --}}
                                <td>
                                    <select x-model="inc.status" class="hc-input hc-select text-xs font-semibold">
                                        <option value="In Progress">In Progress</option>
                                        <option value="Awaiting Redraw">Awaiting Redraw</option>
                                        <option value="Pending Dilution">Pending Dilution</option>
                                        <option value="Referred to Partner">Referred to Partner</option>
                                        <option value="Doctor Clarification">Doctor Clarification</option>
                                    </select>
                                </td>

                                {{-- Action Required (Selectable via Datalist) --}}
                                <td>
                                    <input type="text" list="lab-actions-datalist" x-model="inc.action_required" class="hc-input text-xs"
                                           placeholder="Click to select action">
                                </td>

                                {{-- Remove --}}
                                <td class="text-center">
                                    <button type="button" @click="removeIncompleteTest(idx)" class="btn btn-danger btn-xs" title="Remove">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>
    </div>

    {{-- Equipment Troubleshooting & Maintenance Register --}}
    <div class="hc-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 mb-4 border-b border-[var(--hc-border)]">
            <div class="flex items-center gap-2">
                <span class="hc-badge hc-badge-danger">EQUIPMENT &amp; ANALYZERS</span>
                <div>
                    <h3 class="text-sm font-bold tracking-tight text-[var(--hc-text)]">
                        Equipment Malfunctions &amp; Troubleshooting Register
                    </h3>
                    <p class="text-xs text-[var(--hc-text-muted)]">
                        Select analyzers, alarms, and actions with zero typing needed
                    </p>
                </div>
            </div>
            <button type="button" @click="addEquipmentIssue()" class="btn btn-secondary btn-sm">
                <i data-lucide="wrench" class="w-3.5 h-3.5"></i>
                <span>Report Equipment Issue</span>
            </button>
        </div>

        <template x-if="equipmentIssues.length === 0">
            <div class="p-8 text-center border border-dashed border-[var(--hc-border)] rounded-md bg-[var(--hc-surface-alt)]">
                <i data-lucide="check-circle" class="w-8 h-8 mx-auto text-[var(--hc-success)] mb-2"></i>
                <p class="text-xs font-semibold text-[var(--hc-text)]">All Analyzers Fully Operational.</p>
                <p class="text-[11px] text-[var(--hc-text-muted)] mt-1">Mindray BA 88A, Mindray BC5000, Sysmex XP-300, Hormones analyzers, and mixers are calibrated and running smoothly.</p>
            </div>
        </template>

        <template x-if="equipmentIssues.length > 0">
            <div class="overflow-x-auto">
                <table class="hc-table">
                    <thead>
                        <tr>
                            <th class="w-48">Instrument / Analyzer</th>
                            <th class="w-64">Issue / Error Encountered</th>
                            <th>Action / Troubleshooting Performed</th>
                            <th class="w-44">Operational Status</th>
                            <th class="w-44">Biomedical Engineer</th>
                            <th class="w-14 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(eq, idx) in equipmentIssues" :key="'eq-'+idx">
                            <tr>
                                {{-- Selectable Instrument / Analyzer --}}
                                <td>
                                    <select x-model="eq.equipment_name" class="hc-input hc-select text-xs font-semibold">
                                        <option value="">-- Select Analyzer --</option>
                                        @foreach($labAnalyzers as $analyzer)
                                            <option value="{{ $analyzer }}">{{ $analyzer }}</option>
                                        @endforeach
                                    </select>
                                </td>

                                {{-- Selectable Issue Description --}}
                                <td>
                                    <input type="text" list="lab-equipment-issues-datalist" x-model="eq.issue_description" class="hc-input text-xs"
                                           placeholder="Select or enter alarm">
                                </td>

                                {{-- Selectable Troubleshooting Action --}}
                                <td>
                                    <input type="text" list="lab-troubleshoot-actions-datalist" x-model="eq.troubleshooting_action" class="hc-input text-xs"
                                           placeholder="Select or enter action performed">
                                </td>

                                {{-- Operational Status --}}
                                <td>
                                    <select x-model="eq.status" class="hc-input hc-select text-xs font-semibold">
                                        <option value="Resolved & Online">Resolved &amp; Online</option>
                                        <option value="Running with Backup">Running with Backup</option>
                                        <option value="Down / Needs Engineer">Down / Needs Engineer</option>
                                    </select>
                                </td>

                                {{-- Biomedical Engineer Notified --}}
                                <td>
                                    <select x-model="eq.engineer_notified" class="hc-input hc-select text-xs font-medium">
                                        <option value="No - Handled in-house">No - Handled in-house</option>
                                        <option value="Yes - Ticket Raised">Yes - Ticket Raised</option>
                                        <option value="Engineer On-Site">Engineer On-Site</option>
                                    </select>
                                </td>

                                {{-- Remove --}}
                                <td class="text-center">
                                    <button type="button" @click="removeEquipmentIssue(idx)" class="btn btn-danger btn-xs" title="Remove">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>
    </div>

</div>

