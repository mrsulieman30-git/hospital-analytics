@extends('layouts.app')

@section('content')
@php
    $defaultStandardTests = [
        ['test_name' => 'Blood Glucose (Fasting / Random)', 'analyzer' => 'Mindray BA 88A', 'standard_absorption' => 0.385],
        ['test_name' => 'Serum Creatinine (Jaffe Kinetic)', 'analyzer' => 'Mindray BA 88A', 'standard_absorption' => 0.290],
        ['test_name' => 'Blood Urea Nitrogen (BUN) / Urea', 'analyzer' => 'Mindray BA 88A', 'standard_absorption' => 0.415],
        ['test_name' => 'AST / SGOT (Transaminase)', 'analyzer' => 'Mindray BA 88A', 'standard_absorption' => 0.175],
        ['test_name' => 'ALT / SGPT (Transaminase)', 'analyzer' => 'Mindray BA 88A', 'standard_absorption' => 0.185],
        ['test_name' => 'Total Bilirubin (DSA Mod.)', 'analyzer' => 'Mindray BA 88A', 'standard_absorption' => 0.520],
        ['test_name' => 'Direct Bilirubin', 'analyzer' => 'Mindray BA 88A', 'standard_absorption' => 0.470],
        ['test_name' => 'Total Cholesterol (CHOD-PAP)', 'analyzer' => 'Mindray BA 88A', 'standard_absorption' => 0.345],
        ['test_name' => 'Triglycerides (GPO-PAP)', 'analyzer' => 'Mindray BA 88A', 'standard_absorption' => 0.315],
        ['test_name' => 'Serum Uric Acid', 'analyzer' => 'Mindray BA 88A', 'standard_absorption' => 0.265],
        ['test_name' => 'Total Protein (Biuret)', 'analyzer' => 'Mindray BA 88A', 'standard_absorption' => 0.440],
        ['test_name' => 'Serum Albumin (BCG)', 'analyzer' => 'Mindray BA 88A', 'standard_absorption' => 0.395],
        ['test_name' => 'Alkaline Phosphatase (ALP)', 'analyzer' => 'Mindray BA 88A', 'standard_absorption' => 0.210],
        ['test_name' => 'Electrolytes: Sodium (Na+ ISE)', 'analyzer' => 'ISE Electrolyte Pro', 'standard_absorption' => 140.0],
        ['test_name' => 'Electrolytes: Potassium (K+ ISE)', 'analyzer' => 'ISE Electrolyte Pro', 'standard_absorption' => 4.00],
        ['test_name' => 'Electrolytes: Chloride (Cl- ISE)', 'analyzer' => 'ISE Electrolyte Pro', 'standard_absorption' => 100.0],
        ['test_name' => 'CBC / 5-Part Control (Sysmex)', 'analyzer' => 'Mindray BC5000 CBC Analyzer', 'standard_absorption' => 1.000],
        ['test_name' => 'HbA1c Glycated Hemoglobin', 'analyzer' => 'Mindray BA 88A', 'standard_absorption' => 0.145],
    ];

    // For new day: standard tests are ready with standard absorption, but measured absorption and qc_status remain blank
    $cleanCalibrations = [];
    foreach ($defaultStandardTests as $test) {
        $cleanCalibrations[] = [
            'test_name' => $test['test_name'],
            'analyzer' => $test['analyzer'],
            'standard_absorption' => $test['standard_absorption'],
            'measured_absorption' => null,
            'calibrator_lot' => '',
            'qc_status' => '',
            'calibrated_by' => $report->lead_staff_name ?? auth()->user()->name,
            'calibration_date' => $date,
            'corrective_action' => '',
        ];
    }

    $initialCalibrations = $report?->form_data['section_f_quality_control']['calibrations'] ?? $cleanCalibrations;
    $initialIncomplete = $report?->form_data['section_e_lab_operations']['incomplete_tests'] ?? [];
    $initialTroubleshooting = $report?->form_data['section_e_lab_operations']['equipment_troubleshooting'] ?? [];
    $initialReceived = $report?->form_data['section_g_inventory_supplies']['received_items'] ?? [];
    $initialPartners = $report?->form_data['section_b_partners'] ?? [];
    $initialNilItems = $report?->form_data['section_d_nil_items']['items'] ?? [];

    // Master Catalog & 13 Lab Types from LMS
    $labCatalog = \App\Services\LmsLabReportParserService::MASTER_TEST_CATALOG;
    $labTypesMeta = \App\Services\LmsLabReportParserService::LAB_TYPES;

    $savedTests = $report?->form_data['section_c_benches_and_quality']['tests'] ?? [];
    $savedTestMap = [];
    foreach ($savedTests as $st) {
        if (!empty($st['test_name'])) {
            $savedTestMap[strtoupper(trim($st['test_name']))] = (int)($st['count'] ?? 0);
        }
    }

    $initialCatalogTests = [];
    foreach ($labCatalog as $tName => $tType) {
        $initialCatalogTests[] = [
            'test_name' => $tName,
            'lab_type' => $tType,
            'lab_type_name' => $labTypesMeta[$tType]['short'] ?? $tType,
            'count' => $savedTestMap[$tName] ?? 0,
        ];
    }
@endphp

