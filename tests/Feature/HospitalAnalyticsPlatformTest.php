<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\DepartmentForm;
use App\Models\User;
use App\Services\CrossDepartmentAuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HospitalAnalyticsPlatformTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Kaafi Hospitals');
    }

    public function test_quick_switch_authenticates_admin_user(): void
    {
        $response = $this->post('/quick-switch', [
            'email' => 'admin@kaafi.com',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_dashboard_loads_with_kpis_and_audit_engine(): void
    {
        $admin = User::where('email', 'admin@kaafi.com')->first();
        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Executive Analytics Center');
        $response->assertSee('4-Point Cross-Departmental Audit Engine');
        $response->assertSee('Pharmacy');
        $response->assertSee('Laboratory');
        $response->assertSee('Emergency (E.R)');
        $response->assertSee('Inpatient (IPD)');
        $response->assertSee('Reception');
    }

    public function test_restricted_department_user_dashboard_shows_only_department_workspace(): void
    {
        $pharmacyUser = User::where('email', 'pharmacy@kaafi.com')->first();
        $response = $this->actingAs($pharmacyUser)->get('/dashboard');

        $response->assertStatus(200);
        // Should see department workspace
        $response->assertSee('Pharmacy Department Operational Station');
        $response->assertSee('Station Operations & Quick Links', false);
        $response->assertSee('Recent Submissions History');

        // Should NOT see hospital-wide executive suite
        $response->assertDontSee('Executive Analytics Center');
        $response->assertDontSee('4-Point Cross-Departmental Audit Engine');
        $response->assertDontSee('Department Operational Stations');

        // Test Emergency User
        $erUser = User::where('email', 'emergency@kaafi.com')->first();
        $erResponse = $this->actingAs($erUser)->get('/dashboard');

        $erResponse->assertStatus(200);
        $erResponse->assertSee('Emergency Department (E.R) Operational Station');
        $erResponse->assertSee('Total Emergency Cases');
        $erResponse->assertDontSee('Executive Analytics Center');
        $erResponse->assertDontSee('4-Point Cross-Departmental Audit Engine');
    }

    public function test_admin_report_show_view_renders_clean_organized_clinical_tables_without_code_blocks(): void
    {
        $admin = User::where('email', 'admin@kaafi.com')->first();
        $report = DailyReport::where('department', 'pharmacy')->where('status', 'submitted')->first();

        $response = $this->actingAs($admin)->get("/reports/{$report->id}");

        $response->assertStatus(200);
        // Clean clinical presentation
        $response->assertSee('Pharmacy Daily Clinical Report');
        $response->assertSee('Section A — Prescription Volume &amp; Dispensing Throughput', false);
        $response->assertSee('Unserved Patients Breakdown');
        $response->assertSee('Financials &amp; Credit Ledger', false);
        $response->assertSee('Audit &amp; Approve', false);

        // Raw code block with "Audited Clinical Form Data" must be gone
        $response->assertDontSee('Audited Clinical Form Data');
        $response->assertDontSee('JSON Integrity Check: Passed');
    }

    public function test_department_form_editor_loads_with_dual_mode_support(): void
    {
        $pharmacyUser = User::where('email', 'pharmacy@kaafi.com')->first();
        $response = $this->actingAs($pharmacyUser)->get('/reports/create?department=pharmacy');

        $response->assertStatus(200);
        $response->assertSee('ANL-PHM-01');
        $response->assertSee('Mode A: Paper-Replica');
        $response->assertSee('Mode B: Guided Form');
    }

    public function test_cross_department_audit_service_executes_4_checks(): void
    {
        $auditService = app(CrossDepartmentAuditService::class);
        $result = $auditService->runAuditForDate(now()->toDateString());

        $this->assertArrayHasKey('overall_status', $result);
        $this->assertArrayHasKey('checks', $result);
        $this->assertCount(4, $result['checks']);
    }

    public function test_cross_department_audit_handles_array_structured_nil_items_and_stockouts(): void
    {
        $admin = User::where('email', 'admin@kaafi.com')->first();
        $date = '2026-12-01';

        DailyReport::create([
            'department' => 'laboratory',
            'form_code' => 'ANL-LAB-04',
            'report_date' => $date,
            'submitted_by' => $admin->id,
            'status' => 'approved',
            'form_data' => [
                'section_d_nil_items' => [
                    'items' => [
                        [
                            'item_name' => 'CBC Diluent / Lyse Reagent',
                            'bench' => 'Hematology',
                            'reason' => 'Stockout at supplier',
                            'expected_restock' => '2026-11-01',
                        ],
                    ],
                ],
            ],
        ]);

        $auditService = app(CrossDepartmentAuditService::class);
        $result = $auditService->runAuditForDate($date);

        $this->assertArrayHasKey('checks', $result);
        $this->assertSame('passed', $result['checks']['check_4_stockout_persistence']['status']);
    }

    public function test_superadmin_secret_ai_endpoint_is_invisible_to_normal_admin(): void
    {
        $normalAdmin = User::where('email', 'admin@kaafi.com')->first();
        $response = $this->actingAs($normalAdmin)->get('/superadmin/deepseek');

        // Stealth 404 to hide route existence from normal admins
        $response->assertStatus(404);
    }

    public function test_superadmin_secret_ai_endpoint_is_accessible_to_stealth_superadmin(): void
    {
        $superAdmin = User::where('email', 'superadmin@reports.com')->first();
        $response = $this->actingAs($superAdmin)->get('/superadmin/deepseek');

        $response->assertStatus(200);
        $response->assertSee('DeepSeek AI Executive Analyst');
    }

    public function test_department_supervisor_is_strictly_isolated_from_other_departments(): void
    {
        $pharmacyUser = User::where('email', 'pharmacy@kaafi.com')->first();

        // 1. Cannot open creation form for other departments
        $forbiddenCreate = $this->actingAs($pharmacyUser)->get('/reports/create?department=emergency');
        $forbiddenCreate->assertStatus(403);

        // 2. Cannot view or edit another department's existing report
        $erReport = DailyReport::where('department', 'emergency')->first();
        if ($erReport) {
            $forbiddenShow = $this->actingAs($pharmacyUser)->get(route('reports.show', $erReport));
            $forbiddenShow->assertStatus(403);

            $forbiddenEdit = $this->actingAs($pharmacyUser)->get(route('reports.edit', $erReport));
            $forbiddenEdit->assertStatus(403);

            $forbiddenPrint = $this->actingAs($pharmacyUser)->get(route('reports.print', $erReport));
            $forbiddenPrint->assertStatus(403);
        }

        // 3. Reports index only lists their own department's reports
        $reportsIndex = $this->actingAs($pharmacyUser)->get(route('reports.index'));
        $reportsIndex->assertStatus(200);
        $reportsIndex->assertSee('Pharmacy');
        $reportsIndex->assertDontSee('ANL-ERD-02');
    }

    public function test_duty_lead_is_selectable_and_only_lists_department_staff(): void
    {
        $pharmacyUser = User::where('email', 'pharmacy@kaafi.com')->first();

        $response = $this->actingAs($pharmacyUser)->get('/reports/create?department=pharmacy');
        $response->assertStatus(200);

        // Department staff should be selectable
        $response->assertSee('Ahmed Warsame (PharmD Lead)');
        $response->assertSee('Sahra Yusuf (Senior Dispensing Pharmacist)');

        // Staff from other departments must NOT appear in this department's duty person list
        $response->assertDontSee('Dr. Hassan Ali (Emergency Clinical Lead)');
        $response->assertDontSee('Khadra Duale (Chief Medical Technologist)');
        $response->assertDontSee('Sister Halima Abdi (IPD Charge Nurse)');
    }

    public function test_laboratory_form_loads_with_operations_qc_and_inventory_modules(): void
    {
        $labUser = User::where('email', 'lab@kaafi.com')->first();
        $newDate = '2026-11-20';
        $response = $this->actingAs($labUser)->get("/reports/create?department=laboratory&date={$newDate}");

        $response->assertStatus(200);
        $response->assertSee('ANL-LAB-04');
        $response->assertSee('SECTION E — LAB OPERATIONS', false);
        $response->assertSee('SECTION F — QUALITY CONTROL', false);
        $response->assertSee('SECTION G — LABORATORY INVENTORY', false);
        $response->assertSee('Blood Glucose');
        $response->assertSee('Serum Creatinine');
        $response->assertSee('Sysmex');

        // New day must be clean and empty (all counts zero, dynamic lists empty)
        $response->assertSee('opdPts: 0');
        $response->assertSee('opdTests: 0');
        $response->assertSee('partners: []');
        $response->assertSee('incompleteTests: []');
        $response->assertSee('receivedSupplies: []');

        // Verify ID conventions (P- for patient, RL- for sample, and floor sync helper)
        $response->assertSee('Patient ID (P#)');
        $response->assertSee('Sample ID (RL#)');
        $response->assertSee('= Use Pt ID');

        // Verify Calendar Navigation to existing day loads that day's saved numbers
        $editableReport = DailyReport::create([
            'department' => 'laboratory',
            'form_code' => 'ANL-LAB-04',
            'report_date' => '2026-11-10',
            'shift' => '24-Hour Daily Summary',
            'lead_staff_name' => $labUser->name,
            'status' => 'draft',
            'created_by' => $labUser->id,
            'form_data' => [
                'section_a_volume' => ['opd_patients' => 42, 'opd_tests' => 99, 'total_patients' => 42, 'total_tests' => 99],
                'section_e_lab_operations' => ['incomplete_tests' => []],
                'section_f_quality_control' => ['calibrations' => []],
                'section_g_inventory_supplies' => ['received_items' => []],
            ],
            'summary_metrics' => ['total_patients' => 42, 'total_tests' => 99],
        ]);

        $navResponse = $this->actingAs($labUser)->get('/reports/create?department=laboratory&date=2026-11-10');
        $navResponse->assertRedirect(route('reports.edit', $editableReport));

        $editResponse = $this->actingAs($labUser)->get(route('reports.edit', $editableReport));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('opdPts: 42');
        $editResponse->assertSee('opdTests: 99');
        $editResponse->assertSee('Loaded Saved Day');
    }

    public function test_laboratory_report_stores_and_indexes_operational_qc_and_inventory_data(): void
    {
        $labUser = User::where('email', 'lab@kaafi.com')->first();
        $testDate = '2026-11-01';
        DailyReport::where('department', 'laboratory')->whereDate('report_date', $testDate)->delete();

        $payload = [
            'department' => 'laboratory',
            'form_code' => 'ANL-LAB-04',
            'report_date' => $testDate,
            'shift' => '24-Hour Daily Summary',
            'lead_staff_name' => $labUser->name,
            'status' => 'submitted',
            'form_data' => [
                'section_a_volume' => [
                    'opd_patients' => 50,
                    'opd_tests' => 120,
                    'er_patients' => 15,
                    'er_tests' => 45,
                    'ipd_patients' => 20,
                    'ipd_tests' => 60,
                    'outside_walkin_pts' => 10,
                    'outside_partner_pts' => 15,
                    'outside_patients' => 25,
                    'outside_tests' => 55,
                    'total_patients' => 110,
                    'total_tests' => 280,
                    'tests_per_patient_ratio' => 2.55,
                ],
                'section_b_partners' => [
                    ['partner' => 'Al-Shifa Clinic', 'patients' => 8, 'tests' => 19],
                ],
                'section_c_benches_and_quality' => [
                    'hematology' => 90,
                    'biochemistry' => 80,
                    'serology_immunology' => 50,
                    'microbiology_parasitology' => 30,
                    'hormones_special' => 20,
                    'blood_bank' => 10,
                    'sample_rejections' => 2,
                    'delayed_tests_tat' => 3,
                    'critical_values_reported' => 4,
                ],
                'section_d_nil_items' => [
                    'items' => ['Electrolyte ISE Calibrant Pack'],
                ],
                'section_e_lab_operations' => [
                    'incomplete_tests' => [
                        [
                            'patient_id' => 'P-9901',
                            'sample_id' => 'RL-9901',
                            'patient_name' => 'Amina Nur',
                            'test_name' => 'Urine Culture & Sensitivity',
                            'bench' => 'Microbiology',
                            'reason' => 'Incubation in progress',
                            'status' => 'In Incubation',
                            'action_required' => 'Read sensitivity tomorrow',
                        ],
                    ],
                    'equipment_troubleshooting' => [
                        [
                            'equipment_name' => 'Mindray BS-240',
                            'issue_description' => 'Probe clot',
                            'troubleshooting_action' => 'De-clotted with wash solution',
                            'status' => 'Resolved & Online',
                            'engineer_notified' => 'No',
                        ],
                    ],
                    'shift_handover' => [
                        'shift_notes' => 'Handover to night shift completed smoothly.',
                        'incoming_staff' => 'Mohamed Omar',
                        'manager_notes' => 'Approved by Lab Director.',
                        'urgency_level' => 'routine',
                    ],
                ],
                'section_f_quality_control' => [
                    'calibrations' => [
                        [
                            'test_name' => 'Blood Glucose (Fasting / Random)',
                            'analyzer' => 'Mindray BS-240',
                            'standard_absorption' => 0.385,
                            'measured_absorption' => 0.383,
                            'calibrator_lot' => 'GLU-26-09',
                            'qc_status' => 'passed',
                            'calibrated_by' => $labUser->name,
                            'calibration_date' => $testDate.' 07:30',
                            'corrective_action' => 'Target CV < 2%',
                        ],
                    ],
                ],
                'section_g_inventory_supplies' => [
                    'received_items' => [
                        [
                            'item_name' => 'CBC Diluent (20L Pack)',
                            'category' => 'Hematology Reagent',
                            'brand' => 'Sysmex',
                            'quantity' => 2,
                            'unit' => 'Box',
                            'lot_number' => 'LOT-26-01',
                            'expiry_date' => '2027-12-31',
                            'storage_condition' => 'Controlled Room Temp (15-25°C)',
                            'package_condition' => 'Intact',
                            'who_received' => $labUser->name,
                            'received_date' => $testDate.' 10:00',
                            'supplier_name' => 'Mogadishu Medical',
                            'supplier_representative' => 'Abdirahman Farah',
                            'invoice_po_ref' => 'PO-900',
                            'acceptance_status' => 'Accepted & Stocked',
                        ],
                    ],
                ],
            ],
        ];

        $storeResponse = $this->actingAs($labUser)->post(route('reports.store'), $payload);
        $storeResponse->assertSessionHasNoErrors();
        $storeResponse->assertRedirect();

        // Check if report was created
        $savedReport = DailyReport::latest('id')->first();
        $this->assertNotNull($savedReport);
        $this->assertEquals('laboratory', $savedReport->department);
        $this->assertEquals(1, $savedReport->summary_metrics['incomplete_tests_count']);
        $this->assertEquals(1, $savedReport->summary_metrics['equipment_issues_count']);
        $this->assertEquals(1, $savedReport->summary_metrics['calibrated_tests_count']);
        $this->assertEquals(1, $savedReport->summary_metrics['supplies_received_count']);

        // Check show page renders the operational cards
        $showResponse = $this->actingAs($labUser)->get(route('reports.show', $savedReport));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Amina Nur');
        $showResponse->assertSee('Urine Culture &amp; Sensitivity', false);
        $showResponse->assertSee('Mindray BS-240');
        $showResponse->assertSee('Blood Glucose (Fasting / Random)');
        $showResponse->assertSee('CBC Diluent (20L Pack)');
        $showResponse->assertSee('Abdirahman Farah');

        // Check print page renders A4 layout
        $printResponse = $this->actingAs($labUser)->get(route('reports.print', $savedReport));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('Section E.1 — Incomplete &amp; Delayed Tests Logbook', false);
        $printResponse->assertSee('Section F — Quality Control (QC) &amp; Calibration Register', false);
        $printResponse->assertSee('Section G — Laboratory Inventory &amp; Supplies Received Log', false);
    }

    public function test_admin_can_update_department_form_selectable_dropdown_options(): void
    {
        $admin = User::where('role', 'admin')->first();
        $dept = DepartmentForm::where('slug', 'laboratory')->first();

        $response = $this->actingAs($admin)->put(route('settings.forms.update', $dept), [
            'cadence' => '24_hour_daily',
            'dropdown_options' => [
                'analyzers' => "Mindray BA 88A, Mindray BC5000 CBC Analyzer, 107 Microscope, Getein 1160 Hormones Analyzer, Finecare Plus Hormones Analyzer, Serology Rotation Mixer, Coagulometer\nCustom Lab Machine",
            ],
            'new_option_key' => 'quality_officers',
            'new_option_values' => 'Officer Mukhtar, Officer Deeq, Officer Sahra',
        ]);

        $response->assertRedirect(route('settings.forms.index'));
        $response->assertSessionHas('success');

        $dept->refresh();
        $this->assertContains('Mindray BA 88A', $dept->dropdown_options['analyzers']);
        $this->assertContains('107 Microscope', $dept->dropdown_options['analyzers']);
        $this->assertContains('Coagulometer', $dept->dropdown_options['analyzers']);
        $this->assertContains('Custom Lab Machine', $dept->dropdown_options['analyzers']);
        $this->assertContains('Officer Mukhtar', $dept->dropdown_options['quality_officers']);
    }

    public function test_qc_page_renders_selectable_analyzers_and_fluid_layout(): void
    {
        $labUser = User::where('email', 'mukhtar.lab@kaafi.com')->first();
        $response = $this->actingAs($labUser)->get(route('reports.create', ['department' => 'laboratory', 'date' => '2026-11-20']));

        $response->assertStatus(200);
        // Verify all 7 requested analyzers are rendered in options
        $response->assertSee('Mindray BA 88A');
        $response->assertSee('Mindray BC5000 CBC Analyzer');
        $response->assertSee('107 Microscope');
        $response->assertSee('Getein 1160 Hormones Analyzer');
        $response->assertSee('Finecare Plus Hormones Analyzer');
        $response->assertSee('Serology Rotation Mixer');
        $response->assertSee('Coagulometer');

        // Verify fluid table-fixed layout and datalists
        $response->assertSee('table-fixed');
        $response->assertSee('id="qc-tests-datalist"', false);
        $response->assertSee('id="qc-corrective-datalist"', false);
        $response->assertSee('Daily Clinical Chemistry &amp; Hematology QC Register', false);
    }

    public function test_all_department_print_views_render_comprehensive_a4_details_without_fallback(): void
    {
        $admin = User::where('role', 'admin')->first();
        $departments = ['reception', 'pharmacy', 'emergency', 'inpatient', 'laboratory'];

        foreach ($departments as $dept) {
            $report = DailyReport::where('department', $dept)->first();
            $this->assertNotNull($report, "DailyReport for {$dept} must exist.");

            $response = $this->actingAs($admin)->get(route('reports.print', $report));
            $response->assertStatus(200);

            // Must NOT render the low-information fallback block
            $response->assertDontSee('Summary Clinical Operations Data');

            if ($dept === 'reception') {
                $response->assertSee('Section A — Patient Registration &amp; Reception Footfall Throughput', false);
                $response->assertSee('Section B — Physician Outpatient Clinic Queue &amp; Consultations', false);
                $response->assertSee('Section C — Front Desk Operational Delays, Incidents &amp; Support Requests', false);
            } elseif ($dept === 'pharmacy') {
                $response->assertSee('Section A — Prescription Volume &amp; Dispensing (Pharmacy Footfall)', false);
                $response->assertSee('Section C — Root Causes for Unserved Prescriptions', false);
                $response->assertSee('Section D — Financial Adjustments, Refunds &amp; Credit Reconciliation', false);
            } elseif ($dept === 'emergency') {
                $response->assertSee('Section A — Emergency Department Triage &amp; Clinical Throughput', false);
                $response->assertSee('Emergency Disposition &amp; Inpatient Admission Yield', false);
                $response->assertSee('Section B &amp; E — Demographics, Triage Classifications &amp; Mortality Audit', false);
            } elseif ($dept === 'inpatient') {
                $response->assertSee('Section A — Self-Balancing Daily Bed Census Reconciliation', false);
                $response->assertSee('Section B &amp; C — Maternity, Surgical Deliveries &amp; Ward Distribution', false);
            } elseif ($dept === 'laboratory') {
                $response->assertSee('Section A — Laboratory Diagnostic Volume by Source', false);
                $response->assertSee('Section E.1 — Incomplete &amp; Delayed Tests Logbook', false);
            }
        }
    }

    public function test_dedicated_direct_download_generates_and_streams_official_pdf_with_audit_trail(): void
    {
        $admin = User::where('role', 'admin')->first();
        $report = DailyReport::where('department', 'laboratory')->first();
        $this->assertNotNull($report);

        $response = $this->actingAs($admin)->get(route('reports.download', $report));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');

        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('attachment; filename=', $disposition);
        $this->assertStringContainsString('.pdf', $disposition);
        $this->assertStringContainsString($report->report_date->toDateString(), $disposition);
        $this->assertStringContainsString('laboratory', $disposition);

        // Content must be valid PDF starting with %PDF-
        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $content);

        // Audit log must exist
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'DOWNLOAD',
            'entity_type' => 'DailyReport',
            'entity_id' => $report->id,
            'department' => 'laboratory',
        ]);
    }
}
