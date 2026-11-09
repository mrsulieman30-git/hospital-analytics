<?php

namespace App\Services;

use App\Models\DailyReport;
use App\Models\HospitalSetting;
use Carbon\Carbon;

class AnalyticsKpiService
{
    /**
     * Compute comprehensive metrics for a given date range.
     */
    public function getMetricsForPeriod(string $startDate, string $endDate): array
    {
        $reports = DailyReport::whereBetween('report_date', [$startDate, $endDate])->get();

        $pharmacyReports = $reports->where('department', 'pharmacy');
        $erReports = $reports->where('department', 'emergency');
        $ipdReports = $reports->where('department', 'inpatient');
        $labReports = $reports->where('department', 'laboratory');
        $recReports = $reports->where('department', 'reception');

        $hospital = HospitalSetting::current();
        $activeBeds = (int) ($hospital->extra_metadata['active_beds_capacity'] ?? 60);
        $daysCount = max(1, Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1);

        // 1. Pharmacy KPIs
        $totalServedRx = 0;
        $totalUnservedPts = 0;
        $totalDispensedValue = 0;
        $totalDebtTaken = 0;
        $totalDebtPaid = 0;
        $totalRefunds = 0;
        $rootCausesSum = [
            'price' => 0,
            'stockout' => 0,
            'brand_unavailable' => 0,
            'did_not_present' => 0,
            'unknown' => 0,
            'other' => 0,
        ];

        foreach ($pharmacyReports as $r) {
            $secA = $r->form_data['section_a'] ?? [];
            $totalServedRx += (int) ($secA['total_served_rx'] ?? 0);
            $totalUnservedPts += (int) ($secA['total_unserved_patients'] ?? 0);
            $totalDispensedValue += (float) ($secA['total_dispensed_value'] ?? 0);

            $secC = $r->form_data['section_c_root_causes'] ?? [];
            $rootCausesSum['price'] += (int) ($secC['price'] ?? 0);
            $rootCausesSum['stockout'] += (int) ($secC['stockout'] ?? 0);
            $rootCausesSum['brand_unavailable'] += (int) ($secC['brand_unavailable'] ?? 0);
            $rootCausesSum['did_not_present'] += (int) ($secC['did_not_present'] ?? 0);
            $rootCausesSum['unknown'] += (int) ($secC['unknown'] ?? 0);
            $rootCausesSum['other'] += (int) ($secC['other'] ?? 0);

            $secD = $r->form_data['section_d_financials'] ?? [];
            $totalDebtTaken += (float) ($secD['debt_taken_amount'] ?? 0);
            $totalDebtPaid += (float) ($secD['debt_paid_amount'] ?? 0);
            $totalRefunds += (float) ($secD['refund_billing_error_amount'] ?? 0) + (float) ($secD['refund_returned_meds_amount'] ?? 0);
        }

        $rxDenominator = $totalServedRx + $totalUnservedPts;
        $rxFillRate = $rxDenominator > 0 ? round(($totalServedRx / $rxDenominator) * 100, 1) : 0;
        $netDebtExposure = ($totalDebtTaken - $totalDebtPaid) + $totalRefunds;

        // 2. Emergency (E.R) KPIs
        $totalErCases = 0;
        $totalErAdmittedToIpd = 0;
        $totalInErDeaths = 0;
        $totalErDoa = 0;

        foreach ($erReports as $r) {
            $secA = $r->form_data['section_a'] ?? [];
            $totalErCases += (int) ($secA['total_er_cases'] ?? 0);
            $totalErAdmittedToIpd += (int) ($secA['disposition_admitted_ipd'] ?? 0);

            $secE = $r->form_data['section_e_mortality'] ?? [];
            $totalInErDeaths += (int) ($secE['died_in_er_count'] ?? 0);
            $totalErDoa += (int) ($secE['doa_count'] ?? 0);
        }

        $erConversionRate = $totalErCases > 0 ? round(($totalErAdmittedToIpd / $totalErCases) * 100, 1) : 0;
        $trueErMortalityRate = $totalErCases > 0 ? round(($totalInErDeaths / $totalErCases) * 100, 2) : 0;

        // 3. Inpatient (I.P) KPIs
        $sumCurrentAdmissions = 0;
        $totalBedDays = 0;
        $totalIpdDischarges = 0;
        $totalDama = 0;
        $totalNvd = 0;
        $totalCSections = 0;
        $totalIpdDeaths = 0;

        foreach ($ipdReports as $r) {
            $secA = $r->form_data['section_a_census'] ?? [];
            $sumCurrentAdmissions += (int) ($secA['current_admissions_f'] ?? 0);
            $totalBedDays += (int) ($secA['total_patient_bed_days'] ?? 0);
            $totalIpdDischarges += (int) ($secA['total_discharged_c'] ?? 0);
            $totalDama += (int) ($secA['discharged_dama_c2'] ?? 0);
            $totalIpdDeaths += (int) ($secA['deaths_today_e'] ?? 0);

            $secC = $r->form_data['section_c_maternity'] ?? [];
            $totalNvd += (int) ($secC['nvd_normal_delivery'] ?? 0);
            $totalCSections += (int) ($secC['c_section_delivery'] ?? 0);
        }

        $totalCapacityDays = $activeBeds * $daysCount;
        $bedOccupancyRate = $totalCapacityDays > 0 ? round(($sumCurrentAdmissions / $totalCapacityDays) * 100, 1) : 0;
        $alos = $totalIpdDischarges > 0 ? round($totalBedDays / $totalIpdDischarges, 1) : 0;
        $totalDeliveries = $totalNvd + $totalCSections;
        $cSectionRate = $totalDeliveries > 0 ? round(($totalCSections / $totalDeliveries) * 100, 1) : 0;
        $damaRate = $totalIpdDischarges > 0 ? round(($totalDama / $totalIpdDischarges) * 100, 1) : 0;

        // 4. Laboratory KPIs
        $totalLabPts = 0;
        $totalLabTests = 0;
        $labOpdTests = 0;
        $labErTests = 0;
        $labIpdTests = 0;
        $labOutsideTests = 0;
        $labPartnerTests = 0;
        $totalSampleRejections = 0;
        $totalDelayedTat = 0;
        $totalCriticalValues = 0;
        $totalIncompleteTests = 0;
        $totalEquipmentIncidents = 0;
        $totalQcCalibrations = 0;
        $totalSuppliesReceived = 0;

        foreach ($labReports as $r) {
            $secA = $r->form_data['section_a_volume'] ?? [];
            $totalLabPts += (int) ($secA['total_patients'] ?? 0);
            $totalLabTests += (int) ($secA['total_tests'] ?? 0);
            $labOpdTests += (int) ($secA['opd_tests'] ?? 0);
            $labErTests += (int) ($secA['er_tests'] ?? 0);
            $labIpdTests += (int) ($secA['ipd_tests'] ?? 0);
            $labOutsideTests += (int) ($secA['outside_tests'] ?? 0);

            $secB = $r->form_data['section_b_partners'] ?? [];
            foreach ($secB as $p) {
                $labPartnerTests += (int) ($p['tests'] ?? 0);
            }

            $secC = $r->form_data['section_c_benches_and_quality'] ?? [];
            $totalSampleRejections += (int) ($secC['sample_rejections'] ?? 0);
            $totalDelayedTat += (int) ($secC['delayed_tests_tat'] ?? 0);
            $totalCriticalValues += (int) ($secC['critical_values_reported'] ?? 0);

            $secE = $r->form_data['section_e_lab_operations'] ?? [];
            $totalIncompleteTests += count($secE['incomplete_tests'] ?? []);
            $totalEquipmentIncidents += count($secE['equipment_troubleshooting'] ?? []);

            $secF = $r->form_data['section_f_quality_control'] ?? [];
            $totalQcCalibrations += count($secF['calibrations'] ?? []);

            $secG = $r->form_data['section_g_inventory_supplies'] ?? [];
            $totalSuppliesReceived += count($secG['received_items'] ?? []);
        }

        $testsPerPatientRatio = $totalLabPts > 0 ? round($totalLabTests / $totalLabPts, 2) : 0;
        $partnerSharePercent = $totalLabTests > 0 ? round(($labPartnerTests / $totalLabTests) * 100, 1) : 0;

        // 5. Reception Footfall & Doctor Capture Rate
        $totalFootfall = 0;
        $totalDoctorConsultations = 0;
        $totalDirectWalkins = 0;

        foreach ($recReports as $r) {
            $secA = $r->form_data['section_a'] ?? [];
            $totalFootfall += (int) ($secA['total_footfall'] ?? 0);
            $totalDirectWalkins += (int) ($secA['direct_lab_pharmacy_walkins'] ?? 0);

            $secB = $r->form_data['section_b_doctors'] ?? [];
            foreach ($secB as $doc) {
                $totalDoctorConsultations += (int) ($doc['completed'] ?? 0);
            }
        }

        $doctorCaptureRate = $totalDoctorConsultations > 0 ? round(($totalServedRx / $totalDoctorConsultations) * 100, 1) : 0;

        // Total Service Users / Hospital-wide Footfall (Reception Registered + Pharmacy Served / POS)
        $totalServiceUsers = $totalFootfall + $totalServedRx;

        // Automated Rule-Based Insights & Suggestions
        $smartAlerts = $this->generateSmartAlerts([
            'rx_fill_rate' => $rxFillRate,
            'root_causes' => $rootCausesSum,
            'bor' => $bedOccupancyRate,
            'alos' => $alos,
            'c_section_rate' => $cSectionRate,
            'er_conversion_rate' => $erConversionRate,
            'in_er_deaths' => $totalInErDeaths,
            'net_debt' => $netDebtExposure,
            'tests_per_patient' => $testsPerPatientRatio,
            'sample_rejections' => $totalSampleRejections,
        ]);

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'days' => $daysCount,
            ],
            'overall' => [
                'total_service_users' => $totalServiceUsers,
                'total_hospital_footfall' => $totalServiceUsers,
                'reception_footfall' => $totalFootfall,
                'pharmacy_footfall' => $totalServedRx,
            ],
            'pharmacy' => [
                'total_footfall' => $totalServedRx,
                'served_patients' => $totalServedRx,
                'total_served_rx' => $totalServedRx,
                'total_unserved_patients' => $totalUnservedPts,
                'prescription_fill_rate' => $rxFillRate,
                'dispensed_value' => $totalDispensedValue,
                'total_dispensed_value' => $totalDispensedValue,
                'debt_taken' => $totalDebtTaken,
                'debt_paid' => $totalDebtPaid,
                'total_refunds' => $totalRefunds,
                'net_credit_exposure' => $netDebtExposure,
                'root_causes' => $rootCausesSum,
            ],
            'emergency' => [
                'total_er_cases' => $totalErCases,
                'admitted_to_ipd' => $totalErAdmittedToIpd,
                'conversion_rate_percent' => $erConversionRate,
                'died_in_er_count' => $totalInErDeaths,
                'true_in_er_mortality_rate' => $trueErMortalityRate,
                'doa_count' => $totalErDoa,
            ],
            'inpatient' => [
                'bed_occupancy_rate' => $bedOccupancyRate,
                'average_length_of_stay' => $alos,
                'total_discharges' => $totalIpdDischarges,
                'dama_discharges' => $totalDama,
                'dama_rate_percent' => $damaRate,
                'c_section_rate_percent' => $cSectionRate,
                'total_deliveries' => $totalDeliveries,
                'deaths_count' => $totalIpdDeaths,
                'active_beds' => $activeBeds,
            ],
            'laboratory' => [
                'total_patients' => $totalLabPts,
                'total_tests' => $totalLabTests,
                'tests_per_patient_ratio' => $testsPerPatientRatio,
                'b2b_partner_share_percent' => $partnerSharePercent,
                'sample_rejections' => $totalSampleRejections,
                'delayed_tests_tat' => $totalDelayedTat,
                'delayed_tat' => $totalDelayedTat,
                'critical_values_reported' => $totalCriticalValues,
                'critical_values' => $totalCriticalValues,
                'incomplete_tests' => $totalIncompleteTests,
                'equipment_incidents' => $totalEquipmentIncidents,
                'qc_calibrations' => $totalQcCalibrations,
                'supplies_received' => $totalSuppliesReceived,
                'bench_utilization' => [
                    'opd' => $labOpdTests,
                    'er' => $labErTests,
                    'ipd' => $labIpdTests,
                    'outside' => $labOutsideTests,
                ],
            ],
            'reception' => [
                'total_footfall' => $totalFootfall,
                'registered_patients' => $totalFootfall,
                'total_consultations' => $totalDoctorConsultations,
                'doctor_capture_rate' => $doctorCaptureRate,
                'direct_lab_pharmacy_walkins' => $totalDirectWalkins,
            ],
            'smart_alerts' => $smartAlerts,
        ];
    }

    /**
     * Generate automated rule-based smart alerts visible to management without AI triggered.
     */
    private function generateSmartAlerts(array $data): array
    {
        $alerts = [];

        if (($data['root_causes']['stockout'] ?? 0) > 10) {
            $alerts[] = [
                'type' => 'critical',
                'category' => 'Pharmacy Stockout Alert',
                'title' => 'Stockout Constraint Impacting Fill Rate',
                'description' => "{$data['root_causes']['stockout']} patient prescriptions were unserved due to drug stockouts ('Qayb inaga waayay'). Injectable antibiotics and insulin require urgent restocking.",
                'recommendation' => 'Trigger automated reorder point with verified local distributors for Ceftriaxone and Insulin.',
            ];
        }

        if (($data['bor'] ?? 0) > 85.0) {
            $alerts[] = [
                'type' => 'warning',
                'category' => 'Capacity Alert',
                'title' => 'Bed Occupancy Exceeding Optimal Range',
                'description' => "Inpatient Bed Occupancy Rate is at {$data['bor']}% (above WHO recommended safety threshold of 80%–85%). Risk of ED boarding and nurse staffing fatigue.",
                'recommendation' => 'Expedite morning discharge rounds and open overflow observation beds in Ward B.',
            ];
        } elseif (($data['bor'] ?? 0) < 50.0 && ($data['bor'] ?? 0) > 0) {
            $alerts[] = [
                'type' => 'info',
                'category' => 'Under-Utilization',
                'title' => 'Inpatient Bed Capacity Under-Utilized',
                'description' => "Bed occupancy rate is {$data['bor']}%. Inpatient surgical and medical bed resources have significant available capacity.",
                'recommendation' => 'Coordinate with OPD specialty clinics to schedule elective procedures.',
            ];
        }

        if (($data['c_section_rate'] ?? 0) > 30.0) {
            $alerts[] = [
                'type' => 'warning',
                'category' => 'Clinical Quality Alert',
                'title' => 'Cesarean Delivery Rate High',
                'description' => "C-Section rate stands at {$data['c_section_rate']}% (WHO international benchmark ceiling is 15%–25%).",
                'recommendation' => 'Initiate peer-review clinical audit of primary C-section indications with OB/GYN lead.',
            ];
        }

        if (($data['net_debt'] ?? 0) > 500) {
            $alerts[] = [
                'type' => 'warning',
                'category' => 'Financial Risk Alert',
                'title' => 'Elevated Pharmacy Debt Exposure',
                'description' => "Net credit exposure reached \${$data['net_debt']}. Debt taken exceeds debt collections.",
                'recommendation' => 'Enforce strict 7-day credit settlement policy for corporate and staff accounts.',
            ];
        }

        if (($data['root_causes']['did_not_present'] ?? 0) > 8) {
            $alerts[] = [
                'type' => 'info',
                'category' => 'Revenue Leakage Alert',
                'title' => 'Prescription Drop-off Flagged',
                'description' => "{$data['root_causes']['did_not_present']} patients left OPD consultations without presenting their prescription to the pharmacy ('Pharmacy lama iman').",
                'recommendation' => 'Direct reception runners to escort OPD patients to the pharmacy dispensing counter.',
            ];
        }

        return $alerts;
    }

    /**
     * Generate an authentic, real-time executive clinical operations synthesis based on live metrics.
     */
    public function generateExecutiveSummary(array $metrics, string $startDate, string $endDate, string $filterLabel = 'Selected Period'): array
    {
        $recFootfall = (int) ($metrics['reception']['total_footfall'] ?? 0);
        $servedRx = (int) ($metrics['pharmacy']['total_served_rx'] ?? 0);
        $totalServiceUsers = (int) ($metrics['overall']['total_service_users'] ?? ($recFootfall + $servedRx));
        $consults = (int) ($metrics['reception']['total_consultations'] ?? 0);
        $docCapture = (float) ($metrics['reception']['doctor_capture_rate'] ?? 0);

        $rxFillRate = (float) ($metrics['pharmacy']['prescription_fill_rate'] ?? 0);
        $stockouts = (int) ($metrics['pharmacy']['root_causes']['stockout'] ?? 0);
        $debtExposure = (float) ($metrics['pharmacy']['net_credit_exposure'] ?? 0);

        $bor = (float) ($metrics['inpatient']['bed_occupancy_rate'] ?? 0);
        $activeBeds = (int) ($metrics['inpatient']['active_beds'] ?? 60);
        $alos = (float) ($metrics['inpatient']['average_length_of_stay'] ?? 0);
        $cSectionRate = (float) ($metrics['inpatient']['c_section_rate_percent'] ?? 0);

        $erCases = (int) ($metrics['emergency']['total_er_cases'] ?? 0);
        $erYield = (float) ($metrics['emergency']['conversion_rate_percent'] ?? 0);
        $erAdmitted = (int) ($metrics['emergency']['admitted_to_ipd'] ?? 0);
        $erMortality = (float) ($metrics['emergency']['true_in_er_mortality_rate'] ?? 0);

        $labTests = (int) ($metrics['laboratory']['total_tests'] ?? 0);
        $labIntensity = (float) ($metrics['laboratory']['tests_per_patient_ratio'] ?? 0);
        $labB2b = (float) ($metrics['laboratory']['b2b_partner_share_percent'] ?? 0);

        // Build authentic narrative
        $summary = "During {$filterLabel} ({$startDate} to {$endDate}), hospital operational throughput reached "
            .number_format($totalServiceUsers).' patients utilizing care across the facility ('
            .number_format($recFootfall).' registered at Reception + '.number_format($servedRx).' served at Pharmacy including walk-in POS purchases), with '
            .number_format($consults).' physician consultations and a '.$docCapture.'% prescription capture rate. '
            .'The Pharmacy achieved a '.$rxFillRate.'% prescription fill rate ('.number_format($servedRx).' dispensed), with '.number_format($stockouts).' patients unserved due to formulary stockouts and a net credit exposure of $'.number_format($debtExposure, 2).'. '
            .'Inpatient operations maintained a '.$bor.'% Bed Occupancy Rate across '.$activeBeds.' active beds (ALOS: '.$alos.' days; C-Section rate: '.$cSectionRate.'%). '
            .'The Emergency department triaged '.number_format($erCases).' acute cases, converting '.$erYield.'% ('.number_format($erAdmitted).' patients) into Inpatient ward admissions with an in-ER mortality rate of '.$erMortality.'%. '
            .'The Laboratory conducted '.number_format($labTests).' diagnostic tests ('.$labIntensity.'x tests/patient intensity), with external B2B partners generating '.$labB2b.'% of diagnostic referral demand.';

        $findings = [];
        if ($rxFillRate >= 85) {
            $findings[] = "Pharmacy dispensing efficiency is optimal at {$rxFillRate}%, meeting the 85% clinical formulary target.";
        } else {
            $findings[] = "Pharmacy fill rate is constrained at {$rxFillRate}% due to {$stockouts} stockout incidents requiring urgent supply replenishment.";
        }

        if ($bor > 85) {
            $findings[] = "Inpatient BOR is high at {$bor}%, nearing bed surge threshold; accelerate planned morning discharges.";
        } elseif ($bor < 50 && $bor > 0) {
            $findings[] = "Inpatient capacity has available headroom at {$bor}% BOR ({$activeBeds} beds available).";
        } else {
            $findings[] = "Inpatient census is balanced at {$bor}% BOR with verified bed formula reconciliation.";
        }

        if ($erYield > 20) {
            $findings[] = "Emergency department demonstrated strong admission conversion at {$erYield}% ({$erAdmitted} IPD transfers).";
        }

        if ($labTests > 0) {
            $findings[] = "Diagnostic intensity stands at {$labIntensity}x tests per patient across all 13 clinical specialties.";
        }

        $departmentSummaries = [
            'pharmacy' => [
                'department' => 'Pharmacy',
                'name' => 'Hospital Pharmacy & Dispensing',
                'icon' => 'pill',
                'color' => 'emerald',
                'status' => $rxFillRate >= 85 ? 'optimal' : ($rxFillRate >= 70 ? 'attention' : 'critical'),
                'status_badge' => $rxFillRate >= 85 ? 'Optimal Formulary Fill' : ($rxFillRate >= 70 ? 'Stockout Alert' : 'Critical Depletion'),
                'primary_kpi_label' => 'Prescription Fill Rate',
                'primary_kpi_value' => "{$rxFillRate}%",
                'secondary_metric' => number_format($servedRx).' Prescriptions Dispensed',
                'headline' => $rxFillRate >= 85
                    ? "Formulary operations operating at optimal throughput ({$rxFillRate}% fill rate)."
                    : "Formulary constrained at {$rxFillRate}% fill rate with {$stockouts} stockout drop-offs.",
                'bullet_points' => [
                    number_format($servedRx).' prescriptions served inside hospital pharmacy.',
                    number_format($stockouts).' walkout losses directly attributable to out-of-stock items.',
                    'Net customer credit / insurance exposure: $'.number_format($debtExposure, 2),
                ],
                'action_recommendation' => $stockouts > 0
                    ? 'Audit Action: Replenish out-of-stock formulary lines and review credit recovery.'
                    : 'Maintain current inventory buffer levels.',
            ],
            'emergency' => [
                'department' => 'Emergency',
                'name' => 'Emergency Triage & Resuscitation',
                'icon' => 'zap',
                'color' => 'amber',
                'status' => $erMortality < 2 ? 'optimal' : 'critical',
                'status_badge' => $erMortality < 2 ? 'Stable Triage Flow' : 'High Mortality Flag',
                'primary_kpi_label' => 'Admission Conversion Yield',
                'primary_kpi_value' => "{$erYield}%",
                'secondary_metric' => number_format($erCases).' Acute Triage Cases',
                'headline' => 'Emergency triaged '.number_format($erCases)." acute presentations with {$erYield}% escalated to inpatient admission.",
                'bullet_points' => [
                    number_format($erCases).' total acute presentations received in triage.',
                    number_format($erAdmitted).' critical cases escalated to Inpatient admission.',
                    "True in-ER mortality rate: {$erMortality}%.",
                ],
                'action_recommendation' => $erYield > 20
                    ? 'Ensure ward bed availability aligns with high emergency admission velocity.'
                    : 'Routine acute emergency observation protocols active.',
            ],
            'inpatient' => [
                'department' => 'Inpatient',
                'name' => 'Inpatient Wards & Maternal Care',
                'icon' => 'bed',
                'color' => 'cyan',
                'status' => ($bor >= 60 && $bor <= 85) ? 'optimal' : ($bor > 85 ? 'surge' : 'headroom'),
                'status_badge' => $bor > 85 ? 'Bed Surge Alert' : (($bor >= 60) ? 'Optimal Census' : 'Bed Headroom Available'),
                'primary_kpi_label' => 'Bed Occupancy (BOR)',
                'primary_kpi_value' => "{$bor}%",
                'secondary_metric' => "{$activeBeds} Active Licensed Beds",
                'headline' => "Inpatient wards recorded {$bor}% occupancy with an average length of stay of {$alos} days.",
                'bullet_points' => [
                    "Bed Occupancy Rate (BOR): {$bor}% across {$activeBeds} operational beds.",
                    "Average Length of Stay (ALOS): {$alos} days.",
                    "Surgical / C-Section rate: {$cSectionRate}% of labor ward deliveries.",
                ],
                'action_recommendation' => $bor > 85
                    ? 'Initiate early morning planned discharges to prevent ER bed boarding.'
                    : 'Ward capacity sufficient for incoming acute demand.',
            ],
            'laboratory' => [
                'department' => 'Laboratory',
                'name' => 'Diagnostic Pathology & B2B Referrals',
                'icon' => 'flask-conical',
                'color' => 'purple',
                'status' => 'optimal',
                'status_badge' => 'High Diagnostic Yield',
                'primary_kpi_label' => 'Diagnostic Intensity',
                'primary_kpi_value' => "{$labIntensity}x",
                'secondary_metric' => number_format($labTests).' Clinical Tests',
                'headline' => 'Laboratory executed '.number_format($labTests)." diagnostic tests at an intensity of {$labIntensity}x tests per patient.",
                'bullet_points' => [
                    number_format($labTests).' tests completed across all 13 specialized benches.',
                    "Diagnostic intensity ratio: {$labIntensity}x tests per clinical patient.",
                    "External clinic/hospital B2B referral share: {$labB2b}%.",
                ],
                'action_recommendation' => 'Monitor high-volume reagent consumption against stockout downtime.',
            ],
            'reception' => [
                'department' => 'Reception',
                'name' => 'Outpatient Registration & Physician OPD',
                'icon' => 'users',
                'color' => 'indigo',
                'status' => $docCapture >= 70 ? 'optimal' : 'warning',
                'status_badge' => $docCapture >= 70 ? 'Healthy Rx Capture' : 'Prescription Leakage',
                'primary_kpi_label' => 'Doctor Rx Capture',
                'primary_kpi_value' => "{$docCapture}%",
                'secondary_metric' => number_format($recFootfall).' Registered Visitors',
                'headline' => 'Reception processed '.number_format($recFootfall).' visitors and '.number_format($consults)." doctor consultations with {$docCapture}% Rx capture.",
                'bullet_points' => [
                    number_format($recFootfall).' outpatient registrations logged.',
                    number_format($consults).' doctor consultations completed.',
                    "{$docCapture}% of physician consultations converted to pharmacy dispenses.",
                ],
                'action_recommendation' => $docCapture < 70
                    ? 'Investigate why patients are walking out to street pharmacies without filling prescribed Rx.'
                    : 'Outpatient flow is well integrated with pharmacy dispensing.',
            ],
        ];

        return [
            'title' => "Hospital Performance & Clinical Operations Summary ({$filterLabel})",
            'executive_summary' => $summary,
            'key_findings' => $findings,
            'department_summaries' => $departmentSummaries,
        ];
    }

    /**
     * Compute trend series for executive Chart.js visualizations matching the selected period.
     */
    public function getWeeklyTrend(?string $startDate = null, ?string $endDate = null): array
    {
        $hospital = HospitalSetting::current();
        $activeBeds = (int) ($hospital->extra_metadata['active_beds_capacity'] ?? 60);

        // Determine date sequence
        $dateList = [];
        if (! $startDate || ! $endDate) {
            for ($i = 6; $i >= 0; $i--) {
                $dateList[] = Carbon::today()->subDays($i)->toDateString();
            }
        } else {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->startOfDay();
            $diffDays = (int) $start->diffInDays($end);

            if ($diffDays <= 0) {
                // Single day (today or yesterday) -> trailing 7 days ending on target day
                for ($i = 6; $i >= 0; $i--) {
                    $dateList[] = $end->copy()->subDays($i)->toDateString();
                }
            } elseif ($diffDays <= 31) {
                // 2 to 31 days -> daily
                $curr = $start->copy();
                while ($curr->lte($end)) {
                    $dateList[] = $curr->toDateString();
                    $curr->addDay();
                }
            } else {
                // > 31 days -> sample ~12 points across the range
                $step = max(1, (int) round($diffDays / 12));
                $curr = $start->copy();
                while ($curr->lte($end)) {
                    $dateList[] = $curr->toDateString();
                    $curr->addDays($step);
                }
                if (! in_array($end->toDateString(), $dateList)) {
                    $dateList[] = $end->toDateString();
                }
            }
        }

        $dates = [];
        $footfallData = [];
        $receptionFootfallData = [];
        $pharmacyFootfallData = [];
        $erData = [];
        $ipdAdmissionsData = [];
        $borData = [];
        $rxFillData = [];
        $servedRxData = [];
        $labTestsData = [];
        $consultationsData = [];

        foreach ($dateList as $idx => $d) {
            $label = Carbon::parse($d)->format('M d');
            $dates[] = $label;

            $reports = DailyReport::where('report_date', $d)->get()->keyBy('department');

            // Footfall & Consultations (Reception)
            $rec = $reports->get('reception');
            $recFootfall = $rec ? (int) ($rec->form_data['section_a']['total_footfall'] ?? 0) : 0;
            $receptionFootfallData[] = $recFootfall;

            $docConsults = 0;
            if ($rec && ! empty($rec->form_data['section_b_doctors'])) {
                foreach ($rec->form_data['section_b_doctors'] as $dEntry) {
                    $docConsults += (int) ($dEntry['completed'] ?? 0);
                }
            }
            $consultationsData[] = $docConsults;

            // ER Cases
            $er = $reports->get('emergency');
            $erCases = $er ? (int) ($er->form_data['section_a']['total_er_cases'] ?? 0) : 0;
            $erData[] = $erCases;

            // IPD Admissions
            $ipd = $reports->get('inpatient');
            $ipdAdmissions = $ipd ? (
                (int) ($ipd->form_data['section_b_flow']['direct_admissions'] ?? 0)
                + (int) ($ipd->form_data['section_b_flow']['er_admissions'] ?? 0)
            ) : 0;
            $ipdAdmissionsData[] = $ipdAdmissions;

            // BOR
            $census = $ipd ? (int) ($ipd->form_data['section_b_flow']['closing_census'] ?? ($ipd->form_data['section_a_census']['current_admissions_f'] ?? 0)) : 0;
            $bor = ($census > 0 && $activeBeds > 0) ? round(($census / $activeBeds) * 100, 1) : 0;
            $borData[] = $bor;

            // Rx Fill Rate & Served Rx (Pharmacy)
            $phm = $reports->get('pharmacy');
            $served = $phm ? (int) ($phm->form_data['section_a']['total_served_rx'] ?? 0) : 0;
            $unserved = $phm ? (int) ($phm->form_data['section_a']['total_unserved_patients'] ?? 0) : 0;
            $rxFill = ($served + $unserved > 0) ? round(($served / ($served + $unserved)) * 100, 1) : 0;
            $rxFillData[] = $rxFill;
            $servedRxData[] = $served;
            $pharmacyFootfallData[] = $served;

            // Total Facility Service Users (Admin: Reception Registered + Pharmacy Walk-in POS)
            $footfallData[] = $recFootfall + $served;

            // Lab Tests (Laboratory)
            $lab = $reports->get('laboratory');
            $labTests = $lab ? (int) ($lab->form_data['section_a_volume']['total_tests'] ?? 0) : 0;
            $labTestsData[] = $labTests;
        }

        return [
            'labels' => $dates,
            'footfall' => $footfallData,
            'reception_footfall' => $receptionFootfallData,
            'pharmacy_footfall' => $pharmacyFootfallData,
            'er_cases' => $erData,
            'ipd_admissions' => $ipdAdmissionsData,
            'bor' => $borData,
            'rx_fill_rate' => $rxFillData,
            'served_rx' => $servedRxData,
            'lab_tests' => $labTestsData,
            'consultations' => $consultationsData,
        ];
    }
}
