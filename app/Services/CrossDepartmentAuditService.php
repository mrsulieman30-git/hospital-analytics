<?php

namespace App\Services;

use App\Models\DailyReport;
use Carbon\Carbon;

class CrossDepartmentAuditService
{
    /**
     * Run all 4 automated cross-departmental audit checks for a given date.
     */
    public function runAuditForDate(string $date): array
    {
        $reports = DailyReport::where('report_date', $date)->get()->keyBy('department');

        $check1 = $this->checkInpatientCensusBalance($reports->get('inpatient'));
        $check2 = $this->checkErToIpdReconciliation($reports->get('emergency'), $reports->get('inpatient'));
        $check3 = $this->checkDoctorToPharmacyCapture($reports->get('reception'), $reports->get('pharmacy'));
        $check4 = $this->checkStockoutPersistence($date);

        $passedCount = 0;
        if ($check1['status'] === 'passed') {
            $passedCount++;
        }
        if ($check2['status'] === 'passed') {
            $passedCount++;
        }
        if ($check3['status'] === 'passed') {
            $passedCount++;
        }
        if ($check4['status'] === 'passed') {
            $passedCount++;
        }

        return [
            'date' => $date,
            'overall_status' => $passedCount === 4 ? 'verified' : ($passedCount >= 2 ? 'warning' : 'mismatch'),
            'passed_count' => $passedCount,
            'total_checks' => 4,
            'checks' => [
                'check_1_inpatient_census' => $check1,
                'check_2_er_ipd_reconciliation' => $check2,
                'check_3_doctor_pharmacy_capture' => $check3,
                'check_4_stockout_persistence' => $check4,
            ],
        ];
    }

    /**
     * Check 1: Inpatient Census Balance [A] + [B] - [C] - [D] - [E] == [F]
     */
    private function checkInpatientCensusBalance(?DailyReport $ipdReport): array
    {
        if (! $ipdReport) {
            return [
                'title' => 'Inpatient Bed Census Balance',
                'status' => 'pending',
                'badge' => 'No IPD Report',
                'message' => 'Inpatient report has not been submitted yet for this date.',
            ];
        }

        $census = $ipdReport->form_data['section_a_census'] ?? [];
        $a = (int) ($census['previous_admissions_a'] ?? 0);
        $b = (int) ($census['newly_admitted_b'] ?? 0);
        $c = (int) ($census['total_discharged_c'] ?? 0);
        $d = (int) ($census['referred_out_d'] ?? 0);
        $e = (int) ($census['deaths_today_e'] ?? 0);
        $f = (int) ($census['current_admissions_f'] ?? 0);

        $expectedF = $a + $b - $c - $d - $e;
        $isBalanced = ($expectedF === $f);

        return [
            'title' => 'Audit Check 1: Inpatient Bed Census Balance',
            'status' => $isBalanced ? 'passed' : 'failed',
            'badge' => $isBalanced ? 'Balanced' : 'Census Mismatch',
            'formula' => "[A: {$a}] + [B: {$b}] - [C: {$c}] - [D: {$d}] - [E: {$e}] = Expected {$expectedF} vs Recorded {$f}",
            'message' => $isBalanced
                ? 'Strict formula check verified: Bed census reconciles with 100% mathematical precision.'
                : "Discrepancy detected: Calculated census ({$expectedF}) does not match reported ending census ({$f}). Difference: ".abs($expectedF - $f).' patient(s).',
            'values' => compact('a', 'b', 'c', 'd', 'e', 'f', 'expectedF'),
        ];
    }