<div x-data="{
    viewMode: 'paper',
    // Active section: 'daily-report', 'operations', 'quality-control', 'inventory'
    activeSection: (new URLSearchParams(window.location.search).get('section')) || 'daily-report',

    setSection(sec) {
        this.activeSection = sec;
        const url = new URL(window.location.href);
        url.searchParams.set('section', sec);
        window.history.replaceState({}, '', url.toString());
        this.$dispatch('lab-section-changed', sec);
        this.$nextTick(() => {
            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                window.lucide.createIcons();
            }
        });
    },

    reportDate: '{{ $date }}',
    shift: '{{ $shift ?? '24-Hour Daily Summary' }}',
    labLead: '{{ $report->lead_staff_name ?? auth()->user()->name }}',
    currentUser: '{{ auth()->user()->name }}',

    // Section A: Volume by Source (Defaults to 0 on new days)
    opdPts: {{ $report->form_data['section_a_volume']['opd_patients'] ?? 0 }},
    opdTests: {{ $report->form_data['section_a_volume']['opd_tests'] ?? 0 }},
    erPts: {{ $report->form_data['section_a_volume']['er_patients'] ?? 0 }},
    erTests: {{ $report->form_data['section_a_volume']['er_tests'] ?? 0 }},
    ipdPts: {{ $report->form_data['section_a_volume']['ipd_patients'] ?? 0 }},
    ipdTests: {{ $report->form_data['section_a_volume']['ipd_tests'] ?? 0 }},
    walkinPts: {{ $report->form_data['section_a_volume']['outside_walkin_pts'] ?? 0 }},
    partnerPts: {{ $report->form_data['section_a_volume']['outside_partner_pts'] ?? 0 }},
    outsideTests: {{ $report->form_data['section_a_volume']['outside_tests'] ?? 0 }},

    // Section B: B2B Partners (Empty on new days)
    partners: {{ json_encode($initialPartners, JSON_UNESCAPED_SLASHES) }},

    // Section C: Benches & Quality (13 LMS Specialties)
    benchHematology: {{ $report->form_data['section_c_benches_and_quality']['hematology'] ?? ($report->form_data['section_c_benches_and_quality']['haematology'] ?? 0) }},
    benchBiochemistry: {{ $report->form_data['section_c_benches_and_quality']['biochemistry'] ?? 0 }},
    benchSerology: {{ $report->form_data['section_c_benches_and_quality']['serology_immunology'] ?? ($report->form_data['section_c_benches_and_quality']['serology'] ?? 0) }},
    benchMicrobiology: {{ $report->form_data['section_c_benches_and_quality']['microbiology_parasitology'] ?? 0 }},
    benchHormones: {{ $report->form_data['section_c_benches_and_quality']['hormones_special'] ?? ($report->form_data['section_c_benches_and_quality']['hormones'] ?? 0) }},
    benchBloodBank: {{ $report->form_data['section_c_benches_and_quality']['blood_bank'] ?? 0 }},
    benchUrine: {{ $report->form_data['section_c_benches_and_quality']['urine'] ?? 0 }},
    benchStool: {{ $report->form_data['section_c_benches_and_quality']['stool'] ?? 0 }},
    benchQuantitative: {{ $report->form_data['section_c_benches_and_quality']['quantitative_test'] ?? 0 }},
    benchCoagulation: {{ $report->form_data['section_c_benches_and_quality']['coagulation_profile'] ?? 0 }},
    benchSemen: {{ $report->form_data['section_c_benches_and_quality']['semen_analysis'] ?? 0 }},
    benchTb: {{ $report->form_data['section_c_benches_and_quality']['tb_analysis'] ?? 0 }},
    benchBodyFluids: {{ $report->form_data['section_c_benches_and_quality']['body_fluids'] ?? 0 }},
    sampleRejections: {{ $report->form_data['section_c_benches_and_quality']['sample_rejections'] ?? 0 }},
    delayedTat: {{ $report->form_data['section_c_benches_and_quality']['delayed_tests_tat'] ?? 0 }},
    criticalValues: {{ $report->form_data['section_c_benches_and_quality']['critical_values_reported'] ?? 0 }},

    // Individual Tests & Smart Filtering State
    allCatalogTests: {{ json_encode($initialCatalogTests, JSON_UNESCAPED_SLASHES) }},
    labTypesMeta: {{ json_encode($labTypesMeta, JSON_UNESCAPED_SLASHES) }},
    testSearchTerm: '',
    testFilterType: 'all',
    showAllTests: false,
    isUploadingLms: false,
    openDateMismatchModal: false,
    mismatchInfo: {
        extractedDate: '',
        systemDate: '',
        warning: '',
        pendingResult: null
    },
    lmsSuccessToast: '',

    // Section D: Nil Items (Empty on new days)
    nilItems: {{ json_encode($initialNilItems, JSON_UNESCAPED_SLASHES) }},

    // Section E: Lab Operations & Shift Handover (Empty on new days)
    incompleteTests: {{ json_encode($initialIncomplete, JSON_UNESCAPED_SLASHES) }},
    equipmentIssues: {{ json_encode($initialTroubleshooting, JSON_UNESCAPED_SLASHES) }},
    shiftHandoverNotes: '{{ addslashes($report->form_data['section_e_lab_operations']['shift_handover']['shift_notes'] ?? '') }}',
    incomingStaff: '{{ addslashes($report->form_data['section_e_lab_operations']['shift_handover']['incoming_staff'] ?? '') }}',
    managerNotes: '{{ addslashes($report->form_data['section_e_lab_operations']['shift_handover']['manager_notes'] ?? '') }}',
    operationalUrgency: '{{ $report->form_data['section_e_lab_operations']['shift_handover']['urgency_level'] ?? 'routine' }}',

    // Section F: Quality Control & Calibration Register (Standard tests listed, measurements clean)
    calibrations: {{ json_encode($initialCalibrations, JSON_UNESCAPED_SLASHES) }},

    // Section G: Laboratory Inventory & Received Supplies (Empty on new days)
    receivedSupplies: {{ json_encode($initialReceived, JSON_UNESCAPED_SLASHES) }},

    // Available Units for Supplies
    unitOptions: ['Box', 'Kit', 'Bottle', 'Piece', 'Bag', 'Pack', 'Vial', 'Roll', 'Carton'],

    // Calendar Navigation Methods
    navigateToDate(targetDate) {
        if (!targetDate || targetDate === '{{ $date }}') return;
        const url = new URL('{{ route("reports.create") }}', window.location.origin);
        url.searchParams.set('department', 'laboratory');
        url.searchParams.set('date', targetDate);
        url.searchParams.set('section', this.activeSection);
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
            url.searchParams.set('department', 'laboratory');
            url.searchParams.set('date', this.reportDate);
            url.searchParams.set('shift', newShift);
            url.searchParams.set('section', this.activeSection);
            window.location.href = url.toString();
        @endif
    },

    // Patient ID & Sample ID Methods
    // Rule: Patient IDs start with 'P-', Sample IDs start with 'RL-'
    addIncompleteTest() {
        const randNum = Math.floor(1000 + Math.random() * 9000);
        this.incompleteTests.push({
            patient_id: 'P-' + randNum,
            sample_id: 'RL-' + randNum,
            patient_name: '',
            test_name: '',
            bench: 'Biochemistry',
            reason: '',
            status: 'In Progress',
            action_required: '',
            sync_with_patient: false
        });
        this.$nextTick(() => window.lucide?.createIcons());
    },
    removeIncompleteTest(index) {
        this.incompleteTests.splice(index, 1);
    },
    syncSampleId(inc) {
        inc.sample_id = inc.patient_id;
        inc.sync_with_patient = true;
    },
    restoreRlSampleId(inc) {
        const num = inc.patient_id ? inc.patient_id.replace(/^P-?/i, '') : Math.floor(1000 + Math.random() * 9000);
        inc.sample_id = 'RL-' + num;
        inc.sync_with_patient = false;
    },
    onPatientIdInput(inc) {
        if (inc.patient_id && !inc.patient_id.toUpperCase().startsWith('P')) {
            inc.patient_id = 'P-' + inc.patient_id.replace(/^[^\w]*/, '');
        }
        if (inc.sync_with_patient) {
            inc.sample_id = inc.patient_id;
        }
    },
    onSampleIdInput(inc) {
        if (inc.sample_id && !inc.sample_id.toUpperCase().startsWith('RL') && !inc.sample_id.toUpperCase().startsWith('P')) {
            inc.sample_id = 'RL-' + inc.sample_id.replace(/^[^\w]*/, '');
        }
        if (inc.sample_id !== inc.patient_id) {
            inc.sync_with_patient = false;
        }
    },

    addEquipmentIssue() {
        this.equipmentIssues.push({
            equipment_name: '',
            issue_description: '',
            troubleshooting_action: '',
            status: 'Resolved & Online',
            engineer_notified: 'No - Handled in-house'
        });
        this.$nextTick(() => window.lucide?.createIcons());
    },
    removeEquipmentIssue(index) {
        this.equipmentIssues.splice(index, 1);
    },

    addCalibration() {
        const now = new Date();
        const timeStr = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
        this.calibrations.push({
            test_name: '',
            analyzer: 'Mindray BA 88A',
            standard_absorption: 0.300,
            measured_absorption: null,
            calibrator_lot: 'CAL-' + this.reportDate.substring(2, 4) + '-01',
            qc_status: '',
            calibrated_by: this.currentUser,
            calibration_date: this.reportDate + ' ' + timeStr,
            corrective_action: ''
        });
        this.$nextTick(() => window.lucide?.createIcons());
    },
    removeCalibration(index) {
        this.calibrations.splice(index, 1);
    },
    checkQcStatus(cal) {
        if (cal.measured_absorption === null || cal.measured_absorption === '' || isNaN(parseFloat(cal.measured_absorption))) {
            cal.qc_status = '';
            return;
        }
        const diff = Math.abs(parseFloat(cal.measured_absorption) - parseFloat(cal.standard_absorption));
        const devPct = (diff / parseFloat(cal.standard_absorption)) * 100;
        if (devPct <= 5.0) {
            cal.qc_status = 'passed';
        } else if (devPct <= 10.0) {
            cal.qc_status = 'drift';
        } else {
            cal.qc_status = 'failed';
        }
    },

    addReceivedSupply() {
        const now = new Date();
        const timeStr = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
        this.receivedSupplies.push({
            item_name: '',
            category: 'Biochemistry Reagent',
            brand: '',
            quantity: 1,
            unit: 'Box',
            lot_number: 'LOT-' + Math.floor(1000 + Math.random() * 9000),
            expiry_date: '',
            storage_condition: 'Cold Chain (2°C - 8°C)',
            package_condition: 'Intact & Cold Chain Maintained',
            who_received: this.currentUser,
            received_date: this.reportDate + ' ' + timeStr,
            supplier_name: '',
            supplier_representative: '',
            invoice_po_ref: '',
            acceptance_status: 'Accepted & Stocked'
        });
        this.$nextTick(() => window.lucide?.createIcons());
    },
    removeReceivedSupply(index) {
        this.receivedSupplies.splice(index, 1);
    },

    addNilItem() {
        this.nilItems.push({
            item_name: '',
            bench: 'Biochemistry',
            reason: '',
            expected_restock: ''
        });
        this.$nextTick(() => window.lucide?.createIcons());
    },
    removeNilItem(index) {
        this.nilItems.splice(index, 1);
    },

    addPartner() {
        this.partners.push({
            partner: '',
            patients: 0,
            tests: 0
        });
        this.$nextTick(() => window.lucide?.createIcons());
    },
    removePartner(index) {
        this.partners.splice(index, 1);
    },

    // Computed Properties
    get totalOutsidePts() {
        return parseInt(this.walkinPts || 0) + parseInt(this.partnerPts || 0);
    },
    get totalLabPatients() {
        return parseInt(this.opdPts || 0) + parseInt(this.erPts || 0) + parseInt(this.ipdPts || 0) + this.totalOutsidePts;
    },
    get totalLabTests() {
        return parseInt(this.opdTests || 0) + parseInt(this.erTests || 0) + parseInt(this.ipdTests || 0) + parseInt(this.outsideTests || 0);
    },
    get testsPerPatientRatio() {
        return this.totalLabPatients > 0 ? (this.totalLabTests / this.totalLabPatients).toFixed(2) : 0;
    },
    get partnerShare() {
        return this.totalLabPatients > 0 ? ((this.partnerPts / this.totalLabPatients) * 100).toFixed(1) : 0;
    },

    get totalBenchTests() {
        return parseInt(this.benchHematology || 0) +
            parseInt(this.benchBiochemistry || 0) +
            parseInt(this.benchSerology || 0) +
            parseInt(this.benchMicrobiology || 0) +
            parseInt(this.benchHormones || 0) +
            parseInt(this.benchBloodBank || 0) +
            parseInt(this.benchUrine || 0) +
            parseInt(this.benchStool || 0) +
            parseInt(this.benchQuantitative || 0) +
            parseInt(this.benchCoagulation || 0) +
            parseInt(this.benchSemen || 0) +
            parseInt(this.benchTb || 0) +
            parseInt(this.benchBodyFluids || 0);
    },

    get orderedTests() {
        return this.allCatalogTests.filter(t => parseInt(t.count || 0) > 0);
    },

    get visibleTests() {
        let list = this.showAllTests ? this.allCatalogTests : this.orderedTests;
        if (this.testFilterType !== 'all') {
            list = list.filter(t => t.lab_type === this.testFilterType);
        }
        if (this.testSearchTerm.trim()) {
            const q = this.testSearchTerm.toLowerCase();
            list = list.filter(t => t.test_name.toLowerCase().includes(q) || (t.lab_type_name && t.lab_type_name.toLowerCase().includes(q)));
        }
        return list;
    },

    updateTestCount(testName, count) {
        const item = this.allCatalogTests.find(t => t.test_name === testName);
        if (item) {
            item.count = Math.max(0, parseInt(count || 0));
            this.recalculateBenchFromTests();
        }
    },

    recalculateBenchFromTests() {
        const totals = {};
        for (const k in this.labTypesMeta) {
            totals[k] = 0;
        }
        this.allCatalogTests.forEach(t => {
            const cnt = parseInt(t.count || 0);
            totals[t.lab_type] = (totals[t.lab_type] || 0) + cnt;
        });
        this.benchHematology = totals['haematology'] || 0;
        this.benchBiochemistry = totals['biochemistry'] || 0;
        this.benchSerology = totals['serology'] || 0;
        this.benchHormones = totals['hormones'] || 0;
        this.benchUrine = totals['urine'] || 0;
        this.benchStool = totals['stool'] || 0;
        this.benchQuantitative = totals['quantitative_test'] || 0;
        this.benchCoagulation = totals['coagulation_profile'] || 0;
        this.benchMicrobiology = totals['microbiology_parasitology'] || 0;
        this.benchBloodBank = totals['blood_bank'] || 0;
        this.benchSemen = totals['semen_analysis'] || 0;
        this.benchTb = totals['tb_analysis'] || 0;
        this.benchBodyFluids = totals['body_fluids'] || 0;
    },

    triggerLmsUpload() {
        if (this.$refs.lmsFileInput) {
            this.$refs.lmsFileInput.click();
        }
    },

    async handleLmsFileUpload(e) {
        const file = e.target.files[0];
        if (!file) return;

        this.isUploadingLms = true;
        const formData = new FormData();
        formData.append('lms_file', file);
        formData.append('current_date', this.reportDate);
        formData.append('_token', '{{ csrf_token() }}');

        try {
            const res = await fetch('{{ route("reports.lab.parse-lms") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'Error parsing LMS report');
                return;
            }

            if (!data.date_matches && data.date_warning) {
                this.mismatchInfo = {
                    extractedDate: data.extracted_date,
                    systemDate: data.system_date,
                    warning: data.date_warning,
                    pendingResult: data
                };
                this.openDateMismatchModal = true;
            } else {
                this.applyLmsParsedData(data);
            }
        } catch (err) {
            console.error(err);
            alert('Failed to parse uploaded LMS report file.');
        } finally {
            this.isUploadingLms = false;
            e.target.value = '';
        }
    },

    applyLmsParsedData(data) {
        if (!data) return;

        if (data.bench_totals) {
            this.benchHematology = data.bench_totals.haematology || 0;
            this.benchBiochemistry = data.bench_totals.biochemistry || 0;
            this.benchSerology = data.bench_totals.serology || 0;
            this.benchHormones = data.bench_totals.hormones || 0;
            this.benchUrine = data.bench_totals.urine || 0;
            this.benchStool = data.bench_totals.stool || 0;
            this.benchQuantitative = data.bench_totals.quantitative_test || 0;
            this.benchCoagulation = data.bench_totals.coagulation_profile || 0;
            this.benchMicrobiology = data.bench_totals.microbiology_parasitology || 0;
            this.benchBloodBank = data.bench_totals.blood_bank || 0;
            this.benchSemen = data.bench_totals.semen_analysis || 0;
            this.benchTb = data.bench_totals.tb_analysis || 0;
            this.benchBodyFluids = data.bench_totals.body_fluids || 0;
        }

        if (data.all_tests) {
            const map = {};
            data.all_tests.forEach(t => {
                map[t.test_name.toUpperCase()] = parseInt(t.count || 0);
            });

            this.allCatalogTests.forEach(item => {
                const k = item.test_name.toUpperCase();
                if (map[k] !== undefined) {
                    item.count = map[k];
                } else {
                    item.count = 0;
                }
            });
        }

        if (data.total_tests_examined) {
            if (this.totalLabTests === 0 || this.opdTests === 0) {
                this.opdTests = data.total_tests_examined;
            }
        }

        this.openDateMismatchModal = false;
        this.lmsSuccessToast = '✓ LMS shift report imported successfully: ' + (data.ordered_tests_count || 0) + ' active test orders loaded (' + Number(data.total_tests_examined || 0).toLocaleString() + ' total tests).';
        setTimeout(() => { this.lmsSuccessToast = ''; }, 7000);
        this.$nextTick(() => {
            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                window.lucide.createIcons();
            }
        });
    },

    confirmMismatchImport() {
        if (this.mismatchInfo.pendingResult) {
            this.applyLmsParsedData(this.mismatchInfo.pendingResult);
        }
    },
    // Autosave State & Methods
    openA4Preview: false,
    autosaveStatus: 'idle',
    lastAutosavedAt: null,
    autosaveKey: 'kfh_autosave_lab_{{ $date }}',

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
                opdPts: this.opdPts,
                opdTests: this.opdTests,
                erPts: this.erPts,
                erTests: this.erTests,
                ipdPts: this.ipdPts,
                ipdTests: this.ipdTests,
                walkinPts: this.walkinPts,
                partnerPts: this.partnerPts,
                outsideTests: this.outsideTests,
                partners: this.partners,
                benchHematology: this.benchHematology,
                benchBiochemistry: this.benchBiochemistry,
                benchSerology: this.benchSerology,
                benchMicrobiology: this.benchMicrobiology,
                benchHormones: this.benchHormones,
                benchBloodBank: this.benchBloodBank,
                sampleRejections: this.sampleRejections,
                delayedTat: this.delayedTat,
                criticalValues: this.criticalValues,
                nilItems: this.nilItems,
                incompleteTests: this.incompleteTests,
                equipmentIssues: this.equipmentIssues,
                shiftHandoverNotes: this.shiftHandoverNotes,
                incomingStaff: this.incomingStaff,
                managerNotes: this.managerNotes,
                operationalUrgency: this.operationalUrgency,
                calibrations: this.calibrations,
                receivedSupplies: this.receivedSupplies,
                shift: this.shift,
                labLead: this.labLead
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
        const form = document.getElementById('lab-submit-form');
        if (form) {
            form.status.value = 'submitted';
            form.submit();
        }
    },

    saveDraft() {
        this.clearAutosave();
        const form = document.getElementById('lab-submit-form');
        if (form) {
            form.status.value = 'draft';
            form.submit();
        }
    },

    get qcPassedCount() {
        return this.calibrations.filter(c => c.qc_status === 'passed').length;
    },
    get qcDriftCount() {
        return this.calibrations.filter(c => c.qc_status === 'drift').length;
    },
    get qcFailedCount() {
        return this.calibrations.filter(c => c.qc_status === 'failed').length;
    }
}"
@input.debounce.1000ms="performAutosave()" @change.debounce.1000ms="performAutosave()"
x-on:switch-lab-tab.window="setSection($event.detail)"
class="max-w-7xl mx-auto space-y-6">

    {{-- ═══════════════════════════════════════════════════════════
         UNIFIED SECONDARY CONTROL HEADER & SECTION NAVIGATION
         ═══════════════════════════════════════════════════════════ --}}
    <div class="no-print hc-card p-3 lg:p-4 space-y-3 shadow-xs">

        {{-- Row 1: Department Identification, Autosave & Primary Actions --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-lg bg-[var(--hc-purple)] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                    <i data-lucide="flask-conical" class="w-4 h-4"></i>
                </div>
                <div class="flex items-center gap-2 min-w-0 flex-wrap">
                    <span class="hc-badge hc-badge-purple font-mono font-bold text-xs shrink-0">ANL-LAB-04</span>
                    <h1 class="text-sm lg:text-base font-bold text-[var(--hc-text)] tracking-tight">
                        Laboratory Operations &amp; Daily Diagnostics
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
            {{-- Left: Interactive Calendar Controls + Shift + Lead Staff --}}
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
                    <span class="text-[var(--hc-text-muted)] text-[11px] font-semibold hidden lg:inline">Lab Lead:</span>
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs font-bold text-slate-800 dark:text-slate-100">
                        <i data-lucide="user-check" class="w-3.5 h-3.5 text-emerald-500"></i>
                        <span>{{ $report->lead_staff_name ?? auth()->user()->name }}</span>
                        <span class="text-[10px] text-slate-400 font-mono font-normal">(Automated)</span>
                    </div>
                </div>
            </div>

            {{-- Right: Live Metric Badges --}}
            <div class="flex flex-wrap items-center gap-1.5">
                <div class="hc-badge hc-badge-purple text-xs py-0.5" title="Total diagnostic tests">
                    <i data-lucide="activity" class="w-3 h-3"></i>
                    <span x-text="totalLabTests + ' Tests (' + testsPerPatientRatio + 'x/pt)'"></span>
                </div>
                <div class="hc-badge hc-badge-warning text-xs py-0.5" title="Incomplete tests requiring action">
                    <span class="status-dot status-dot-warning"></span>
                    <span x-text="incompleteTests.length + ' Incomplete'"></span>
                </div>
                <div class="hc-badge hc-badge-success text-xs py-0.5" title="Quality control calibrations passed">
                    <span class="status-dot status-dot-success"></span>
                    <span x-text="qcPassedCount + '/' + calibrations.length + ' QC Pass'"></span>
                </div>
                <div class="hc-badge hc-badge-brand text-xs py-0.5" title="Supplies received today">
                    <span class="status-dot status-dot-brand"></span>
                    <span x-text="receivedSupplies.length + ' Supplies'"></span>
                </div>
            </div>
        </div>

        {{-- Row 3: Integrated Department Section Navigation Tabs --}}
        <div class="flex items-center gap-1.5 pt-2 border-t border-[var(--hc-border)] overflow-x-auto">
            <button type="button" @click="setSection('daily-report')"
                    class="flex items-center gap-2 px-3 py-1.5 text-xs font-bold rounded-md transition whitespace-nowrap cursor-pointer"
                    :style="activeSection === 'daily-report' ? 'background: var(--color-primary); color: #FFFFFF;' : 'color: var(--hc-text-muted); background: var(--hc-surface-alt);'">
                <i data-lucide="bar-chart-2" class="w-3.5 h-3.5"></i>
                <span x-text="$store.app.t('daily_report', 'Daily Report')">Daily Report</span>
                <span class="text-[10px] font-mono px-1.5 py-0.5 rounded"
                      :style="activeSection === 'daily-report' ? 'background: rgba(255,255,255,0.25); color: #FFFFFF;' : 'background: var(--hc-surface); color: var(--hc-text-muted);'"
                      x-text="totalLabTests + ' tests'"></span>
            </button>

            <button type="button" @click="setSection('operations')"
                    class="flex items-center gap-2 px-3 py-1.5 text-xs font-bold rounded-md transition whitespace-nowrap cursor-pointer"
                    :style="activeSection === 'operations' ? 'background: var(--color-primary); color: #FFFFFF;' : 'color: var(--hc-text-muted); background: var(--hc-surface-alt);'">
                <i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i>
                <span x-text="$store.app.t('lab_operations', 'Lab Operations & Handover')">Lab Operations</span>
                <span x-show="incompleteTests.length > 0" class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-500 font-bold"
                      x-text="incompleteTests.length + ' pending'"></span>
            </button>

            <button type="button" @click="setSection('quality-control')"
                    class="flex items-center gap-2 px-3 py-1.5 text-xs font-bold rounded-md transition whitespace-nowrap cursor-pointer"
                    :style="activeSection === 'quality-control' ? 'background: var(--color-primary); color: #FFFFFF;' : 'color: var(--hc-text-muted); background: var(--hc-surface-alt);'">
                <i data-lucide="flask-conical" class="w-3.5 h-3.5"></i>
                <span x-text="$store.app.t('quality_control', 'Quality Control & Calibration')">Quality Control</span>
                <span class="text-[10px] font-mono px-1.5 py-0.5 rounded"
                      :style="activeSection === 'quality-control' ? 'background: rgba(255,255,255,0.25); color: #FFFFFF;' : 'background: var(--hc-surface); color: var(--hc-text-muted);'"
                      x-text="qcPassedCount + '/' + calibrations.length + ' pass'"></span>
            </button>

            <button type="button" @click="setSection('inventory')"
                    class="flex items-center gap-2 px-3 py-1.5 text-xs font-bold rounded-md transition whitespace-nowrap cursor-pointer"
                    :style="activeSection === 'inventory' ? 'background: var(--color-primary); color: #FFFFFF;' : 'color: var(--hc-text-muted); background: var(--hc-surface-alt);'">
                <i data-lucide="package" class="w-3.5 h-3.5"></i>
                <span x-text="$store.app.t('inventory_supplies', 'Inventory & Supplies Receipt')">Inventory & Supplies</span>
                <span x-show="receivedSupplies.length > 0" class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-500 font-bold"
                      x-text="receivedSupplies.length + ' received'"></span>
            </button>
        </div>

    </div>

    {{-- ═══════════════════════════════════════════════════════════
         HIDDEN MASTER FORM (Serializes all 4 sections together)
         ═══════════════════════════════════════════════════════════ --}}
    <form id="lab-submit-form" action="{{ $mode === 'edit' ? route('reports.update', $report) : route('reports.store') }}" method="POST">
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        <input type="hidden" name="department" value="laboratory">
        <input type="hidden" name="form_code" value="ANL-LAB-04">
        <input type="hidden" name="report_date" :value="reportDate">
        <input type="hidden" name="shift" :value="shift">
        <input type="hidden" name="lead_staff_name" :value="labLead">
        <input type="hidden" name="status" value="draft">

        <!-- Section A: Volume -->
        <input type="hidden" name="form_data[section_a_volume][opd_patients]" :value="opdPts">
        <input type="hidden" name="form_data[section_a_volume][opd_tests]" :value="opdTests">
        <input type="hidden" name="form_data[section_a_volume][er_patients]" :value="erPts">
        <input type="hidden" name="form_data[section_a_volume][er_tests]" :value="erTests">
        <input type="hidden" name="form_data[section_a_volume][ipd_patients]" :value="ipdPts">
        <input type="hidden" name="form_data[section_a_volume][ipd_tests]" :value="ipdTests">
        <input type="hidden" name="form_data[section_a_volume][outside_walkin_pts]" :value="walkinPts">
        <input type="hidden" name="form_data[section_a_volume][outside_partner_pts]" :value="partnerPts">
        <input type="hidden" name="form_data[section_a_volume][outside_patients]" :value="totalOutsidePts">
        <input type="hidden" name="form_data[section_a_volume][outside_tests]" :value="outsideTests">
        <input type="hidden" name="form_data[section_a_volume][total_patients]" :value="totalLabPatients">
        <input type="hidden" name="form_data[section_a_volume][total_tests]" :value="totalLabTests">
        <input type="hidden" name="form_data[section_a_volume][tests_per_patient_ratio]" :value="testsPerPatientRatio">

        <!-- Section B: Partners -->
        <template x-for="(part, idx) in partners" :key="'b-'+idx">
            <div>
                <input type="hidden" :name="`form_data[section_b_partners][${idx}][partner]`" :value="part.partner">
                <input type="hidden" :name="`form_data[section_b_partners][${idx}][patients]`" :value="part.patients">
                <input type="hidden" :name="`form_data[section_b_partners][${idx}][tests]`" :value="part.tests">
            </div>
        </template>

        <!-- Section C: Benches & Quality (13 LMS Specialties) -->
        <input type="hidden" name="form_data[section_c_benches_and_quality][haematology]" :value="benchHematology">
        <input type="hidden" name="form_data[section_c_benches_and_quality][hematology]" :value="benchHematology">
        <input type="hidden" name="form_data[section_c_benches_and_quality][biochemistry]" :value="benchBiochemistry">
        <input type="hidden" name="form_data[section_c_benches_and_quality][serology]" :value="benchSerology">
        <input type="hidden" name="form_data[section_c_benches_and_quality][serology_immunology]" :value="benchSerology">
        <input type="hidden" name="form_data[section_c_benches_and_quality][microbiology_parasitology]" :value="benchMicrobiology">
        <input type="hidden" name="form_data[section_c_benches_and_quality][hormones]" :value="benchHormones">
        <input type="hidden" name="form_data[section_c_benches_and_quality][hormones_special]" :value="benchHormones">
        <input type="hidden" name="form_data[section_c_benches_and_quality][blood_bank]" :value="benchBloodBank">
        <input type="hidden" name="form_data[section_c_benches_and_quality][urine]" :value="benchUrine">
        <input type="hidden" name="form_data[section_c_benches_and_quality][stool]" :value="benchStool">
        <input type="hidden" name="form_data[section_c_benches_and_quality][quantitative_test]" :value="benchQuantitative">
        <input type="hidden" name="form_data[section_c_benches_and_quality][coagulation_profile]" :value="benchCoagulation">
        <input type="hidden" name="form_data[section_c_benches_and_quality][semen_analysis]" :value="benchSemen">
        <input type="hidden" name="form_data[section_c_benches_and_quality][tb_analysis]" :value="benchTb">
        <input type="hidden" name="form_data[section_c_benches_and_quality][body_fluids]" :value="benchBodyFluids">
        <input type="hidden" name="form_data[section_c_benches_and_quality][sample_rejections]" :value="sampleRejections">
        <input type="hidden" name="form_data[section_c_benches_and_quality][delayed_tests_tat]" :value="delayedTat">
        <input type="hidden" name="form_data[section_c_benches_and_quality][critical_values_reported]" :value="criticalValues">

        <!-- Individual Test Counts Serialized in Background -->
        <template x-for="(testItem, idx) in allCatalogTests" :key="'cat-test-'+idx">
            <div>
                <input type="hidden" :name="`form_data[section_c_benches_and_quality][tests][${idx}][test_name]`" :value="testItem.test_name">
                <input type="hidden" :name="`form_data[section_c_benches_and_quality][tests][${idx}][lab_type]`" :value="testItem.lab_type">
                <input type="hidden" :name="`form_data[section_c_benches_and_quality][tests][${idx}][count]`" :value="testItem.count">
            </div>
        </template>

        <!-- Section D: Nil Items -->
        <template x-for="(nil, idx) in nilItems" :key="'d-'+idx">
            <div>
                <input type="hidden" :name="`form_data[section_d_nil_items][items][${idx}][item_name]`" :value="nil.item_name">
                <input type="hidden" :name="`form_data[section_d_nil_items][items][${idx}][bench]`" :value="nil.bench">
                <input type="hidden" :name="`form_data[section_d_nil_items][items][${idx}][reason]`" :value="nil.reason">
                <input type="hidden" :name="`form_data[section_d_nil_items][items][${idx}][expected_restock]`" :value="nil.expected_restock">
            </div>
        </template>

        <!-- Section E: Lab Operations & Shift Handover -->
        <input type="hidden" name="form_data[section_e_lab_operations][shift_handover][shift_notes]" :value="shiftHandoverNotes">
        <input type="hidden" name="form_data[section_e_lab_operations][shift_handover][incoming_staff]" :value="incomingStaff">
        <input type="hidden" name="form_data[section_e_lab_operations][shift_handover][manager_notes]" :value="managerNotes">
        <input type="hidden" name="form_data[section_e_lab_operations][shift_handover][urgency_level]" :value="operationalUrgency">

        <template x-for="(inc, idx) in incompleteTests" :key="'e-inc-'+idx">
            <div>
                <input type="hidden" :name="`form_data[section_e_lab_operations][incomplete_tests][${idx}][patient_id]`" :value="inc.patient_id">
                <input type="hidden" :name="`form_data[section_e_lab_operations][incomplete_tests][${idx}][sample_id]`" :value="inc.sample_id">
                <input type="hidden" :name="`form_data[section_e_lab_operations][incomplete_tests][${idx}][patient_name]`" :value="inc.patient_name">
                <input type="hidden" :name="`form_data[section_e_lab_operations][incomplete_tests][${idx}][test_name]`" :value="inc.test_name">
                <input type="hidden" :name="`form_data[section_e_lab_operations][incomplete_tests][${idx}][bench]`" :value="inc.bench">
                <input type="hidden" :name="`form_data[section_e_lab_operations][incomplete_tests][${idx}][reason]`" :value="inc.reason">
                <input type="hidden" :name="`form_data[section_e_lab_operations][incomplete_tests][${idx}][status]`" :value="inc.status">
                <input type="hidden" :name="`form_data[section_e_lab_operations][incomplete_tests][${idx}][action_required]`" :value="inc.action_required">
            </div>
        </template>

        <template x-for="(eq, idx) in equipmentIssues" :key="'e-eq-'+idx">
            <div>
                <input type="hidden" :name="`form_data[section_e_lab_operations][equipment_troubleshooting][${idx}][equipment_name]`" :value="eq.equipment_name">
                <input type="hidden" :name="`form_data[section_e_lab_operations][equipment_troubleshooting][${idx}][issue_description]`" :value="eq.issue_description">
                <input type="hidden" :name="`form_data[section_e_lab_operations][equipment_troubleshooting][${idx}][troubleshooting_action]`" :value="eq.troubleshooting_action">
                <input type="hidden" :name="`form_data[section_e_lab_operations][equipment_troubleshooting][${idx}][status]`" :value="eq.status">
                <input type="hidden" :name="`form_data[section_e_lab_operations][equipment_troubleshooting][${idx}][engineer_notified]`" :value="eq.engineer_notified">
            </div>
        </template>

        <!-- Section F: Quality Control -->
        <template x-for="(cal, idx) in calibrations" :key="'f-cal-'+idx">
            <div>
                <input type="hidden" :name="`form_data[section_f_quality_control][calibrations][${idx}][test_name]`" :value="cal.test_name">
                <input type="hidden" :name="`form_data[section_f_quality_control][calibrations][${idx}][analyzer]`" :value="cal.analyzer">
                <input type="hidden" :name="`form_data[section_f_quality_control][calibrations][${idx}][standard_absorption]`" :value="cal.standard_absorption">
                <input type="hidden" :name="`form_data[section_f_quality_control][calibrations][${idx}][measured_absorption]`" :value="cal.measured_absorption">
                <input type="hidden" :name="`form_data[section_f_quality_control][calibrations][${idx}][calibrator_lot]`" :value="cal.calibrator_lot">
                <input type="hidden" :name="`form_data[section_f_quality_control][calibrations][${idx}][qc_status]`" :value="cal.qc_status">
                <input type="hidden" :name="`form_data[section_f_quality_control][calibrations][${idx}][calibrated_by]`" :value="cal.calibrated_by">
                <input type="hidden" :name="`form_data[section_f_quality_control][calibrations][${idx}][calibration_date]`" :value="cal.calibration_date">
                <input type="hidden" :name="`form_data[section_f_quality_control][calibrations][${idx}][corrective_action]`" :value="cal.corrective_action">
            </div>
        </template>

        <!-- Section G: Inventory & Received Supplies -->
        <template x-for="(inv, idx) in receivedSupplies" :key="'g-inv-'+idx">
            <div>
                <input type="hidden" :name="`form_data[section_g_inventory_supplies][received_items][${idx}][item_name]`" :value="inv.item_name">
                <input type="hidden" :name="`form_data[section_g_inventory_supplies][received_items][${idx}][category]`" :value="inv.category">
                <input type="hidden" :name="`form_data[section_g_inventory_supplies][received_items][${idx}][brand]`" :value="inv.brand">
                <input type="hidden" :name="`form_data[section_g_inventory_supplies][received_items][${idx}][quantity]`" :value="inv.quantity">
                <input type="hidden" :name="`form_data[section_g_inventory_supplies][received_items][${idx}][unit]`" :value="inv.unit">
                <input type="hidden" :name="`form_data[section_g_inventory_supplies][received_items][${idx}][lot_number]`" :value="inv.lot_number">
                <input type="hidden" :name="`form_data[section_g_inventory_supplies][received_items][${idx}][expiry_date]`" :value="inv.expiry_date">
                <input type="hidden" :name="`form_data[section_g_inventory_supplies][received_items][${idx}][storage_condition]`" :value="inv.storage_condition">
                <input type="hidden" :name="`form_data[section_g_inventory_supplies][received_items][${idx}][package_condition]`" :value="inv.package_condition">
                <input type="hidden" :name="`form_data[section_g_inventory_supplies][received_items][${idx}][who_received]`" :value="inv.who_received">
                <input type="hidden" :name="`form_data[section_g_inventory_supplies][received_items][${idx}][received_date]`" :value="inv.received_date">
                <input type="hidden" :name="`form_data[section_g_inventory_supplies][received_items][${idx}][supplier_name]`" :value="inv.supplier_name">
                <input type="hidden" :name="`form_data[section_g_inventory_supplies][received_items][${idx}][supplier_representative]`" :value="inv.supplier_representative">
                <input type="hidden" :name="`form_data[section_g_inventory_supplies][received_items][${idx}][invoice_po_ref]`" :value="inv.invoice_po_ref">
                <input type="hidden" :name="`form_data[section_g_inventory_supplies][received_items][${idx}][acceptance_status]`" :value="inv.acceptance_status">
            </div>
        </template>
    </form>

    {{-- ═══════════════════════════════════════════════════════════
         AUTHENTIC A4 CLINICAL PAPER DOCUMENT SHEET
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
                                {{ substr($hospital->hospital_name ?? 'Kaafi Hospital', 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <h1 class="text-lg font-black uppercase tracking-wider text-[var(--hc-text)]">
                                {{ $hospital->hospital_name ?? 'KAAFI HOSPITAL' }}
                            </h1>
                            <p class="text-xs font-semibold text-[var(--hc-text-muted)]">
                                DEPARTMENT OF CLINICAL LABORATORY &amp; DIAGNOSTIC PATHOLOGY
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="inline-block px-2.5 py-1 bg-[var(--hc-text)] text-[var(--hc-surface)] font-mono font-bold text-xs rounded">
                            FORM ID: ANL-LAB-04
                        </div>
                        <div class="text-[10px] text-[var(--hc-text-muted)] font-mono mt-1">ISO 15189:2022 &bull; CLINICAL AUDIT SHEET</div>
                    </div>
                </div>

                {{-- Metadata Bar --}}
                <div class="paper-metadata-bar">
                    <div>
                        <span class="font-bold text-[var(--hc-text)]">REPORT DATE:</span>
                        <input type="date" x-model="reportDate" class="paper-input ml-2 font-mono text-xs">
                    </div>
                    <div>
                        <span class="font-bold text-[var(--hc-text)]">DUTY PERIOD:</span>
                        <select x-model="shift" @change="changeShift($event.target.value)" class="paper-input ml-2 font-semibold text-xs">
                            <option value="Morning">Morning Shift (08:00 - 14:00)</option>
                            <option value="Afternoon">Noon Shift (14:00 - 21:00)</option>
                            <option value="Night">Night Shift (21:00 - 08:00)</option>
                            <option value="24-Hour Daily Summary">24-Hour Daily Summary</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-bold text-[var(--hc-text)]">LAB TECHNOLOGIST:</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 font-bold text-xs text-slate-900 dark:text-white border border-slate-300 dark:border-slate-700">
                            <i data-lucide="user-check" class="w-3 h-3 text-emerald-500"></i>
                            {{ $report->lead_staff_name ?? auth()->user()->name }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Tab Sub-Letterhead Bar displaying Active Module --}}
            <div class="bg-[var(--hc-surface-alt)] px-6 py-2.5 border-b border-[var(--hc-border)] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--hc-text-muted)]">ACTIVE CLINICAL MODULE:</span>
                    <span class="font-bold text-xs font-mono" :style="'color: var(--color-primary);'" x-text="
                        activeSection === 'daily-report' ? 'MODULE 1: DAILY DIAGNOSTIC VOLUME & BENCH REGISTER (SEC A - D)' :
                        (activeSection === 'operations' ? 'MODULE 2: LAB OPERATIONS, INCOMPLETE TESTS & SHIFT HANDOVER (SEC E)' :
                        (activeSection === 'quality-control' ? 'MODULE 3: QUALITY CONTROL & ANALYZER CALIBRATIONS (SEC F)' :
                        'MODULE 4: RECEIVED REAGENTS & SUPPLIES INVENTORY INTAKE (SEC G)'))
                    "></span>
                </div>
                <div class="text-[11px] font-mono text-[var(--hc-text-muted)]">
                    Document Ref: <strong class="text-[var(--hc-text)]">KFH-LAB-2026</strong>
                </div>
            </div>

            {{-- Dedicated Section Workspaces Inside Paper Sheet --}}
            <div class="p-6 space-y-6">
                {{-- Section 1: Daily Diagnostic Report --}}
                <div x-show="activeSection === 'daily-report'"
                     x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    @include('reports.forms.lab.daily-report')
                </div>

                {{-- Section 2: Lab Operations & Shift Handover --}}
                <div x-show="activeSection === 'operations'"
                     x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    @include('reports.forms.lab.operations')
                </div>

                {{-- Section 3: Quality Control & Calibrations --}}
                <div x-show="activeSection === 'quality-control'"
                     x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    @include('reports.forms.lab.quality-control')
                </div>

                {{-- Section 4: Inventory & Supplies --}}
                <div x-show="activeSection === 'inventory'"
                     x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    @include('reports.forms.lab.inventory')
                </div>
            </div>

            {{-- Signature Verification Footer --}}
            <div class="px-6 py-4 bg-[var(--hc-surface-alt)] border-t border-[var(--hc-border)] flex flex-col sm:flex-row items-center justify-between text-xs text-[var(--hc-text-muted)] gap-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[var(--hc-success)]"></i>
                    <span>Official Kaafi Hospital Clinical Laboratory Audit Sheet &bull; All entries certified by Duty Technologist</span>
                </div>
                <div class="font-mono text-[11px] font-bold text-[var(--hc-text)]">
                    Audit Status: <span class="text-[var(--hc-brand)] uppercase">Draft Pending Review</span>
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
                <div class="w-10 h-10 rounded-md bg-[var(--hc-purple)] text-white flex items-center justify-center font-bold text-sm shrink-0">
                    <i data-lucide="layout-list" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-[var(--hc-text)]">
                        Mode B — Guided Laboratory Operations &amp; Quick Entry
                    </h2>
                    <p class="text-xs text-[var(--hc-text-muted)]">
                        Touch stepper counters, bench tallies &amp; rapid clinical loggers &bull; Fully synchronized with Mode A (A4 Sheet)
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <div class="hc-badge hc-badge-purple">
                    <span class="font-bold">Total Tests:</span>
                    <span class="font-mono font-bold" x-text="totalLabTests"></span>
                </div>
                <div class="hc-badge hc-badge-warning">
                    <span class="font-bold">Incomplete:</span>
                    <span class="font-mono font-bold" x-text="incompleteTests.length"></span>
                </div>
                <div class="hc-badge hc-badge-success">
                    <span class="font-bold">QC Passed:</span>
                    <span class="font-mono font-bold" x-text="qcPassedCount + '/' + calibrations.length"></span>
                </div>
                <button type="button" @click="viewMode = 'paper'" class="btn btn-secondary btn-xs">
                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                    <span>Switch to A4 View</span>
                </button>
            </div>
        </div>

        {{-- Mode B Section Workspaces --}}
        <div class="space-y-6">
            {{-- Section 1: Daily Diagnostic Report --}}
            <div x-show="activeSection === 'daily-report'"
                 x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0">
                @include('reports.forms.lab.daily-report')
            </div>

            {{-- Section 2: Lab Operations & Shift Handover --}}
            <div x-show="activeSection === 'operations'"
                 x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0">
                @include('reports.forms.lab.operations')
            </div>

            {{-- Section 3: Quality Control & Calibrations --}}
            <div x-show="activeSection === 'quality-control'"
                 x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0">
                @include('reports.forms.lab.quality-control')
            </div>

            {{-- Section 4: Inventory & Supplies --}}
            <div x-show="activeSection === 'inventory'"
                 x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0">
                @include('reports.forms.lab.inventory')
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
                    <div class="w-10 h-10 rounded-md bg-[var(--hc-purple)] text-white flex items-center justify-center font-bold shrink-0">
                        <i data-lucide="file-check-2" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="hc-badge hc-badge-purple font-mono">FORM: ANL-LAB-04</span>
                            <span class="text-xs text-[var(--hc-text-muted)] font-mono">&bull; Date: <strong class="text-[var(--hc-text)]" x-text="reportDate"></strong></span>
                            <span class="text-xs text-[var(--hc-text-muted)] font-mono">&bull; Shift: <strong class="text-[var(--hc-text)]" x-text="shift"></strong></span>
                        </div>
                        <h2 class="text-base font-bold text-[var(--hc-text)] mt-0.5">
                            Laboratory Clinical Audit Verification &amp; Final Approval
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
                                            DEPARTMENT OF CLINICAL LABORATORY &amp; DIAGNOSTIC PATHOLOGY
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="inline-block px-2.5 py-1 bg-[var(--hc-text)] text-[var(--hc-surface)] font-mono font-bold text-xs rounded">
                                        FORM ID: ANL-LAB-04
                                    </div>
                                    <div class="text-[10px] text-[var(--hc-text-muted)] font-mono mt-1">ISO 15189:2022 &bull; CLINICAL AUDIT READY</div>
                                </div>
                            </div>

                            <div class="paper-metadata-bar">
                                <div><span class="font-bold text-[var(--hc-text)]">REPORT DATE:</span> <span class="font-mono text-xs font-bold" x-text="reportDate"></span></div>
                                <div><span class="font-bold text-[var(--hc-text)]">DUTY PERIOD:</span> <span class="font-semibold text-xs" x-text="shift"></span></div>
                                <div><span class="font-bold text-[var(--hc-text)]">LAB TECHNOLOGIST:</span> <span class="font-semibold text-xs" x-text="labLead"></span></div>
                            </div>
                        </div>

                        <!-- Section A Preview Summary -->
                        <div class="p-4 border-b border-[var(--hc-border)] space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs uppercase tracking-wider text-[var(--hc-text)]">MODULE 1: DIAGNOSTIC TEST VOLUME &amp; BENCH TALLY</span>
                                <span class="font-mono text-xs font-bold text-[var(--color-primary)]" x-text="'Total: ' + totalLabTests + ' Tests (' + testsPerPatientRatio + 'x/pt)'"></span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2 text-xs font-mono">
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] text-center">
                                    <span class="text-[9px] uppercase text-[var(--hc-text-muted)] block truncate">1. Hematology</span>
                                    <span class="font-bold text-xs text-[var(--hc-text)]" x-text="benchHematology"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] text-center">
                                    <span class="text-[9px] uppercase text-[var(--hc-text-muted)] block truncate">2. Biochemistry</span>
                                    <span class="font-bold text-xs text-[var(--hc-text)]" x-text="benchBiochemistry"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] text-center">
                                    <span class="text-[9px] uppercase text-[var(--hc-text-muted)] block truncate">3. Serology</span>
                                    <span class="font-bold text-xs text-[var(--hc-text)]" x-text="benchSerology"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] text-center">
                                    <span class="text-[9px] uppercase text-[var(--hc-text-muted)] block truncate">4. Hormones</span>
                                    <span class="font-bold text-xs text-[var(--hc-text)]" x-text="benchHormones"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] text-center">
                                    <span class="text-[9px] uppercase text-[var(--hc-text-muted)] block truncate">5. Urine</span>
                                    <span class="font-bold text-xs text-[var(--hc-text)]" x-text="benchUrine"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] text-center">
                                    <span class="text-[9px] uppercase text-[var(--hc-text-muted)] block truncate">6. Stool</span>
                                    <span class="font-bold text-xs text-[var(--hc-text)]" x-text="benchStool"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] text-center">
                                    <span class="text-[9px] uppercase text-[var(--hc-text-muted)] block truncate">7. Quantitative</span>
                                    <span class="font-bold text-xs text-[var(--hc-text)]" x-text="benchQuantitative"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] text-center">
                                    <span class="text-[9px] uppercase text-[var(--hc-text-muted)] block truncate">8. Coagulation</span>
                                    <span class="font-bold text-xs text-[var(--hc-text)]" x-text="benchCoagulation"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] text-center">
                                    <span class="text-[9px] uppercase text-[var(--hc-text-muted)] block truncate">9. Microbiology</span>
                                    <span class="font-bold text-xs text-[var(--hc-text)]" x-text="benchMicrobiology"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] text-center">
                                    <span class="text-[9px] uppercase text-[var(--hc-text-muted)] block truncate">10. Blood Bank</span>
                                    <span class="font-bold text-xs text-[var(--hc-text)]" x-text="benchBloodBank"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] text-center">
                                    <span class="text-[9px] uppercase text-[var(--hc-text-muted)] block truncate">11. Semen</span>
                                    <span class="font-bold text-xs text-[var(--hc-text)]" x-text="benchSemen"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] text-center">
                                    <span class="text-[9px] uppercase text-[var(--hc-text-muted)] block truncate">12. TB</span>
                                    <span class="font-bold text-xs text-[var(--hc-text)]" x-text="benchTb"></span>
                                </div>
                                <div class="p-2 rounded bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] text-center">
                                    <span class="text-[9px] uppercase text-[var(--hc-text-muted)] block truncate">13. Body Fluids</span>
                                    <span class="font-bold text-xs text-[var(--hc-text)]" x-text="benchBodyFluids"></span>
                                </div>
                                <div class="p-2 rounded bg-purple-500/10 border border-purple-500/30 text-center">
                                    <span class="text-[9px] uppercase text-purple-300 block truncate">Total Bench</span>
                                    <span class="font-bold text-xs text-purple-400" x-text="Number(totalBenchTests).toLocaleString()"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Modules 2, 3, 4 Preview Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x border-b border-[var(--hc-border)]">
                            <!-- Operations & Incomplete -->
                            <div class="p-4 space-y-2 text-xs">
                                <span class="font-bold uppercase tracking-wider text-[var(--hc-text)]">MODULE 2: OPERATIONS</span>
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between py-1 border-b border-[var(--hc-border)]">
                                        <span>Incomplete Tests:</span>
                                        <span class="font-bold font-mono text-[var(--hc-warning)]" x-text="incompleteTests.length"></span>
                                    </div>
                                    <div class="flex items-center justify-between py-1 border-b border-[var(--hc-border)]">
                                        <span>TAT Delays:</span>
                                        <span class="font-bold font-mono text-[var(--hc-danger)]" x-text="delayedTat"></span>
                                    </div>
                                    <div class="flex items-center justify-between py-1">
                                        <span>Critical Values:</span>
                                        <span class="font-bold font-mono text-[var(--color-primary)]" x-text="criticalValues"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Quality Control -->
                            <div class="p-4 space-y-2 text-xs">
                                <span class="font-bold uppercase tracking-wider text-[var(--hc-text)]">MODULE 3: QUALITY CONTROL</span>
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between py-1 border-b border-[var(--hc-border)]">
                                        <span>QC Passed:</span>
                                        <span class="font-bold font-mono text-[var(--hc-success)]" x-text="qcPassedCount"></span>
                                    </div>
                                    <div class="flex items-center justify-between py-1 border-b border-[var(--hc-border)]">
                                        <span>QC Calibration Drift:</span>
                                        <span class="font-bold font-mono text-[var(--hc-warning)]" x-text="qcDriftCount"></span>
                                    </div>
                                    <div class="flex items-center justify-between py-1">
                                        <span>QC Out of Control:</span>
                                        <span class="font-bold font-mono text-[var(--hc-danger)]" x-text="qcFailedCount"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Inventory Intake -->
                            <div class="p-4 space-y-2 text-xs">
                                <span class="font-bold uppercase tracking-wider text-[var(--hc-text)]">MODULE 4: INVENTORY INTAKE</span>
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between py-1 border-b border-[var(--hc-border)]">
                                        <span>Received Shipments:</span>
                                        <span class="font-bold font-mono text-[var(--color-primary)]" x-text="receivedSupplies.length + ' lots'"></span>
                                    </div>
                                    <div class="flex items-center justify-between py-1 border-b border-[var(--hc-border)]">
                                        <span>Stockout Reagents:</span>
                                        <span class="font-bold font-mono text-[var(--hc-danger)]" x-text="nilItems.length + ' items'"></span>
                                    </div>
                                    <div class="flex items-center justify-between py-1">
                                        <span>Handover Status:</span>
                                        <span class="font-bold uppercase font-mono text-[10px] text-[var(--hc-success)]" x-text="operationalUrgency"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Certification Footer -->
                        <div class="p-4 bg-[var(--hc-surface-alt)] flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-[var(--hc-text-muted)]">
                            <div class="flex items-center gap-2">
                                <i data-lucide="shield-check" class="w-4 h-4 text-[var(--hc-success)]"></i>
                                <span>Certified Clinical Laboratory Audit Document &bull; Verified by Duty Technologist</span>
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
