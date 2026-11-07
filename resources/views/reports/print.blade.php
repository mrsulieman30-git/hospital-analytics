<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>A4 Official Report - {{ $report->form_code }} ({{ $report->report_date->toDateString() }})</title>

    <style>
        :root {
            --color-primary: {{ $hospital->primary_color ?? '#124E78' }};
            --color-secondary: {{ $hospital->secondary_color ?? '#1E293B' }};
            --color-accent: {{ $hospital->accent_color ?? '#C81D25' }};
        }

        @page {
            size: A4 portrait;
            margin: 10mm 14mm;
        }

        body {
            font-family: 'Instrument Sans', system-ui, -apple-system, sans-serif;
            color: #0F172A;
            background: #FFFFFF;
            margin: 0;
            padding: 0;
            font-size: 8.5pt;
            line-height: 1.35;
        }

        .no-print {
            background: #0F172A;
            color: #FFFFFF;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .print-btn {
            background: #124E78;
            color: #FFFFFF;
            border: none;
            padding: 6px 16px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 12px;
        }

        .a4-container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            padding: 8px;
            box-sizing: border-box;
        }

        /* Hospital Header */
        .hospital-header {
            border-bottom: 2px solid var(--color-primary);
            padding-bottom: 10px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .hospital-logo {
            height: 44px;
            width: auto;
            object-fit: contain;
        }

        .hospital-title {
            font-size: 15pt;
            font-weight: 900;
            color: var(--color-primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .hospital-tagline {
            font-size: 7.5pt;
            color: #64748B;
            margin: 2px 0 0 0;
        }

        .form-badge {
            font-family: monospace;
            font-weight: bold;
            background: #F1F5F9;
            border: 1px solid #CBD5E1;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 8.5pt;
            color: #0F172A;
        }

        /* Metadata Grid */
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 8pt;
            margin-bottom: 12px;
        }

        /* Section Banner */
        .section-banner {
            background: var(--color-primary);
            color: #FFFFFF;
            padding: 3.5px 8px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 10px;
            margin-bottom: 4px;
            display: flex;
            justify-content: space-between;
            page-break-after: avoid;
        }

        /* Booktabs Style Table */
        .booktabs-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 10px;
        }

        .booktabs-table th {
            border-top: 1.5px solid #0F172A;
            border-bottom: 1.5px solid #0F172A;
            padding: 4px 6px;
            text-align: left;
            font-weight: 700;
            background: #FAFAFA;
        }

        .booktabs-table td {
            border-bottom: 0.5px solid #E2E8F0;
            padding: 3.5px 6px;
            vertical-align: middle;
        }

        .booktabs-table tr:last-child td {
            border-bottom: 1.5px solid #0F172A;
        }

        .booktabs-table tr.total-row td {
            border-top: 1px solid #0F172A;
            border-bottom: 1.5px solid #0F172A;
            font-weight: bold;
            background: #F8FAFC;
        }

        /* Official Signature Stamps */
        .signature-block {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #CBD5E1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            font-size: 8pt;
            page-break-inside: avoid;
        }

        .stamp-box {
            border: 2px dashed #94A3B8;
            padding: 4px 10px;
            display: inline-block;
            border-radius: 6px;
            font-family: monospace;
            font-weight: bold;
            color: #124E78;
        }

        tr {
            page-break-inside: avoid;
        }

        @media print {
            .no-print { display: none !important; }
            .a4-container { padding: 0 !important; width: 100% !important; max-width: 100% !important; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <div style="display: flex; align-items: center; gap: 8px;">
            <strong>{{ $hospital->hospital_name }}</strong> &bull; Official A4 Print &amp; Archival System
            @if(isset($downloadMeta))
                <span style="font-size: 8pt; background: #1E293B; padding: 2px 8px; border-radius: 4px; color: #94A3B8; font-family: monospace;">
                    Downloaded: {{ $downloadMeta['downloaded_by'] }} &bull; {{ $downloadMeta['downloaded_at'] }}
                </span>
            @endif
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('reports.download', $report) }}" class="print-btn" style="background: #10B981; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" title="Directly download official PDF">
                <span>Download PDF</span>
            </a>
            <button onclick="window.print()" class="print-btn">Print / Save as PDF</button>
        </div>
    </div>

    <div class="a4-container">
        
        <!-- Header -->
        <div class="hospital-header">
            <div style="display: flex; align-items: center; gap: 12px;">
                <img src="{{ asset('images/kaafi_logo.png') }}" alt="Logo" class="hospital-logo">
                <div>
                    <h1 class="hospital-title">{{ $hospital->hospital_name }}</h1>
                    <p class="hospital-tagline">{{ $hospital->tagline }} &bull; {{ $hospital->address }}</p>
                </div>
            </div>
            <div style="text-align: right;">
                <div class="form-badge">FORM: {{ $report->form_code }}</div>
                <div style="font-size: 7.5pt; color: #64748B; font-family: monospace; margin-top: 3px;">
                    STATUS: {{ strtoupper($report->status) }}
                </div>
            </div>
        </div>

        <!-- Metadata -->
        <div class="meta-grid">
            <div><strong>Report Date:</strong> {{ $report->report_date->toDateString() }}</div>
            <div><strong>Shift / Cadence:</strong> {{ $report->shift }}</div>
            <div><strong>Duty Staff Lead:</strong> {{ $report->lead_staff_name }}</div>
            <div><strong>Submission Time:</strong> {{ $report->submitted_at ? $report->submitted_at->format('Y-m-d H:i') : 'Draft' }}</div>
            <div><strong>Audited By:</strong> {{ $report->auditor->name ?? 'Pending Analytics Audit' }}</div>
            <div><strong>Verified:</strong> {{ $report->audited_at ? $report->audited_at->format('Y-m-d H:i') : 'In Queue' }}</div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════
             DEPARTMENT SPECIFIC FULL DETAILED CONTENT
             ════════════════════════════════════════════════════════════════ --}}

        @if($report->department === 'reception')
            <!-- Reception Section A: Patient Registration & Footfall Throughput -->
            @php
                $secA = $report->form_data['section_a'] ?? [];
                $newOpd = (int)($secA['new_opd_registrations'] ?? 0);
                $followUp = (int)($secA['followup_opd_visits'] ?? 0);
                $routedEr = (int)($secA['emergency_walkin_routed_er'] ?? 0);
                $directWalkins = (int)($secA['direct_lab_pharmacy_walkins'] ?? 0);
                $totalRecFootfall = (int)($secA['total_footfall'] ?? ($newOpd + $followUp + $routedEr + $directWalkins));
            @endphp
            <div class="section-banner">
                <span>Section A — Patient Registration &amp; Reception Footfall Throughput</span>
                <span>Wadarta Bukaanka Diiwaangashan</span>
            </div>
            <table class="booktabs-table">
                <thead>
                    <tr>
                        <th>Registration Stream</th>
                        <th style="text-align: center; width: 90px;">Patient Count</th>
                        <th>Department Routing &amp; Operational Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1. New OPD Registrations (Diiwaangalinta Cusub ee OPD)</td>
                        <td style="text-align: center; font-weight: bold;">{{ number_format($newOpd) }}</td>
                        <td>Assigned new Hospital MRN file &amp; opened consultation chart</td>
                    </tr>
                    <tr>
                        <td>2. Follow-Up OPD Visits (Bukaankii Hore ee Soo Laabtay)</td>
                        <td style="text-align: center; font-weight: bold;">{{ number_format($followUp) }}</td>
                        <td>Scheduled specialist outpatient clinical review appointments</td>
                    </tr>
                    <tr>
                        <td>3. Emergency Walk-Ins Routed to E.R (Bukaanka Gurmadka ee loo Diray E.R)</td>
                        <td style="text-align: center; font-weight: bold; color: #C81D25;">{{ number_format($routedEr) }}</td>
                        <td>Direct triage transfer to Emergency Resuscitation Bay</td>
                    </tr>
                    <tr>
                        <td>4. Direct Lab / Pharmacy Walk-In Tickets (Tikidhada Tooska ah)</td>
                        <td style="text-align: center; font-weight: bold;">{{ number_format($directWalkins) }}</td>
                        <td>Direct service cashier tickets without physician clinical consultation</td>
                    </tr>
                    <tr class="total-row">
                        <td>TOTAL REGISTERED PATIENTS (RECEPTION FOOTFALL)</td>
                        <td style="text-align: center; font-size: 9.5pt; color: #124E78;">{{ number_format($totalRecFootfall) }}</td>
                        <td style="font-size: 7.5pt; color: #64748B;">Reconciled front desk intake (combined with Pharmacy POS on hospital-wide dashboard)</td>
                    </tr>
                </tbody>
            </table>

            <!-- Reception Section B: Outpatient Doctor Consultations Queue -->
            @php
                $secBDocs = $report->form_data['section_b_doctors'] ?? [];
                $totBooked = 0;
                $totCompleted = 0;
                $totNoshow = 0;
                foreach($secBDocs as $d) {
                    $totBooked += (int)($d['booked'] ?? 0);
                    $totCompleted += (int)($d['completed'] ?? 0);
                    $totNoshow += (int)($d['noshow'] ?? 0);
                }
                $compRate = $totBooked > 0 ? round(($totCompleted / $totBooked) * 100, 1) : ($totCompleted > 0 ? 100 : 0);
            @endphp
            <div class="section-banner">
                <span>Section B — Physician Outpatient Clinic Queue &amp; Consultations</span>
                <span>La-talinta Dhakhaatiirta (Completed: {{ $totCompleted }})</span>
            </div>
            <table class="booktabs-table">
                <thead>
                    <tr>
                        <th>Attending Physician / Specialty Clinic</th>
                        <th style="text-align: center; width: 80px;">Booked (Bkd)</th>
                        <th style="text-align: center; width: 80px;">Completed (Done)</th>
                        <th style="text-align: center; width: 80px;">No-Show</th>
                        <th style="text-align: right; width: 90px;">Completion Rate (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($secBDocs as $doc)
                        @php
                            $bkd = (int)($doc['booked'] ?? 0);
                            $done = (int)($doc['completed'] ?? 0);
                            $ns = (int)($doc['noshow'] ?? 0);
                            $rate = $bkd > 0 ? round(($done / $bkd) * 100, 1) : ($done > 0 ? 100 : 0);
                        @endphp
                        <tr>
                            <td><strong>{{ $doc['doctor'] ?? 'Physician' }}</strong></td>
                            <td style="text-align: center;">{{ $bkd }}</td>
                            <td style="text-align: center; font-weight: bold; color: #124E78;">{{ $done }}</td>
                            <td style="text-align: center; color: {{ $ns > 0 ? '#C81D25' : '#64748B' }};">{{ $ns }}</td>
                            <td style="text-align: right; font-family: monospace;">{{ $rate }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: #64748B;">No physician consultation records logged.</td>
                        </tr>
                    @endforelse
                    <tr class="total-row">
                        <td>TOTAL CLINIC CONSULTATIONS</td>
                        <td style="text-align: center;">{{ $totBooked }}</td>
                        <td style="text-align: center; color: #124E78;">{{ $totCompleted }}</td>
                        <td style="text-align: center; color: {{ $totNoshow > 0 ? '#C81D25' : '#64748B' }};">{{ $totNoshow }}</td>
                        <td style="text-align: right; font-family: monospace;">{{ $compRate }}%</td>
                    </tr>
                </tbody>
            </table>

            <!-- Reception Section C: Front Desk Delays & Incidents -->
            <div class="section-banner">
                <span>Section C — Front Desk Operational Delays, Incidents &amp; Support Requests</span>
                <span>Caqabadaha &amp; Ciladaha</span>
            </div>
            <table class="booktabs-table">
                <thead>
                    <tr>
                        <th>Operational Indicator / Incident Description</th>
                        <th style="text-align: center; width: 90px;">Incidents / Count</th>
                        <th>Root Cause &amp; Remedial Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Patients Left Due to Long Waiting (Bukaan Ka Tagay Safka Dheer)</td>
                        <td style="text-align: center; font-weight: bold; color: {{ ($report->form_data['section_c']['patients_left_due_delay'] ?? 0) > 0 ? '#C81D25' : '#0F172A' }};">
                            {{ $report->form_data['section_c']['patients_left_due_delay'] ?? 0 }}
                        </td>
                        <td>Unserved OPD departures; triage queue time monitoring required</td>
                    </tr>
                    <tr>
                        <td>System &amp; Hardware Downtime (Ciladaha Qalabka / Daabacaha)</td>
                        <td style="text-align: center; font-weight: bold; color: {{ ($report->form_data['section_c']['system_printer_issues'] ?? 0) > 0 ? '#C81D25' : '#0F172A' }};">
                            {{ $report->form_data['section_c']['system_printer_issues'] ?? 0 }}
                        </td>
                        <td>EMR workstation/printer issues reported to IT biomedical support</td>
                    </tr>
                    @if(!empty($report->form_data['section_c']['operational_notes']))
                        <tr>
                            <td>Supervisor Operational Notes</td>
                            <td colspan="2">{{ $report->form_data['section_c']['operational_notes'] }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>

        @elseif($report->department === 'pharmacy')
            <!-- Pharmacy Section A: Prescription Volume & Dispensing -->
            @php
                $secA = $report->form_data['section_a'] ?? [];
                $insideRx = (int)($secA['inside_rx_count'] ?? 0);
                $insideItems = (int)($secA['inside_items_dispensed'] ?? 0);
                $outsideRx = (int)($secA['outside_rx_count'] ?? 0);
                $outsideItems = (int)($secA['outside_items_dispensed'] ?? 0);
                $totalServedRx = (int)($secA['total_served_rx'] ?? ($insideRx + $outsideRx));
                $totalItems = (int)($secA['total_items_dispensed'] ?? ($insideItems + $outsideItems));
                $dispValue = (float)($secA['total_dispensed_value'] ?? 0);
                $unserved = (int)($secA['total_unserved_patients'] ?? 0);
                $fillRate = ($totalServedRx + $unserved) > 0 ? round(($totalServedRx / ($totalServedRx + $unserved)) * 100, 1) : 0;
            @endphp
            <div class="section-banner">
                <span>Section A — Prescription Volume &amp; Dispensing (Pharmacy Footfall)</span>
                <span>Wadarta Dawada La Bixiyay (Fill Rate: {{ $fillRate }}%)</span>
            </div>
            <table class="booktabs-table">
                <thead>
                    <tr>
                        <th>Category / Dispensing Stream</th>
                        <th style="text-align: center; width: 90px;">Rx / Patients</th>
                        <th style="text-align: center; width: 90px;">Items Dispensed</th>
                        <th style="text-align: right; width: 110px;">Dispensed Value ($)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Inside Hospital Prescriptions (OPD / ER / IPD Internal Flow)</td>
                        <td style="text-align: center; font-weight: bold;">{{ number_format($insideRx) }}</td>
                        <td style="text-align: center;">{{ number_format($insideItems) }}</td>
                        <td style="text-align: right; color: #64748B;">Internal Charge</td>
                    </tr>
                    <tr>
                        <td>Outside Prescriptions (Walk-in POS Purchases &amp; External Clinics)</td>
                        <td style="text-align: center; font-weight: bold;">{{ number_format($outsideRx) }}</td>
                        <td style="text-align: center;">{{ number_format($outsideItems) }}</td>
                        <td style="text-align: right; color: #64748B;">Direct POS Retail</td>
                    </tr>
                    <tr class="total-row">
                        <td>TOTAL SERVED PATIENTS (PHARMACY FOOTFALL)</td>
                        <td style="text-align: center; font-size: 9.5pt; color: #124E78;">{{ number_format($totalServedRx) }}</td>
                        <td style="text-align: center; font-size: 9.5pt;">{{ number_format($totalItems) }}</td>
                        <td style="text-align: right; font-size: 9.5pt; font-family: monospace; color: #10B981;">${{ number_format($dispValue, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="color: #C81D25;">Total Unserved Patients (Bukaan Aan Loo Adeegin)</td>
                        <td style="text-align: center; font-weight: bold; color: #C81D25;">{{ number_format($unserved) }}</td>
                        <td colspan="2" style="font-size: 7.5pt; color: #64748B;">
                            Prescription Fill Rate: <strong>{{ $fillRate }}%</strong> &bull; Total Demand: {{ number_format($totalServedRx + $unserved) }} Patients
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pharmacy Section B: Physician Prescription Queue -->
            @php
                $secBDocs = $report->form_data['section_b_doctors'] ?? [];
            @endphp
            @if(!empty($secBDocs))
                <div class="section-banner">
                    <span>Section B — Physician Prescription Capture Queue</span>
                    <span>Qaybta Dhakhaatiirta ee Qoray Dawada</span>
                </div>
                <table class="booktabs-table">
                    <thead>
                        <tr>
                            <th>Prescribing Physician</th>
                            <th style="text-align: center; width: 90px;">Served Rx</th>
                            <th style="text-align: center; width: 90px;">Unserved Rx</th>
                            <th style="text-align: center; width: 90px;">Total Prescriptions</th>
                            <th style="text-align: right; width: 90px;">Fill Rate (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($secBDocs as $doc)
                            @php
                                $s = (int)($doc['served_rx'] ?? 0);
                                $u = (int)($doc['unserved_rx'] ?? 0);
                                $t = $s + $u;
                                $r = $t > 0 ? round(($s / $t) * 100, 1) : 0;
                            @endphp
                            <tr>
                                <td><strong>{{ $doc['doctor'] ?? 'Doctor' }}</strong></td>
                                <td style="text-align: center; font-weight: bold; color: #124E78;">{{ $s }}</td>
                                <td style="text-align: center; color: {{ $u > 0 ? '#C81D25' : '#64748B' }};">{{ $u }}</td>
                                <td style="text-align: center;">{{ $t }}</td>
                                <td style="text-align: right; font-family: monospace;">{{ $r }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <!-- Pharmacy Section C: Root Causes for Unserved Prescriptions -->
            @php
                $rc = $report->form_data['section_c_root_causes'] ?? [];
            @endphp
            <div class="section-banner">
                <span>Section C — Root Causes for Unserved Prescriptions</span>
                <span>Sababta Bukaanka Aan Loo Adeegin</span>
            </div>
            <table class="booktabs-table">
                <thead>
                    <tr>
                        <th>Root Cause Category</th>
                        <th style="text-align: center; width: 100px;">Unserved Patients</th>
                        <th>Clinical &amp; Operational Impact</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1. Qiimaha (Financial / Price Constraint)</td>
                        <td style="text-align: center; font-weight: bold;">{{ $rc['price'] ?? 0 }}</td>
                        <td>Financial assistance / social worker referral</td>
                    </tr>
                    <tr>
                        <td>2. Qayb inaga waayay (Formulary Drug Out of Stock)</td>
                        <td style="text-align: center; font-weight: bold; color: #C81D25;">{{ $rc['stockout'] ?? 0 }}</td>
                        <td>Procurement reorder requisition submitted immediately</td>
                    </tr>
                    <tr>
                        <td>3. Nooceeda kuma qancin (Brand Preference / Unavailability)</td>
                        <td style="text-align: center;">{{ $rc['brand_unavailable'] ?? 0 }}</td>
                        <td>Alternative therapeutic brand counseling</td>
                    </tr>
                    <tr>
                        <td>4. Pharmacy lama iman (Drop-off / Did not present)</td>
                        <td style="text-align: center;">{{ $rc['did_not_present'] ?? 0 }}</td>
                        <td>OPD patient routing reminder; revenue leakage risk</td>
                    </tr>
                    <tr>
                        <td>5. Other / Unknown Causes</td>
                        <td style="text-align: center;">{{ (int)($rc['unknown'] ?? 0) + (int)($rc['other'] ?? 0) }}</td>
                        <td>{{ $rc['other_details'] ?? 'Routine operational variances' }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Pharmacy Section D: Financial Adjustments & Credit Reconciliation -->
            @php
                $fin = $report->form_data['section_d_financials'] ?? [];
                $netExp = ((float)($fin['debt_taken_amount'] ?? 0)) - ((float)($fin['debt_paid_amount'] ?? 0));
            @endphp
            <div class="section-banner">
                <span>Section D — Financial Adjustments, Refunds &amp; Credit Reconciliation</span>
                <span>Dib-u-bixinta &amp; Deynta</span>
            </div>
            <table class="booktabs-table">
                <thead>
                    <tr>
                        <th>Financial Category</th>
                        <th style="text-align: center; width: 90px;">Cases</th>
                        <th style="text-align: right; width: 110px;">Amount ($)</th>
                        <th>Reconciliation Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Refunds: System / Billing Error Adjustments</td>
                        <td style="text-align: center;">{{ $fin['refund_billing_error_cases'] ?? 0 }}</td>
                        <td style="text-align: right; font-family: monospace;">${{ number_format($fin['refund_billing_error_amount'] ?? 0, 2) }}</td>
                        <td>Cashier receipt reversal verified</td>
                    </tr>
                    <tr>
                        <td>Refunds: Returned Medications Credited</td>
                        <td style="text-align: center;">{{ $fin['refund_returned_meds_cases'] ?? 0 }}</td>
                        <td style="text-align: right; font-family: monospace;">${{ number_format($fin['refund_returned_meds_amount'] ?? 0, 2) }}</td>
                        <td>Inspected unopened blister packs restocked</td>
                    </tr>
                    <tr>
                        <td>Emergency Credit / Debt Issued (Dayn La Bixiyay)</td>
                        <td style="text-align: center; font-weight: bold; color: #C81D25;">{{ $fin['debt_taken_cases'] ?? 0 }}</td>
                        <td style="text-align: right; font-family: monospace; font-weight: bold; color: #C81D25;">${{ number_format($fin['debt_taken_amount'] ?? 0, 2) }}</td>
                        <td>Authorized emergency dispensing liability</td>
                    </tr>
                    <tr>
                        <td>Historical Debt Recovered (Dayn La Bixiyay / Dib Loo Helay)</td>
                        <td style="text-align: center; font-weight: bold; color: #10B981;">{{ $fin['debt_paid_cases'] ?? 0 }}</td>
                        <td style="text-align: right; font-family: monospace; font-weight: bold; color: #10B981;">${{ number_format($fin['debt_paid_amount'] ?? 0, 2) }}</td>
                        <td>Cash collections reconciled with finance</td>
                    </tr>
                    <tr class="total-row">
                        <td>NET CREDIT / DEBT EXPOSURE FOR SHIFT</td>
                        <td colspan="2" style="text-align: right; font-size: 9.5pt; font-family: monospace; color: {{ $netExp > 0 ? '#C81D25' : '#10B981' }};">
                            ${{ number_format($netExp, 2) }}
                        </td>
                        <td style="font-size: 7.5pt; color: #64748B;">Net change in hospital accounts receivable</td>
                    </tr>
                </tbody>
            </table>

        @elseif($report->department === 'emergency')
            <!-- Emergency Section A: Triage Volume & Dispositions -->
            @php
                $secA = $report->form_data['section_a'] ?? [];
                $acuteCases = (int)($secA['emergency_cases_acute'] ?? 0);
                $opdInEr = (int)($secA['opd_cases_seen_in_er'] ?? 0);
                $followUp = (int)($secA['followup_cases'] ?? 0);
                $totalEr = (int)($secA['total_er_cases'] ?? ($acuteCases + $opdInEr + $followUp));
                $admittedIpd = (int)($secA['disposition_admitted_ipd'] ?? 0);
                $obsWard = (int)($secA['disposition_obs_ward'] ?? 0);
                $dischargedStable = (int)($secA['disposition_discharged_stable'] ?? 0);
                $referredOut = (int)($secA['disposition_referred_out'] ?? 0);
                $lama = (int)($secA['disposition_lama'] ?? 0);
                $erYield = $totalEr > 0 ? round(($admittedIpd / $totalEr) * 100, 1) : 0;
            @endphp
            <div class="section-banner">
                <span>Section A — Emergency Department Triage &amp; Clinical Throughput</span>
                <span>Gurmadka Degdegga ah (Total Cases: {{ $totalEr }})</span>
            </div>
            <table class="booktabs-table">
                <thead>
                    <tr>
                        <th>Emergency Triage Presentation Stream</th>
                        <th style="text-align: center; width: 90px;">Cases</th>
                        <th>Clinical Severity &amp; Intake Pathway</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1. Acute Emergencies (Xaaladaha Degdegga ah ee Khatarta ah)</td>
                        <td style="text-align: center; font-weight: bold; color: #C81D25;">{{ number_format($acuteCases) }}</td>
                        <td>Trauma, acute cardiorespiratory crises, and shock states</td>
                    </tr>
                    <tr>
                        <td>2. Out-of-Hours OPD Cases Seen in E.R (Bukaan Guud ee Gurmadka)</td>
                        <td style="text-align: center; font-weight: bold;">{{ number_format($opdInEr) }}</td>
                        <td>Urgent ambulatory presentations outside clinic operating hours</td>
                    </tr>
                    <tr>
                        <td>3. Follow-Up &amp; Wound Care Cases (Bukaankii Hore ee Soo Laabtay)</td>
                        <td style="text-align: center; font-weight: bold;">{{ number_format($followUp) }}</td>
                        <td>Post-injury wound dressings, suture removals, and injections</td>
                    </tr>
                    <tr class="total-row">
                        <td>TOTAL EMERGENCY DEPARTMENT THROUGHPUT</td>
                        <td style="text-align: center; font-size: 9.5pt; color: #124E78;">{{ number_format($totalEr) }}</td>
                        <td>Total patients triaged and stabilized in Emergency Unit</td>
                    </tr>
                </tbody>
            </table>

            <!-- Emergency Dispositions -->
            <div class="section-banner">
                <span>Emergency Disposition &amp; Inpatient Admission Yield</span>
                <span>Go'aannada Bukaanka Gurmadka</span>
            </div>
            <table class="booktabs-table">
                <thead>
                    <tr>
                        <th>Clinical Disposition Destination</th>
                        <th style="text-align: center; width: 90px;">Patients</th>
                        <th>Clinical Yield &amp; Continuity</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Direct Inpatient Ward Admissions (IPD Transfer)</td>
                        <td style="text-align: center; font-weight: bold; color: #124E78;">{{ number_format($admittedIpd) }}</td>
                        <td>Admission Yield: <strong>{{ $erYield }}%</strong> (Direct hospital bed conversions)</td>
                    </tr>
                    <tr>
                        <td>Observation Ward Monitoring (Obs Ward &lt; 24h)</td>
                        <td style="text-align: center;">{{ number_format($obsWard) }}</td>
                        <td>Short-stay stabilization and diagnostic observation</td>
                    </tr>
                    <tr>
                        <td>Discharged Home in Stable Condition</td>
                        <td style="text-align: center; font-weight: bold; color: #10B981;">{{ number_format($dischargedStable) }}</td>
                        <td>Treated and released with emergency discharge medications</td>
                    </tr>
                    <tr>
                        <td>Referred Out to Tertiary Centers</td>
                        <td style="text-align: center;">{{ number_format($referredOut) }}</td>
                        <td>Inter-hospital emergency transfers with ambulance transport</td>
                    </tr>
                    <tr>
                        <td>Left Against Medical Advice (LAMA)</td>
                        <td style="text-align: center; font-weight: bold; color: #C81D25;">{{ number_format($lama) }}</td>
                        <td>Documented refusal of care; signed legal waiver retained</td>
                    </tr>
                </tbody>
            </table>

            <!-- Emergency Section B & E: Demographics & Mortality -->
            @php
                $secB = $report->form_data['section_b_demographics'] ?? [];
                $secE = $report->form_data['section_e_mortality'] ?? [];
                $diedInEr = (int)($secE['died_in_er_count'] ?? 0);
                $doa = (int)($secE['doa_count'] ?? 0);
                $erMortalityRate = $totalEr > 0 ? round(($diedInEr / $totalEr) * 100, 2) : 0;
            @endphp
            <div class="section-banner">
                <span>Section B &amp; E — Demographics, Triage Classifications &amp; Mortality Audit</span>
                <span>Bukaanka &amp; Dhimashada</span>
            </div>
            <table class="booktabs-table">
                <thead>
                    <tr>
                        <th>Metric Classification</th>
                        <th style="text-align: center; width: 110px;">Recorded Value</th>
                        <th>Clinical Benchmark &amp; Registry Standard</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Patient Demographics (Gender)</td>
                        <td style="text-align: center; font-weight: bold;">{{ $secB['male_count'] ?? 0 }} M / {{ $secB['female_count'] ?? 0 }} F</td>
                        <td>Gender balance across acute presentations</td>
                    </tr>
                    <tr>
                        <td>Pediatric Patients (&lt; 5 Years Old)</td>
                        <td style="text-align: center; font-weight: bold;">{{ $secB['under_5'] ?? 0 }}</td>
                        <td>Pediatric emergency resuscitation protocol</td>
                    </tr>
                    <tr>
                        <td>Adult Patients (18 - 59 Years)</td>
                        <td style="text-align: center;">{{ $secB['age_18_59'] ?? 0 }}</td>
                        <td>Productive age bracket medical &amp; trauma load</td>
                    </tr>
                    <tr>
                        <td>Geriatric Patients (60+ Years)</td>
                        <td style="text-align: center;">{{ $secB['age_60_plus'] ?? 0 }}</td>
                        <td>Chronic disease decompensations &amp; stroke presentations</td>
                    </tr>
                    <tr>
                        <td>Died in E.R (True In-ER Resuscitation Mortality)</td>
                        <td style="text-align: center; font-weight: bold; color: {{ $diedInEr > 0 ? '#C81D25' : '#0F172A' }};">
                            {{ $diedInEr }}
                        </td>
                        <td>True In-ER Mortality: <strong>{{ $erMortalityRate }}%</strong> (Clinical safety benchmark &lt; 2.5%)</td>
                    </tr>
                    <tr>
                        <td>Brought Dead / Dead on Arrival (DOA)</td>
                        <td style="text-align: center; font-weight: bold;">{{ $doa }}</td>
                        <td>Deceased prior to hospital arrival; forensic documentation completed</td>
                    </tr>
                </tbody>
            </table>

        @elseif($report->department === 'inpatient')
            <!-- Inpatient Section A: Daily Bed Census Reconciliation -->
            @php
                $secA = $report->form_data['section_a_census'] ?? [];
                $prevA = (int)($secA['previous_admissions_a'] ?? 0);
                $newFromEr = (int)($secA['newly_admitted_from_er'] ?? 0);
                $newFromOpd = (int)($secA['newly_admitted_from_opd'] ?? 0);
                $newB = (int)($secA['newly_admitted_b'] ?? ($newFromEr + $newFromOpd));
                $recC1 = (int)($secA['discharged_recovered_c1'] ?? 0);
                $damaC2 = (int)($secA['discharged_dama_c2'] ?? 0);
                $totC = (int)($secA['total_discharged_c'] ?? ($recC1 + $damaC2));
                $refD = (int)($secA['referred_out_d'] ?? 0);
                $deathE = (int)($secA['deaths_today_e'] ?? 0);
                $currF = (int)($secA['current_admissions_f'] ?? ($prevA + $newB - $totC - $refD - $deathE));
                $formulaPass = (($prevA + $newB - $totC - $refD - $deathE) === $currF);
                $activeBeds = 60;
                $bor = $activeBeds > 0 ? round(($currF / $activeBeds) * 100, 1) : 0;
            @endphp
            <div class="section-banner">
                <span>Section A — Self-Balancing Daily Bed Census Reconciliation</span>
                <span>Formula: [A] + [B] - [C] - [D] - [E] = [F] (BOR: {{ $bor }}%)</span>
            </div>
            <table class="booktabs-table">
                <thead>
                    <tr>
                        <th style="width: 45px;">Step</th>
                        <th>Census Reconciliation Metric Description</th>
                        <th style="text-align: center; width: 80px;">Count</th>
                        <th>Audit Source &amp; Cross-Check</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="font-weight: bold; font-family: monospace;">[A]</td>
                        <td>Previous Admissions (Ending Bed Census Yesterday)</td>
                        <td style="text-align: center; font-weight: bold;">{{ $prevA }}</td>
                        <td>Verified from yesterday's official signed register</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; font-family: monospace; color: #10B981;">[+B]</td>
                        <td>Newly Admitted Today (ER: {{ $newFromEr }} + OPD: {{ $newFromOpd }})</td>
                        <td style="text-align: center; font-weight: bold; color: #10B981;">{{ $newB }}</td>
                        <td>Cross-checked against Emergency &amp; OPD admission slips</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; font-family: monospace; color: #124E78;">[-C]</td>
                        <td>Total Discharged (Recovered: {{ $recC1 }} + DAMA: {{ $damaC2 }})</td>
                        <td style="text-align: center; font-weight: bold; color: #124E78;">{{ $totC }}</td>
                        <td>Physician discharge summaries signed &amp; verified</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; font-family: monospace; color: #C81D25;">[-D]</td>
                        <td>Referred Out to Tertiary Specialist Centers</td>
                        <td style="text-align: center;">{{ $refD }}</td>
                        <td>Inter-hospital transfer documentation filed</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; font-family: monospace; color: #C81D25;">[-E]</td>
                        <td>Inpatient Mortality (Ward Deaths)</td>
                        <td style="text-align: center; font-weight: bold; color: {{ $deathE > 0 ? '#C81D25' : '#0F172A' }};">{{ $deathE }}</td>
                        <td>Clinical death certificates completed &amp; logged</td>
                    </tr>
                    <tr class="total-row">
                        <td style="font-weight: bold; font-family: monospace; font-size: 9pt;">[=F]</td>
                        <td>CURRENT ADMISSIONS (TODAY'S ENDING BED CENSUS)</td>
                        <td style="text-align: center; font-size: 9.5pt; color: #124E78;">{{ $currF }}</td>
                        <td>
                            @if($formulaPass)
                                <strong style="color: #10B981;">&check; Strict Formula Balance: PASSED</strong>
                            @else
                                <strong style="color: #C81D25;">&cross; Balance Discrepancy Flagged</strong>
                            @endif
                            &bull; BOR: <strong>{{ $bor }}%</strong> across {{ $activeBeds }} active beds
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Inpatient Section B: Ward Intake & Delivery Operations -->
            @php
                $secBFlow = $report->form_data['section_b_flow'] ?? [];
                $secC = $report->form_data['section_c_deliveries'] ?? [];
                $normalDeliv = (int)($secC['normal_deliveries'] ?? 0);
                $cSections = (int)($secC['c_section_deliveries'] ?? 0);
                $totDeliv = $normalDeliv + $cSections;
                $csRate = $totDeliv > 0 ? round(($cSections / $totDeliv) * 100, 1) : 0;
            @endphp
            <div class="section-banner">
                <span>Section B &amp; C — Maternity, Surgical Deliveries &amp; Ward Distribution</span>
                <span>Dhalmada &amp; Qalliinka</span>
            </div>
            <table class="booktabs-table">
                <thead>
                    <tr>
                        <th>Clinical Department / Operational Parameter</th>
                        <th style="text-align: center; width: 100px;">Recorded Value</th>
                        <th>Quality Target &amp; WHO Guideline Threshold</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Normal Spontaneous Vaginal Deliveries (SVD)</td>
                        <td style="text-align: center; font-weight: bold;">{{ $normalDeliv }}</td>
                        <td>Uncomplicated labor ward deliveries</td>
                    </tr>
                    <tr>
                        <td>Cesarean Section Deliveries (C-Section)</td>
                        <td style="text-align: center; font-weight: bold; color: #124E78;">{{ $cSections }}</td>
                        <td>Surgical obstetric interventions</td>
                    </tr>
                    <tr class="total-row">
                        <td>TOTAL MATERNITY DELIVERIES &amp; C-SECTION RATE</td>
                        <td style="text-align: center; color: #124E78;">{{ $totDeliv }} Deliveries</td>
                        <td>C-Section Rate: <strong>{{ $csRate }}%</strong> (Clinical threshold: 15% - 25%)</td>
                    </tr>
                    <tr>
                        <td>Direct Ward Admissions (Direct OPD Walk-Ins)</td>
                        <td style="text-align: center;">{{ $secBFlow['direct_admissions'] ?? 0 }}</td>
                        <td>Scheduled elective surgical &amp; medical admissions</td>
                    </tr>
                    <tr>
                        <td>Emergency Department Ward Transfers</td>
                        <td style="text-align: center;">{{ $secBFlow['er_admissions'] ?? 0 }}</td>
                        <td>Acute emergency bed placements</td>
                    </tr>
                </tbody>
            </table>

            <!-- Inpatient Section D: Mortality Registry -->
            @php
                $mortalityList = $report->form_data['section_d_mortality']['mortality_cases'] ?? [];
            @endphp
            @if(!empty($mortalityList))
                <div class="section-banner">
                    <span>Section D — Inpatient Mortality Registry</span>
                    <span>Diiwaanka Dhimashada Bukaan Jiifka</span>
                </div>
                <table class="booktabs-table">
                    <thead>
                        <tr>
                            <th>Patient MRN</th>
                            <th>Ward / Bed</th>
                            <th style="text-align: center;">Time of Death</th>
                            <th>Primary Cause of Death</th>
                            <th>Attending Physician</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mortalityList as $m)
                            <tr>
                                <td style="font-family: monospace; font-weight: bold;">{{ $m['patient_mrn'] ?? '—' }}</td>
                                <td>{{ $m['ward'] ?? 'General Ward' }}</td>
                                <td style="text-align: center; font-family: monospace;">{{ $m['time_of_death'] ?? '—' }}</td>
                                <td style="color: #C81D25; font-weight: bold;">{{ $m['cause_of_death'] ?? '—' }}</td>
                                <td>{{ $m['attending_doctor'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

        @elseif($report->department === 'laboratory')
            <!-- Laboratory Section A: Diagnostic Volume by Source -->
            @php
                $secA = $report->form_data['section_a_volume'] ?? [];
                $totLabPts = (int)($secA['total_patients'] ?? 0);
                $totLabTests = (int)($secA['total_tests'] ?? 0);
                $intensity = $totLabPts > 0 ? round($totLabTests / $totLabPts, 2) : 0;
            @endphp
            <div class="section-banner">
                <span>Section A — Laboratory Diagnostic Volume by Source</span>
                <span>Wadarta Baaritaannada (Intensity: {{ $intensity }}x tests/pt)</span>
            </div>
            <table class="booktabs-table">
                <thead>
                    <tr>
                        <th>Patient Stream / Referring Source</th>
                        <th style="text-align: center; width: 90px;">Patients</th>
                        <th style="text-align: center; width: 90px;">Tests Run</th>
                        <th>Clinical Ratio / Performance Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1. Internal OPD Outpatient Clinics</td>
                        <td style="text-align: center; font-weight: bold;">{{ $secA['opd_patients'] ?? 0 }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $secA['opd_tests'] ?? 0 }}</td>
                        <td>Routine outpatient diagnostic profiles</td>
                    </tr>
                    <tr>
                        <td>2. Internal Emergency (E.R) STAT Orders</td>
                        <td style="text-align: center; font-weight: bold; color: #C81D25;">{{ $secA['er_patients'] ?? 0 }}</td>
                        <td style="text-align: center; font-weight: bold; color: #C81D25;">{{ $secA['er_tests'] ?? 0 }}</td>
                        <td>STAT acute care resuscitation panels</td>
                    </tr>
                    <tr>
                        <td>3. Internal Inpatient Wards (IPD Monitoring)</td>
                        <td style="text-align: center; font-weight: bold;">{{ $secA['ipd_patients'] ?? 0 }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $secA['ipd_tests'] ?? 0 }}</td>
                        <td>Daily inpatient therapeutic monitoring &amp; rounds</td>
                    </tr>
                    <tr>
                        <td>4. Outside Walk-Ins &amp; B2B Partner Clinics</td>
                        <td style="text-align: center; font-weight: bold;">{{ $secA['outside_patients'] ?? 0 }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $secA['outside_tests'] ?? 0 }}</td>
                        <td>Walk-ins: {{ $secA['outside_walkin_pts'] ?? 0 }} &bull; B2B Partners: {{ $secA['outside_partner_pts'] ?? 0 }}</td>
                    </tr>
                    <tr class="total-row">
                        <td>TOTAL LABORATORY VOLUME</td>
                        <td style="text-align: center; font-size: 9.5pt; color: #124E78;">{{ number_format($totLabPts) }}</td>
                        <td style="text-align: center; font-size: 9.5pt; color: #124E78;">{{ number_format($totLabTests) }}</td>
                        <td>Diagnostic Intensity: <strong>{{ $intensity }}x</strong> tests per patient</td>
                    </tr>
                </tbody>
            </table>

            <!-- Laboratory Section B: B2B External Healthcare Referral Partners -->
            @php
                $secBPartners = $report->form_data['section_b_partners'] ?? [];
            @endphp
            @if(!empty($secBPartners))
                <div class="section-banner">
                    <span>Section B — External Healthcare B2B Referral Partners Registry</span>
                    <span>Xarumaha Caafimaadka ee Bukaanka Soo Diray</span>
                </div>
                <table class="booktabs-table">
                    <thead>
                        <tr>
                            <th>Partner Hospital / Clinic Facility</th>
                            <th style="text-align: center; width: 90px;">Patients Referred</th>
                            <th style="text-align: center; width: 90px;">Tests Performed</th>
                            <th>Contact / Operational Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($secBPartners as $bp)
                            <tr>
                                <td><strong>{{ $bp['partner'] ?? ($bp['partner_name'] ?? 'Partner Clinic') }}</strong></td>
                                <td style="text-align: center; font-weight: bold;">{{ $bp['patients'] ?? 0 }}</td>
                                <td style="text-align: center; font-weight: bold; color: #124E78;">{{ $bp['tests'] ?? 0 }}</td>
                                <td>{{ $bp['phone'] ?? 'External clinical outreach' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <!-- Laboratory Section C: 13 Clinical Benches, Quality & Ordered Tests -->
            @php
                $secC = $report->form_data['section_c_benches_and_quality'] ?? [];
                $benches = [
                    ['name' => '1. Hematology & CBC (Dhiigga)', 'count' => (int)($secC['hematology'] ?? ($secC['haematology'] ?? 0))],
                    ['name' => '2. Clinical Biochemistry (Kiimikada)', 'count' => (int)($secC['biochemistry'] ?? 0)],
                    ['name' => '3. Serology & Immunology (Difaaca Jirka)', 'count' => (int)($secC['serology_immunology'] ?? ($secC['serology'] ?? 0))],
                    ['name' => '4. Microbiology & Parasitology (Jeermiska)', 'count' => (int)($secC['microbiology_parasitology'] ?? 0)],
                    ['name' => '5. Hormones & Special Tests (Qanjirada)', 'count' => (int)($secC['hormones_special'] ?? ($secC['hormones'] ?? 0))],
                    ['name' => '6. Blood Bank & Transfusion (Keydka Dhiigga)', 'count' => (int)($secC['blood_bank'] ?? 0)],
                    ['name' => '7. Urinalysis & Microscopy (Kaadida)', 'count' => (int)($secC['urine'] ?? 0)],
                    ['name' => '8. Stool & Occult Blood (Saxarada)', 'count' => (int)($secC['stool'] ?? 0)],
                    ['name' => '9. Quantitative Chemistry', 'count' => (int)($secC['quantitative_test'] ?? 0)],
                    ['name' => '10. Coagulation Profile (Xinjirowga)', 'count' => (int)($secC['coagulation_profile'] ?? 0)],
                    ['name' => '11. Semen Analysis & Fertility', 'count' => (int)($secC['semen_analysis'] ?? 0)],
                    ['name' => '12. TB & Acid-Fast Bacilli (Qaaxada)', 'count' => (int)($secC['tb_analysis'] ?? 0)],
                    ['name' => '13. Body Fluids & Cytology (Dheecaannada)', 'count' => (int)($secC['body_fluids'] ?? 0)],
                ];
                $activeOrderedTests = array_filter($secC['tests'] ?? [], fn($t) => ((int)($t['count'] ?? 0)) > 0);
            @endphp
            <div class="section-banner">
                <span>Section C — Clinical Bench Breakdown &amp; Quality Indicators</span>
                <span>Qeybaha Shaybaarka (13 Benches)</span>
            </div>
            <table class="booktabs-table">
                <thead>
                    <tr>
                        <th>Diagnostic Bench Specialty</th>
                        <th style="text-align: center; width: 80px;">Tests Run</th>
                        <th>Diagnostic Bench Specialty</th>
                        <th style="text-align: center; width: 80px;">Tests Run</th>
                    </tr>
                </thead>
                <tbody>
                    @for($i = 0; $i < count($benches); $i += 2)
                        <tr>
                            <td>{{ $benches[$i]['name'] }}</td>
                            <td style="text-align: center; font-weight: bold; color: {{ $benches[$i]['count'] > 0 ? '#124E78' : '#64748B' }};">
                                {{ $benches[$i]['count'] }}
                            </td>
                            @if(isset($benches[$i+1]))
                                <td>{{ $benches[$i+1]['name'] }}</td>
                                <td style="text-align: center; font-weight: bold; color: {{ $benches[$i+1]['count'] > 0 ? '#124E78' : '#64748B' }};">
                                    {{ $benches[$i+1]['count'] }}
                                </td>
                            @else
                                <td>—</td>
                                <td style="text-align: center;">—</td>
                            @endif
                        </tr>
                    @endfor
                    <tr class="total-row">
                        <td>Sample Rejections (Muunadaha La Diiday):</td>
                        <td style="text-align: center; color: #C81D25;">{{ $secC['sample_rejections'] ?? 0 }}</td>
                        <td>Delayed Turnaround (TAT) &bull; Critical Values Reported:</td>
                        <td style="text-align: center; color: #124E78;">{{ $secC['delayed_tests_tat'] ?? 0 }} del &bull; {{ $secC['critical_values_reported'] ?? 0 }} crit</td>
                    </tr>
                </tbody>
            </table>

            @if(!empty($activeOrderedTests))
                <div class="section-banner">
                    <span>Section C.2 — Detailed Active Specific Tests Examined</span>
                    <span>Faahfaahinta Baaritaannada La Qabtay ({{ count($activeOrderedTests) }} Tests)</span>
                </div>
                <table class="booktabs-table">
                    <thead>
                        <tr>
                            <th>Specific Test Examination Name</th>
                            <th>Clinical Bench / Laboratory Specialty</th>
                            <th style="text-align: center; width: 100px;">Tests Performed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activeOrderedTests as $t)
                            <tr>
                                <td><strong>{{ $t['test_name'] ?? ($t['name'] ?? 'Test') }}</strong></td>
                                <td>{{ $t['lab_type_name'] ?? ($t['lab_type'] ?? 'General') }}</td>
                                <td style="text-align: center; font-weight: bold; color: #124E78;">{{ $t['count'] ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <!-- Laboratory Section D: NIL / Out of Stock Diagnostic Reagents -->
            @php
                $nilItems = $report->form_data['section_d_nil_items']['items'] ?? [];
            @endphp
            @if(!empty($nilItems))
                <div class="section-banner">
                    <span>Section D — Out of Stock / NIL Diagnostic Reagents</span>
                    <span>Diiwaanka Qalabka &amp; Reagents-ka Go'ay</span>
                </div>
                <table class="booktabs-table">
                    <thead>
                        <tr>
                            <th>Reagent / Consumable Name</th>
                            <th>Bench Section</th>
                            <th>Stockout Reason</th>
                            <th style="text-align: center;">Expected Restock Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($nilItems as $nil)
                            <tr>
                                <td style="font-weight: bold; color: #C81D25;">{{ $nil['item_name'] ?? '—' }}</td>
                                <td>{{ $nil['bench'] ?? '—' }}</td>
                                <td>{{ $nil['reason'] ?? 'Supply chain delay' }}</td>
                                <td style="text-align: center; font-family: monospace;">{{ $nil['expected_restock'] ?? 'Pending Order' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <!-- Laboratory Section E: Incomplete, Equipment & Shift Handover -->
            @php
                $secE = $report->form_data['section_e_lab_operations'] ?? [];
            @endphp
            @if(!empty($secE['incomplete_tests']))
                <div class="section-banner">
                    <span>Section E.1 — Incomplete &amp; Delayed Tests Logbook</span>
                    <span>Baaritaannada Qabyada ah</span>
                </div>
                <table class="booktabs-table">
                    <thead>
                        <tr>
                            <th>Sample ID</th>
                            <th>Patient Details</th>
                            <th>Test Parameter</th>
                            <th>Bench</th>
                            <th>Delay Reason</th>
                            <th style="text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($secE['incomplete_tests'] as $inc)
                            <tr>
                                <td style="font-family: monospace; font-weight: bold;">{{ $inc['sample_id'] ?? '—' }}</td>
                                <td>{{ $inc['patient_name'] ?? '—' }} (MRN: {{ $inc['patient_id'] ?? '—' }})</td>
                                <td><strong>{{ $inc['test_name'] ?? '—' }}</strong></td>
                                <td>{{ $inc['bench'] ?? '—' }}</td>
                                <td>{{ $inc['reason'] ?? '—' }}</td>
                                <td style="text-align: center; font-weight: bold;">{{ $inc['status'] ?? 'Pending' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if(!empty($secE['equipment_troubleshooting']))
                <div class="section-banner">
                    <span>Section E.2 — Equipment Incidents &amp; Troubleshooting</span>
                    <span>Ciladaha Qalabka</span>
                </div>
                <table class="booktabs-table">
                    <thead>
                        <tr>
                            <th>Analyzer Instrument</th>
                            <th>Fault Description</th>
                            <th>Corrective Action Taken</th>
                            <th style="text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($secE['equipment_troubleshooting'] as $eq)
                            <tr>
                                <td><strong>{{ $eq['equipment_name'] ?? '—' }}</strong></td>
                                <td>{{ $eq['issue_description'] ?? '—' }}</td>
                                <td>{{ $eq['troubleshooting_action'] ?? '—' }}</td>
                                <td style="text-align: center; font-weight: bold;">{{ $eq['status'] ?? 'Resolved' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @php
                $handover = $secE['shift_handover'] ?? [];
            @endphp
            @if(!empty($handover['shift_notes']) || !empty($handover['incoming_staff']) || !empty($handover['manager_notes']))
                <div class="section-banner">
                    <span>Section E.3 — Laboratory Shift Handover &amp; Operational Notes</span>
                    <span>Wareejinta Qaybta Shaybaarka (Urgency: {{ strtoupper($handover['urgency_level'] ?? 'ROUTINE') }})</span>
                </div>
                <table class="booktabs-table">
                    <thead>
                        <tr>
                            <th style="width: 28%;">Handover Parameter</th>
                            <th>Operational Log Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($handover['shift_notes']))
                            <tr>
                                <td><strong>Outgoing Shift Handover Notes:</strong></td>
                                <td>{{ $handover['shift_notes'] }}</td>
                            </tr>
                        @endif
                        @if(!empty($handover['incoming_staff']))
                            <tr>
                                <td><strong>Incoming Relief Staff:</strong></td>
                                <td>{{ $handover['incoming_staff'] }}</td>
                            </tr>
                        @endif
                        @if(!empty($handover['manager_notes']))
                            <tr>
                                <td><strong>Laboratory Manager / Supervisor Notes:</strong></td>
                                <td>{{ $handover['manager_notes'] }}</td>
                            </tr>
                        @endif
                        @if(!empty($handover['urgency_level']))
                            <tr>
                                <td><strong>Operational Alert Status:</strong></td>
                                <td>
                                    <span style="font-weight: bold; color: {{ ($handover['urgency_level'] ?? '') === 'critical' ? '#C81D25' : '#124E78' }};">
                                        {{ strtoupper($handover['urgency_level']) }}
                                    </span>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            @endif

            <!-- Laboratory Section F: Quality Control Calibration -->
            @php
                $secF = $report->form_data['section_f_quality_control']['calibrations'] ?? [];
            @endphp
            @if(!empty($secF))
                <div class="section-banner">
                    <span>Section F — Quality Control (QC) &amp; Calibration Register</span>
                    <span>Diiwaanka Cabirka Tayada</span>
                </div>
                <table class="booktabs-table">
                    <thead>
                        <tr>
                            <th>Clinical Test</th>
                            <th>Analyzer</th>
                            <th style="text-align: center;">Standard Abs</th>
                            <th style="text-align: center;">Measured Abs</th>
                            <th style="text-align: center;">Lot #</th>
                            <th style="text-align: center;">QC Status</th>
                            <th>Technologist</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($secF as $cal)
                            <tr>
                                <td><strong>{{ $cal['test_name'] ?? '—' }}</strong></td>
                                <td>{{ $cal['analyzer'] ?? '—' }}</td>
                                <td style="text-align: center; font-family: monospace;">{{ $cal['standard_absorption'] ?? '—' }}</td>
                                <td style="text-align: center; font-family: monospace; font-weight: bold; color: #124E78;">{{ $cal['measured_absorption'] ?? '—' }}</td>
                                <td style="text-align: center; font-family: monospace; font-size: 7.5pt;">{{ $cal['calibrator_lot'] ?? '—' }}</td>
                                <td style="text-align: center; font-weight: bold; color: {{ strtolower($cal['qc_status'] ?? '') === 'passed' ? '#10B981' : '#C81D25' }};">
                                    {{ strtoupper($cal['qc_status'] ?? 'PASSED') }}
                                </td>
                                <td>{{ $cal['calibrated_by'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <!-- Laboratory Section G: Supplies Received Log -->
            @php
                $secG = $report->form_data['section_g_inventory_supplies']['received_items'] ?? [];
            @endphp
            @if(!empty($secG))
                <div class="section-banner">
                    <span>Section G — Laboratory Inventory &amp; Supplies Received Log</span>
                    <span>Alaabta La Helay</span>
                </div>
                <table class="booktabs-table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Brand / Manufacturer</th>
                            <th style="text-align: center;">Quantity</th>
                            <th style="text-align: center;">Lot #</th>
                            <th style="text-align: center;">Expiry Date</th>
                            <th>Supplier Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($secG as $inv)
                            <tr>
                                <td><strong>{{ $inv['item_name'] ?? '—' }}</strong></td>
                                <td>{{ $inv['brand'] ?? '—' }}</td>
                                <td style="text-align: center; font-weight: bold;">{{ $inv['quantity'] ?? 0 }} {{ $inv['unit'] ?? 'Unit' }}</td>
                                <td style="text-align: center; font-family: monospace; font-size: 7.5pt;">{{ $inv['lot_number'] ?? '—' }}</td>
                                <td style="text-align: center; font-family: monospace;">{{ $inv['expiry_date'] ?? '—' }}</td>
                                <td>{{ $inv['supplier_name'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

        @else
            {{-- Default / Fallback Department --}}
            <div class="section-banner">
                <span>Summary Clinical Operations Data</span>
                <span>{{ $report->form_code }}</span>
            </div>
            <table class="booktabs-table">
                <thead>
                    <tr>
                        <th>Metric Parameter</th>
                        <th style="text-align: center;">Recorded Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report->summary_metrics ?? [] as $mKey => $mVal)
                        <tr>
                            <td>{{ ucwords(str_replace('_', ' ', $mKey)) }}</td>
                            <td style="text-align: center; font-weight: bold;">{{ is_array($mVal) ? json_encode($mVal) : $mVal }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Signatures & Governance Stamps -->
        <div class="signature-block">
            <div>
                <p><strong>Department Supervisor / Shift Lead:</strong></p>
                <p style="margin-top: 14px; font-weight: bold; font-size: 9pt;">{{ $report->lead_staff_name }}</p>
                <p style="color: #64748B; margin-top: 4px;">Signature: __________________________</p>
            </div>
            <div style="text-align: right;">
                <p><strong>Hospital Analytics Directorate:</strong></p>
                <div style="margin-top: 6px;">
                    <span class="stamp-box">AUDITED &amp; APPROVED</span>
                </div>
                <p style="color: #64748B; margin-top: 6px;">Date: {{ $report->audited_at ? $report->audited_at->toDateString() : date('Y-m-d') }}</p>
            </div>
        </div>

    </div>

</body>
</html>