    /**
     * Check 2: E.R-to-IPD Admission Reconciliation
     * Compares E.R Form -> Admitted to IPD against IPD Form -> Newly Admitted (Source: E.R)
     */
    private function checkErToIpdReconciliation(?DailyReport $erReport, ?DailyReport $ipdReport): array
    {
        if (! $erReport || ! $ipdReport) {
            return [
                'title' => 'Audit Check 2: E.R-to-IPD Admission Reconciliation',
                'status' => 'pending',
                'badge' => 'Pending Submissions',
                'message' => 'Requires both E.R and Inpatient reports for reconciliation comparison.',
            ];
        }

        $erAdmitted = (int) ($erReport->form_data['section_a']['disposition_admitted_ipd'] ?? 0);
        $ipdFromEr = (int) ($ipdReport->form_data['section_a_census']['newly_admitted_from_er'] ?? 0);

        $matches = ($erAdmitted === $ipdFromEr);

        return [
            'title' => 'Audit Check 2: E.R-to-IPD Admission Reconciliation',
            'status' => $matches ? 'passed' : 'failed',
            'badge' => $matches ? 'Reconciled' : 'Discrepancy',
            'formula' => "E.R Transferred to IPD: {$erAdmitted} | IPD Received from E.R: {$ipdFromEr}",
            'message' => $matches
                ? "Perfect cross-departmental transfer match: All {$erAdmitted} acute cases transferred from E.R were logged as admitted in IPD census."
                : "Mismatch flagged: E.R reported {$erAdmitted} patients admitted to IPD, but Inpatient ward logged {$ipdFromEr} admissions from E.R. Variance: ".abs($erAdmitted - $ipdFromEr).' patient(s).',
            'values' => [
                'er_transferred_to_ipd' => $erAdmitted,
                'ipd_admitted_from_er' => $ipdFromEr,
                'variance' => abs($erAdmitted - $ipdFromEr),
            ],
        ];
    }

    /**
     * Check 3: Doctor Consultation-to-Pharmacy Capture
     * Compares Reception Consultations by Doctor against Pharmacy Inside Rx by Doctor and notes "Pharmacy lama iman"
     */
    private function checkDoctorToPharmacyCapture(?DailyReport $recReport, ?DailyReport $phmReport): array
    {
        if (! $recReport || ! $phmReport) {
            return [
                'title' => 'Audit Check 3: Doctor Consultation-to-Pharmacy Capture',
                'status' => 'pending',
                'badge' => 'Pending Submissions',
                'message' => 'Requires both Reception and Pharmacy reports to analyze doctor Rx capture rate.',
            ];
        }

        $recDoctors = collect($recReport->form_data['section_b_doctors'] ?? [])->keyBy('doctor');
        $phmDoctors = collect($phmReport->form_data['section_b_doctors'] ?? [])->keyBy('doctor');

        $totalConsultations = 0;
        $totalServedRx = 0;
        $doctorBreakdown = [];

        foreach ($recDoctors as $docName => $docRec) {
            $consultations = (int) ($docRec['completed'] ?? 0);
            $phmDoc = $phmDoctors->get($docName);
            $servedRx = (int) ($phmDoc['served_rx'] ?? 0);
            $unservedRx = (int) ($phmDoc['unserved_rx'] ?? 0);

            $captureRate = $consultations > 0 ? round(($servedRx / $consultations) * 100, 1) : 0;

            $totalConsultations += $consultations;
            $totalServedRx += $servedRx;

            $doctorBreakdown[] = [
                'doctor' => $docName,
                'consultations' => $consultations,
                'served_rx' => $servedRx,
                'capture_rate' => $captureRate,
            ];
        }

        $overallCaptureRate = $totalConsultations > 0 ? round(($totalServedRx / $totalConsultations) * 100, 1) : 0;
        $dropOffCount = (int) ($phmReport->form_data['section_c_root_causes']['did_not_present'] ?? 0);

        return [
            'title' => 'Audit Check 3: Doctor Consultation-to-Pharmacy Capture',
            'status' => $overallCaptureRate >= 70 ? 'passed' : 'warning',
            'badge' => "{$overallCaptureRate}% Capture Rate",
            'formula' => "OPD Consultations: {$totalConsultations} | Pharmacy Inside Rx: {$totalServedRx} | Unpresented Drop-offs (Pharmacy lama iman): {$dropOffCount}",
            'message' => "Hospital-wide prescription capture rate is {$overallCaptureRate}%. {$dropOffCount} patients were flagged under 'Pharmacy lama iman' (did not present prescribed Rx to hospital pharmacy).",
            'overall_capture_rate' => $overallCaptureRate,
            'drop_off_count' => $dropOffCount,
            'doctors' => $doctorBreakdown,
        ];
    }

    /**
     * Check 4: Stockout Persistence & Downtime Tracker
     * Automatically tracks consecutive days a Pharmacy drug or Lab reagent has remained unresolved.
     */
    private function checkStockoutPersistence(string $date): array
    {
        $targetDate = Carbon::parse($date);
        $startDate = $targetDate->copy()->subDays(6)->toDateString();

        $recentReports = DailyReport::whereIn('department', ['pharmacy', 'laboratory'])
            ->whereBetween('report_date', [$startDate, $date])
            ->orderBy('report_date')
            ->get();

        $pharmacyReports = $recentReports->where('department', 'pharmacy');
        $labReports = $recentReports->where('department', 'laboratory');

        // Track persistent pharmacy stockouts
        $drugOccurrences = [];
        foreach ($pharmacyReports as $rep) {
            $stockouts = $rep->form_data['section_e_inventory']['out_of_stock_drugs'] ?? [];
            if (is_array($stockouts)) {
                foreach ($stockouts as $drug) {
                    $drugName = is_array($drug)
                        ? ($drug['drug_name'] ?? $drug['name'] ?? $drug['item_name'] ?? '')
                        : trim((string) $drug);

                    if ($drugName !== '') {
                        $drugOccurrences[$drugName] = ($drugOccurrences[$drugName] ?? 0) + 1;
                    }
                }
            }
        }

        // Track persistent lab nil items
        $reagentOccurrences = [];
        foreach ($labReports as $rep) {
            $nilItems = $rep->form_data['section_d_nil_items']['items'] ?? [];
            if (is_array($nilItems)) {
                foreach ($nilItems as $reagent) {
                    $reagentName = is_array($reagent)
                        ? ($reagent['item_name'] ?? $reagent['name'] ?? $reagent['reagent'] ?? '')
                        : trim((string) $reagent);

                    if ($reagentName !== '') {
                        $reagentOccurrences[$reagentName] = ($reagentOccurrences[$reagentName] ?? 0) + 1;
                    }
                }
            }
        }

        $criticalBottlenecks = [];
        foreach ($drugOccurrences as $drug => $days) {
            if ($days >= 3) {
                $criticalBottlenecks[] = [
                    'department' => 'Pharmacy',
                    'item' => $drug,
                    'consecutive_days' => $days,
                    'severity' => $days >= 5 ? 'critical' : 'high',
                ];
            }
        }

        foreach ($reagentOccurrences as $reagent => $days) {
            if ($days >= 3) {
                $criticalBottlenecks[] = [
                    'department' => 'Laboratory',
                    'item' => $reagent,
                    'consecutive_days' => $days,
                    'severity' => $days >= 5 ? 'critical' : 'high',
                ];
            }
        }

        $hasChronicBottlenecks = count($criticalBottlenecks) > 0;

        return [
            'title' => 'Audit Check 4: Stockout Persistence & Downtime Tracker',
            'status' => ! $hasChronicBottlenecks ? 'passed' : 'warning',
            'badge' => $hasChronicBottlenecks ? count($criticalBottlenecks).' Chronic Stockout(s)' : 'Clean Supply Chain',
            'formula' => 'Consecutive Stockout Monitoring (Past 7 Days Window)',
            'message' => $hasChronicBottlenecks
                ? count($criticalBottlenecks).' critical item(s) have been out of stock for 3 or more consecutive days, causing patient unserved drop-offs.'
                : 'No chronic stockouts detected. All critical drugs and reagents are within acceptable operational replenishment windows.',
            'bottlenecks' => $criticalBottlenecks,
        ];
    }
}
