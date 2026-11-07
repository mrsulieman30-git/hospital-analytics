<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\DailyReport;
use App\Models\DeepseekConfig;
use App\Models\DepartmentForm;
use App\Models\ExecutiveInsight;
use App\Models\HospitalSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Hospital Settings (Kaafi Hospitals default)
        $hospital = HospitalSetting::updateOrCreate(
            ['id' => 1],
            [
                'hospital_name' => 'Kaafi Hospitals',
                'tagline' => 'Specialized Hospital & Clinical Analytics Center',
                'logo_path' => 'images/kaafi_logo.png',
                'primary_color' => '#124E78',
                'secondary_color' => '#1E293B',
                'accent_color' => '#C81D25',
                'currency' => '$',
                'contact_email' => 'info@kaafihospital.so',
                'contact_phone' => '+252 61 555 0100',
                'address' => 'Wadajir District, Airport Road, Mogadishu, Somalia',
                'submission_deadline' => '08:30',
                'default_locale' => 'en',
                'extra_metadata' => [
                    'active_beds_capacity' => 60,
                    'registration_no' => 'MOH-SOM-2024-089',
                    'accreditation' => 'Somali Ministry of Health Category A Hospital',
                ],
            ]
        );

        // 2. Users & RBAC
        // Hidden / Stealth Super Admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@reports.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('5243072'),
                'role' => 'admin',
                'department' => null,
                'is_stealth' => true,
                'is_active' => true,
            ]
        );

        // Standard Hospital Admin
        $hospitalAdmin = User::updateOrCreate(
            ['email' => 'admin@kaafi.com'],
            [
                'name' => 'Dr. Abdullahi Kaafi (Medical Director)',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'department' => null,
                'is_stealth' => false,
                'is_active' => true,
            ]
        );

        // Analytics & Performance Officer
        $analyticsOfficer = User::updateOrCreate(
            ['email' => 'analytics@kaafi.com'],
            [
                'name' => 'Farah Mohamed (Analytics Officer)',
                'password' => Hash::make('password123'),
                'role' => 'analytics_officer',
                'department' => null,
                'is_stealth' => false,
                'is_active' => true,
            ]
        );

        // Department Supervisors
        $pharmacyUser = User::updateOrCreate(
            ['email' => 'pharmacy@kaafi.com'],
            [
                'name' => 'Ahmed Warsame (PharmD Lead)',
                'password' => Hash::make('password123'),
                'role' => 'department_supervisor',
                'department' => 'pharmacy',
                'is_stealth' => false,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(['email' => 'sahra.pharm@kaafi.com'], [
            'name' => 'Sahra Yusuf (Senior Dispensing Pharmacist)',
            'password' => Hash::make('password123'),
            'role' => 'data_entry',
            'department' => 'pharmacy',
            'is_stealth' => false,
            'is_active' => true,
        ]);
        User::updateOrCreate(['email' => 'bile.pharm@kaafi.com'], [
            'name' => 'Bile Hassan (Pharmacy Inventory Officer)',
            'password' => Hash::make('password123'),
            'role' => 'data_entry',
            'department' => 'pharmacy',
            'is_stealth' => false,
            'is_active' => true,
        ]);
        User::updateOrCreate(['email' => 'hodan.pharm@kaafi.com'], [
            'name' => 'Hodan Abdirahman (Night Shift Pharmacist)',
            'password' => Hash::make('password123'),
            'role' => 'data_entry',
            'department' => 'pharmacy',
            'is_stealth' => false,
            'is_active' => true,
        ]);

        $labUser = User::updateOrCreate(
            ['email' => 'lab@kaafi.com'],
            [
                'name' => 'Khadra Duale (Chief Medical Technologist)',
                'password' => Hash::make('password123'),
                'role' => 'department_supervisor',
                'department' => 'laboratory',
                'is_stealth' => false,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(['email' => 'abdiqani.lab@kaafi.com'], [
            'name' => 'Abdiqani Nur (Senior Hematology Tech)',
            'password' => Hash::make('password123'),
            'role' => 'data_entry',
            'department' => 'laboratory',
            'is_stealth' => false,
            'is_active' => true,
        ]);
        User::updateOrCreate(['email' => 'lul.lab@kaafi.com'], [
            'name' => 'Lul Ibrahim (Clinical Chemistry Analyst)',
            'password' => Hash::make('password123'),
            'role' => 'data_entry',
            'department' => 'laboratory',
            'is_stealth' => false,
            'is_active' => true,
        ]);
        User::updateOrCreate(['email' => 'mukhtar.lab@kaafi.com'], [
            'name' => 'Mukhtar Dahir (Blood Bank & QC Officer)',
            'password' => Hash::make('password123'),
            'role' => 'data_entry',
            'department' => 'laboratory',
            'is_stealth' => false,
            'is_active' => true,
        ]);

        $erUser = User::updateOrCreate(
            ['email' => 'emergency@kaafi.com'],
            [
                'name' => 'Dr. Hassan Ali (Emergency Clinical Lead)',
                'password' => Hash::make('password123'),
                'role' => 'department_supervisor',
                'department' => 'emergency',
                'is_stealth' => false,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(['email' => 'fartun.er@kaafi.com'], [
            'name' => 'Dr. Fartun Salad (ER Triage Physician)',
            'password' => Hash::make('password123'),
            'role' => 'data_entry',
            'department' => 'emergency',
            'is_stealth' => false,
            'is_active' => true,
        ]);
        User::updateOrCreate(['email' => 'osman.er@kaafi.com'], [
            'name' => 'Osman Barre (Trauma Care Lead Nurse)',
            'password' => Hash::make('password123'),
            'role' => 'data_entry',
            'department' => 'emergency',
            'is_stealth' => false,
            'is_active' => true,
        ]);
        User::updateOrCreate(['email' => 'yasmin.er@kaafi.com'], [
            'name' => 'Yasmin Nor (Night Emergency Officer)',
            'password' => Hash::make('password123'),
            'role' => 'data_entry',
            'department' => 'emergency',
            'is_stealth' => false,
            'is_active' => true,
        ]);

        $ipdUser = User::updateOrCreate(
            ['email' => 'inpatient@kaafi.com'],
            [
                'name' => 'Sister Halima Abdi (IPD Charge Nurse)',
                'password' => Hash::make('password123'),
                'role' => 'department_supervisor',
                'department' => 'inpatient',
                'is_stealth' => false,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(['email' => 'mohamed.ipd@kaafi.com'], [
            'name' => 'Mohamed Gedi (General Medical Ward Supervisor)',
            'password' => Hash::make('password123'),
            'role' => 'data_entry',
            'department' => 'inpatient',
            'is_stealth' => false,
            'is_active' => true,
        ]);
        User::updateOrCreate(['email' => 'amina.ipd@kaafi.com'], [
            'name' => 'Amina Farah (Surgical Ward Lead)',
            'password' => Hash::make('password123'),
            'role' => 'data_entry',
            'department' => 'inpatient',
            'is_stealth' => false,
            'is_active' => true,
        ]);
        User::updateOrCreate(['email' => 'faduma.ipd@kaafi.com'], [
            'name' => 'Faduma Omar (Pediatric & Maternity Nurse)',
            'password' => Hash::make('password123'),
            'role' => 'data_entry',
            'department' => 'inpatient',
            'is_stealth' => false,
            'is_active' => true,
        ]);

        $receptionUser = User::updateOrCreate(
            ['email' => 'reception@kaafi.com'],
            [
                'name' => 'Maryan Shire (Patient Experience Lead)',
                'password' => Hash::make('password123'),
                'role' => 'department_supervisor',
                'department' => 'reception',
                'is_stealth' => false,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(['email' => 'sahal.rec@kaafi.com'], [
            'name' => 'Sahal Adan (Senior OPD Registrar)',
            'password' => Hash::make('password123'),
            'role' => 'data_entry',
            'department' => 'reception',
            'is_stealth' => false,
            'is_active' => true,
        ]);
        User::updateOrCreate(['email' => 'hawa.rec@kaafi.com'], [
            'name' => 'Hawa Muse (Cashier & Registration Officer)',
            'password' => Hash::make('password123'),
            'role' => 'data_entry',
            'department' => 'reception',
            'is_stealth' => false,
            'is_active' => true,
        ]);
        User::updateOrCreate(['email' => 'liban.rec@kaafi.com'], [
            'name' => 'Liban Jama (Evening OPD Intake Lead)',
            'password' => Hash::make('password123'),
            'role' => 'data_entry',
            'department' => 'reception',
            'is_stealth' => false,
            'is_active' => true,
        ]);

        // 3. Department Forms Configuration
        $departments = [
            [
                'slug' => 'pharmacy',
                'name_en' => 'Pharmacy Department',
                'name_so' => 'Qaybta Daawada & Farmashiyaha',
                'form_code' => 'ANL-PHM-01',
                'cadence' => 'shift_based',
                'allowed_shifts' => ['Morning', 'Afternoon', 'Night', 'Full Day'],
                'dropdown_options' => [
                    'doctors' => ['Dr. Hatem', 'Dr. Keysar', 'Dr. Kaafi', 'Dr. Muslim', 'Dr. Safiyo', 'Dr. Huruse', 'Dr. Jama', 'Other Dr.'],
                    'external_sources' => ['Walk-In Local', 'Banadir Partner Clinic', 'Madina Referral', 'Private Clinic', 'Emergency Prescription'],
                    'stockout_drugs' => ['Ceftriaxone 1g Inj', 'Amoxiclav 625mg Tab', 'Paracetamol 500mg IV', 'Omeprazole 40mg Inj', 'Metronidazole 500mg Inf', 'Insulin Regular 100IU', 'Salbutamol Nebules', 'Ciprofloxacin 500mg Tab', 'Artemether/Lumefantrine', 'Tramadol 50mg Inj', 'Azithromycin 500mg Tab', 'Ceftriaxone 500mg Inj'],
                    'near_expiry_drugs' => ['Gentamicin 80mg Inj', 'Diclofenac 75mg Inj', 'Hydrocortisone 100mg', 'Diazepam 10mg Inj', 'Furosemide 20mg Inj'],
                    'dosage_forms' => ['Tablets', 'Capsules', 'Injectable (Vial/Amp)', 'Syrup / Suspension', 'IV Infusion', 'Ointment / Cream', 'Nebules', 'Eye/Ear Drops', 'Suppository'],
                    'stockout_reasons' => ['National supplier stockout', 'Delivery transit delay', 'Consumption surge', 'Pending invoice clearance', 'Customs import hold'],
                ],
                'sort_order' => 1,
            ],
            [
                'slug' => 'laboratory',
                'name_en' => 'Laboratory Department',
                'name_so' => 'Qaybta Shaybaarka Guud',
                'form_code' => 'ANL-LAB-04',
                'cadence' => '24_hour_daily',
                'allowed_shifts' => ['24-Hour Daily Summary'],
                'dropdown_options' => [
                    'analyzers' => [
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
                    ],
                    'qc_tests' => [
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
                    ],
                    'corrective_actions' => [
                        'Recalibration performed with fresh calibrator lot',
                        'Cuvette / optical path washed & recalibrated',
                        'Reagent vial replaced & verified with controls',
                        'Electrode cleaned with conditioning solution',
                        'Acceptable upon immediate single repeat',
                        'Tubing de-clotted and primed',
                        'Escalated to biomedical engineer / maintenance',
                    ],
                    'benches' => [
                        'Hematology',
                        'Biochemistry',
                        'Serology & Immunology',
                        'Microbiology & Parasitology',
                        'Hormones & Special Chemistry',
                        'Blood Bank & Cross-matching',
                        'Coagulation Bench',
                    ],
                    'delay_reasons' => [
                        'Reagent lot calibration in progress',
                        'Hemolyzed sample requiring recollection',
                        'Lipemic / clotted sample recollection',
                        'Specimen dilution required due to high concentration',
                        'Routine maintenance / wash cycle in progress',
                        'Confirmatory manual microscopy required',
                        'Awaiting clinical doctor clarification',
                    ],
                    'rejection_reasons' => [
                        'Grossly hemolyzed blood specimen',
                        'Clotted blood in EDTA anticoagulated tube',
                        'Insufficient volume for analysis (QNS)',
                        'Mislabeled / unlabelled sample container',
                        'Wrong vacutainer tube used for test order',
                        'Transit time exceeded acceptable stability window (>4h)',
                    ],
                    'b2b_partners' => [
                        'Al-Shifa Clinic',
                        'Somali Diagnostic Center',
                        'Hodan Family Practice',
                        'Darasalam Medical Center',
                        'Wadajir Poly Clinic',
                        'Banadir Central Referral Lab',
                    ],
                    'nil_reagents' => [
                        'CBC Diluent / Lyse Reagent',
                        'Biochemistry Creatinine Kit',
                        'Electrolyte Analyzer ISE Calibrant',
                        'HBsAg Rapid Test Strips',
                        'Blood Grouping Antisera Anti-A',
                        'Troponin I Quantitative Cassettes',
                        'Urine Dipstick 10-Parameter',
                        'Widal Rapid Titre Kit',
                        'Gram Stain Set',
                    ],
                    'suspendable_tests' => [
                        'Electrolytes (Na+/K+/Cl-)',
                        'Serum Creatinine',
                        'Troponin I',
                        'Lipid Profile',
                        'Blood Cross-matching',
                        'Thyroid Profile (TSH/FT4)',
                    ],
                    'suppliers' => [
                        'Somali Medical Supplies Co.',
                        'Horn of Africa Diagnostic Distributors',
                        'Banadir Scientific & Healthcare Ltd.',
                        'Global Health Reagents Mogadishu',
                    ],
                ],
                'sort_order' => 2,
            ],
            [
                'slug' => 'emergency',
                'name_en' => 'Emergency Department (E.R)',
                'name_so' => 'Qaybta Gurmadka Degdegga ah (E.R)',
                'form_code' => 'ANL-ERD-02',
                'cadence' => 'shift_based',
                'allowed_shifts' => ['Morning', 'Afternoon', 'Night', '24-Hour Daily Summary'],
                'dropdown_options' => [
                    'disposition_wards' => ['Male IPD Ward', 'Female IPD Ward', 'Pediatric Ward', 'Maternity Ward', 'ICU / High Dependency', 'Home Discharge', 'Referred to Banadir Hospital', 'Left Against Medical Advice (LAMA)'],
                    'procedure_types' => ['Circumcision', 'Orthopedic / Cast / Splinting', 'Minor Surgery / Suturing', 'Wound Dressing & Debridement', 'Nebulization / O2 Therapy', 'IV Fluids / Injections', 'ECG / Resuscitation', 'Foley Catheterization', 'Nasogastric Tube (NGT) Insertion', 'Other'],
                    'triage_categories' => ['Red (Immediate / Resuscitation)', 'Yellow (Urgent / High Dependency)', 'Green (Non-Urgent / Walking)', 'Black (Deceased / Expectant)'],
                    'common_diagnoses' => ['Acute Trauma / MVA', 'Severe Malaria with Vomiting', 'Acute Gastroenteritis (Dehydration)', 'Bronchial Asthma Exacerbation', 'Hypertensive Crisis', 'Acute Coronary Syndrome / Chest Pain', 'Sepsis / Febrile Illness', 'Diabetic Ketoacidosis (DKA)', 'Burn Injury', 'Acute Appendicitis Suspect'],
                ],
                'sort_order' => 3,
            ],
            [
                'slug' => 'inpatient',
                'name_en' => 'Inpatient Department (IPD)',
                'name_so' => 'Qaybta Bukaanka Jiifka (IPD)',
                'form_code' => 'ANL-IPD-03',
                'cadence' => '24_hour_daily',
                'allowed_shifts' => ['24-Hour Daily Summary'],
                'dropdown_options' => [
                    'rooms' => ['Room 101', 'Room 102', 'Room 103', 'Room 104', 'Room 105', 'Room 201', 'Room 202', 'Room 203', 'Maternity 01', 'Maternity 02', 'ICU Bed 1', 'ICU Bed 2', 'Pediatric Bed 1', 'Pediatric Bed 2'],
                    'specialties' => ['Ophthalmology', 'Gynecological', 'Orthopedic', 'General Surgery', 'Pediatric Medical', 'Internal Medicine', 'Neonatal Care'],
                    'discharge_dispositions' => ['Discharged Home Recovered', 'Discharged on Request / LAMA', 'Transferred to ICU', 'Referred to Banadir Hospital', 'Absconded', 'Deceased'],
                ],
                'sort_order' => 4,
            ],
            [
                'slug' => 'reception',
                'name_en' => 'Reception & Outpatient (OPD)',
                'name_so' => 'Qaybta Qaabilaadda & Bukaansocodka',
                'form_code' => 'ANL-REC-05',
                'cadence' => 'shift_based',
                'allowed_shifts' => ['Morning', 'Afternoon', 'Night', 'Full Day'],
                'dropdown_options' => [
                    'doctors' => ['Dr. Hatem', 'Dr. Keysar', 'Dr. Kaafi', 'Dr. Muslim', 'Dr. Safiyo', 'Dr. Huruse', 'Dr. Jama', 'Dr. Asha', 'Other Dr.'],
                    'payment_methods' => ['Cash (USD)', 'Cash (SOS)', 'Hormuud EVC Plus', 'Dahabshiil eDahab', 'Corporate Insurance', 'Free / Waiver'],
                    'visit_types' => ['New Consultation', 'Follow-Up Visit', 'Direct Lab Only', 'Direct Pharmacy Only', 'Emergency Triage', 'Dressing / Injections'],
                ],
                'sort_order' => 5,
            ],
        ];

        foreach ($departments as $deptData) {
            DepartmentForm::updateOrCreate(['slug' => $deptData['slug']], $deptData);
        }

        // 4. Secret DeepSeek Agent Configuration
        DeepseekConfig::updateOrCreate(
            ['id' => 1],
            [
                'selected_model' => 'deepseek-chat',
                'custom_system_prompt' => 'You are an elite Senior Hospital Operations & Clinical Analytics Director with 25+ years of experience in tertiary hospital governance, supply chain optimization, and clinical quality assurance. Analyze the provided multi-department hospital daily metrics, identify hidden bottlenecks, compute key clinical ratios, compare with international benchmarks (WHO, JCI), and generate a boardroom-ready executive report with clear root-cause breakdowns and prioritized operational interventions.',
                'enable_web_search' => true,
                'temperature' => 0.70,
                'updated_by' => $superAdmin->id,
            ]
        );

        // 5. Multi-Day Seeded Reports (Today and past 7 days)
        $today = Carbon::today();

        for ($i = 7; $i >= 0; $i--) {
            $reportDate = $today->copy()->subDays($i)->toDateString();
            $isPast = $i > 0;
            $status = $isPast ? 'approved' : 'submitted';

            // 1. Reception Form
            $recFootfall = 110 + ($i * 6) + rand(-8, 12);
            $newOpd = (int) round($recFootfall * 0.42);
            $followUp = (int) round($recFootfall * 0.35);
            $walkinEr = (int) round($recFootfall * 0.13);
            $walkinLabPhm = $recFootfall - ($newOpd + $followUp + $walkinEr);

            DailyReport::updateOrCreate(
                [
                    'department' => 'reception',
                    'report_date' => $reportDate,
                    'shift' => 'Full Day',
                ],
                [
                    'form_code' => 'ANL-REC-05',
                    'lead_staff_name' => 'Maryan Shire',
                    'status' => $status,
                    'created_by' => $receptionUser->id,
                    'submitted_by' => $receptionUser->id,
                    'audited_by' => $isPast ? $analyticsOfficer->id : null,
                    'submitted_at' => Carbon::parse("$reportDate 16:30:00"),
                    'audited_at' => $isPast ? Carbon::parse("$reportDate 17:30:00") : null,
                    'audit_feedback' => $isPast ? 'All footfall totals reconciled with cash register receipts.' : null,
                    'form_data' => [
                        'section_a' => [
                            'new_opd_registrations' => $newOpd,
                            'followup_opd_visits' => $followUp,
                            'emergency_walkin_routed_er' => $walkinEr,
                            'direct_lab_pharmacy_walkins' => $walkinLabPhm,
                            'total_footfall' => $recFootfall,
                        ],
                        'section_b_doctors' => [
                            ['doctor' => 'Dr. Hatem', 'booked' => 18, 'completed' => 16, 'noshow' => 2],
                            ['doctor' => 'Dr. Keysar', 'booked' => 15, 'completed' => 14, 'noshow' => 1],
                            ['doctor' => 'Dr. Kaafi', 'booked' => 22, 'completed' => 20, 'noshow' => 2],
                            ['doctor' => 'Dr. Muslim', 'booked' => 14, 'completed' => 12, 'noshow' => 2],
                            ['doctor' => 'Dr. Safiyo', 'booked' => 16, 'completed' => 15, 'noshow' => 1],
                            ['doctor' => 'Dr. Huruse', 'booked' => 12, 'completed' => 11, 'noshow' => 1],
                            ['doctor' => 'Dr. Jama', 'booked' => 10, 'completed' => 9, 'noshow' => 1],
                            ['doctor' => 'Other Dr.', 'booked' => 8, 'completed' => 7, 'noshow' => 1],
                        ],
                        'section_c' => [
                            'patients_left_due_delay' => rand(1, 4),
                            'system_printer_issues' => 0,
                            'needs' => ['Need 2 new barcode sticker rolls', 'Queue management display refresh'],
                        ],
                    ],
                    'summary_metrics' => [
                        'total_footfall' => $recFootfall,
                        'completed_consultations' => 104,
                        'routed_to_er' => $walkinEr,
                    ],
                ]
            );

            // 2. Pharmacy Form
            $insideRx = 85 + rand(-5, 8);
            $outsideRx = 22 + rand(-3, 6);
            $totalServedRx = $insideRx + $outsideRx;
            $itemsDispensed = (int) round($totalServedRx * 2.4);
            $dispensedValue = $totalServedRx * 16.50;

            // Root causes for unserved
            $unservedPrice = rand(4, 9);
            $unservedStockout = 12 + ($i % 3); // Continuous stockout
            $unservedBrand = rand(2, 5);
            $unservedDropoff = rand(3, 7); // "Pharmacy lama iman"
            $unservedUnknown = rand(0, 2);
            $unservedOther = rand(1, 3);
            $totalUnserved = $unservedPrice + $unservedStockout + $unservedBrand + $unservedDropoff + $unservedUnknown + $unservedOther;

            $debtTaken = 240.00 + ($i * 15);
            $debtPaid = 180.00 + ($i * 10);
            $refundError = 25.00;
            $refundReturn = 45.00;

            DailyReport::updateOrCreate(
                [
                    'department' => 'pharmacy',
                    'report_date' => $reportDate,
                    'shift' => 'Full Day',
                ],
                [
                    'form_code' => 'ANL-PHM-01',
                    'lead_staff_name' => 'Ahmed Warsame (PharmD)',
                    'status' => $status,
                    'created_by' => $pharmacyUser->id,
                    'submitted_by' => $pharmacyUser->id,
                    'audited_by' => $isPast ? $analyticsOfficer->id : null,
                    'submitted_at' => Carbon::parse("$reportDate 17:00:00"),
                    'audited_at' => $isPast ? Carbon::parse("$reportDate 17:45:00") : null,
                    'audit_feedback' => $isPast ? 'Prescription fill rate is 78.4%. Stockouts in Ceftriaxone 1g highlighted.' : null,
                    'form_data' => [
                        'section_a' => [
                            'inside_rx_count' => $insideRx,
                            'inside_items_dispensed' => (int) round($insideRx * 2.3),
                            'inside_notes' => 'IPD & OPD Ward orders filled smoothly.',
                            'outside_rx_count' => $outsideRx,
                            'outside_items_dispensed' => (int) round($outsideRx * 2.6),
                            'outside_sources' => ['Walk-In Local', 'Madina Referral'],
                            'total_served_rx' => $totalServedRx,
                            'total_items_dispensed' => $itemsDispensed,
                            'total_dispensed_value' => $dispensedValue,
                            'total_unserved_patients' => $totalUnserved,
                        ],
                        'section_b_doctors' => [
                            ['doctor' => 'Dr. Hatem', 'served_rx' => 14, 'unserved_rx' => 3],
                            ['doctor' => 'Dr. Keysar', 'served_rx' => 12, 'unserved_rx' => 2],
                            ['doctor' => 'Dr. Kaafi', 'served_rx' => 17, 'unserved_rx' => 4],
                            ['doctor' => 'Dr. Muslim', 'served_rx' => 10, 'unserved_rx' => 3],
                            ['doctor' => 'Dr. Safiyo', 'served_rx' => 13, 'unserved_rx' => 2],
                            ['doctor' => 'Dr. Huruse', 'served_rx' => 9, 'unserved_rx' => 2],
                            ['doctor' => 'Dr. Jama', 'served_rx' => 8, 'unserved_rx' => 2],
                            ['doctor' => 'Other Dr.', 'served_rx' => 6, 'unserved_rx' => 2],
                        ],
                        'section_c_root_causes' => [
                            'price' => $unservedPrice,
                            'stockout' => $unservedStockout,
                            'brand_unavailable' => $unservedBrand,
                            'did_not_present' => $unservedDropoff,
                            'unknown' => $unservedUnknown,
                            'other' => $unservedOther,
                            'other_details' => 'Requested injection form only available elsewhere.',
                            'total_unserved' => $totalUnserved,
                        ],
                        'section_d_financials' => [
                            'refund_billing_error_cases' => 2,
                            'refund_billing_error_amount' => $refundError,
                            'refund_returned_meds_cases' => 3,
                            'refund_returned_meds_amount' => $refundReturn,
                            'debt_taken_cases' => 4,
                            'debt_taken_amount' => $debtTaken,
                            'debt_paid_cases' => 3,
                            'debt_paid_amount' => $debtPaid,
                            'net_credit_exposure' => ($debtTaken - $debtPaid) + ($refundError + $refundReturn),
                        ],
                        'section_e_inventory' => [
                            'out_of_stock_drugs' => ['Ceftriaxone 1g Inj', 'Amoxiclav 625mg Tab', 'Insulin Regular 100IU'],
                            'near_expiry_drugs' => ['Gentamicin 80mg Inj', 'Diclofenac 75mg Inj'],
                            'urgent_needs' => ['Urgent PO for Ceftriaxone 500 vials', 'Cold-chain fridge temperature logger battery replacement'],
                        ],
                    ],
                    'summary_metrics' => [
                        'total_served_rx' => $totalServedRx,
                        'total_unserved_patients' => $totalUnserved,
                        'fill_rate_percent' => round(($totalServedRx / ($totalServedRx + $totalUnserved)) * 100, 1),
                        'stockout_unserved_count' => $unservedStockout,
                        'net_debt_exposure' => ($debtTaken - $debtPaid),
                    ],
                ]
            );

            // 3. Emergency Department (E.R)
            $erAcute = 18 + rand(-2, 4);
            $erOpdSeen = 12 + rand(-2, 3);
            $erFollowup = 5 + rand(-1, 2);
            $erTotal = $erAcute + $erOpdSeen + $erFollowup;

            $erAdmittedToIpd = 7 + ($i % 2); // Matched with Inpatient newly admitted source ER!
            $erObsWard = 6;
            $erDischargedStable = $erTotal - ($erAdmittedToIpd + $erObsWard + 2);
            $erReferredOut = 1;
            $erLama = 1;

            $erDeathsInEr = ($i == 2 || $i == 5) ? 1 : 0;
            $erDoa = ($i == 3) ? 1 : 0;

            DailyReport::updateOrCreate(
                [
                    'department' => 'emergency',
                    'report_date' => $reportDate,
                    'shift' => '24-Hour Daily Summary',
                ],
                [
                    'form_code' => 'ANL-ERD-02',
                    'lead_staff_name' => 'Dr. Hassan Ali',
                    'status' => $status,
                    'created_by' => $erUser->id,
                    'submitted_by' => $erUser->id,
                    'audited_by' => $isPast ? $analyticsOfficer->id : null,
                    'submitted_at' => Carbon::parse("$reportDate 18:00:00"),
                    'audited_at' => $isPast ? Carbon::parse("$reportDate 18:30:00") : null,
                    'audit_feedback' => $isPast ? 'Audited. ER-to-IPD admission count matches IPD census precisely.' : null,
                    'form_data' => [
                        'section_a' => [
                            'emergency_cases_acute' => $erAcute,
                            'opd_cases_seen_in_er' => $erOpdSeen,
                            'followup_cases' => $erFollowup,
                            'total_er_cases' => $erTotal,
                            'disposition_admitted_ipd' => $erAdmittedToIpd,
                            'disposition_obs_ward' => $erObsWard,
                            'disposition_discharged_stable' => $erDischargedStable,
                            'disposition_referred_out' => $erReferredOut,
                            'disposition_lama' => $erLama,
                        ],
                        'section_b_demographics' => [
                            'male_count' => (int) round($erTotal * 0.54),
                            'female_count' => $erTotal - (int) round($erTotal * 0.54),
                            'under_5' => (int) round($erTotal * 0.22),
                            'age_5_17' => (int) round($erTotal * 0.18),
                            'age_18_59' => (int) round($erTotal * 0.44),
                            'age_60_plus' => $erTotal - ((int) round($erTotal * 0.22) + (int) round($erTotal * 0.18) + (int) round($erTotal * 0.44)),
                        ],
                        'section_c_procedures' => [
                            'circumcision' => 2,
                            'orthopedic_cast' => 3,
                            'minor_surgery_suturing' => 5,
                            'wound_dressing' => 8,
                            'nebulization_o2' => 7,
                            'iv_fluids_injections' => 14,
                            'ecg_resuscitation' => 4,
                            'other' => 2,
                            'total_procedures' => 45,
                        ],
                        'section_d_critical_cases' => [
                            [
                                'case_no' => 'C-01',
                                'patient_id' => 'ER-9821',
                                'sex' => 'M',
                                'age' => 48,
                                'diagnosis' => 'Acute Coronary Syndrome (STEMI)',
                                'disposition' => 'ICU Bed 1',
                            ],
                            [
                                'case_no' => 'C-02',
                                'patient_id' => 'ER-9834',
                                'sex' => 'F',
                                'age' => 29,
                                'diagnosis' => 'Severe Eclampsia / Hypertensive Crisis',
                                'disposition' => 'Maternity High Dependency',
                            ],
                        ],
                        'section_e_mortality' => [
                            'doa_count' => $erDoa,
                            'died_in_er_count' => $erDeathsInEr,
                            'cases' => $erDeathsInEr > 0 ? [
                                [
                                    'arrival_status' => 'Died in E.R (<24 hrs)',
                                    'sex' => 'M',
                                    'age' => 64,
                                    'cause_of_death' => 'Cardiogenic Shock secondary to AMI',
                                    'time_of_death' => '14:20',
                                ],
                            ] : [],
                        ],
                        'section_f_needs' => [
                            'Defibrillator pediatric pads exhausted',
                            'Oxygen flow meter replacement for Bay 3',
                            'Suction catheters 14Fr restocking',
                        ],
                    ],
                    'summary_metrics' => [
                        'total_er_cases' => $erTotal,
                        'admitted_to_ipd' => $erAdmittedToIpd,
                        'conversion_rate_percent' => round(($erAdmittedToIpd / $erTotal) * 100, 1),
                        'in_er_deaths' => $erDeathsInEr,
                        'doa_deaths' => $erDoa,
                    ],
                ]
            );

            // 4. Inpatient Department (IPD)
            // Census self-balancing: [A] + [B] - [C] - [D] - [E] == [F]
            $censusPrev = 38 + ($i * 2);
            $censusNewFromEr = $erAdmittedToIpd; // Exact match!
            $censusNewFromOpd = 4;
            $censusNew = $censusNewFromEr + $censusNewFromOpd;
            $censusDischarged = 8;
            $censusDama = 1;
            $censusTotalDischarges = $censusDischarged + $censusDama;
            $censusReferredOut = 1;
            $censusDeaths = ($i == 3) ? 1 : 0;

            $censusCurrentEnding = $censusPrev + $censusNew - $censusTotalDischarges - $censusReferredOut - $censusDeaths;
            $patientBedDays = 46;

            DailyReport::updateOrCreate(
                [
                    'department' => 'inpatient',
                    'report_date' => $reportDate,
                    'shift' => '24-Hour Daily Summary',
                ],
                [
                    'form_code' => 'ANL-IPD-03',
                    'lead_staff_name' => 'Sister Halima Abdi',
                    'status' => $status,
                    'created_by' => $ipdUser->id,
                    'submitted_by' => $ipdUser->id,
                    'audited_by' => $isPast ? $analyticsOfficer->id : null,
                    'submitted_at' => Carbon::parse("$reportDate 18:30:00"),
                    'audited_at' => $isPast ? Carbon::parse("$reportDate 19:00:00") : null,
                    'audit_feedback' => $isPast ? 'Bed census reconciled perfectly: [A]+[B]-[C]-[D]-[E]=[F]. Verified.' : null,
                    'form_data' => [
                        'header' => [
                            'total_active_beds' => 60,
                            'occupied_rooms' => ['Room 101', 'Room 102', 'Room 201', 'Maternity 01', 'ICU Bed 1'],
                        ],
                        'section_a_census' => [
                            'previous_admissions_a' => $censusPrev,
                            'newly_admitted_b' => $censusNew,
                            'newly_admitted_from_er' => $censusNewFromEr,
                            'newly_admitted_from_opd' => $censusNewFromOpd,
                            'discharged_recovered_c1' => $censusDischarged,
                            'discharged_dama_c2' => $censusDama,
                            'total_discharged_c' => $censusTotalDischarges,
                            'referred_out_d' => $censusReferredOut,
                            'deaths_today_e' => $censusDeaths,
                            'current_admissions_f' => $censusCurrentEnding,
                            'formula_check_passed' => true,
                            'total_patient_bed_days' => $patientBedDays,
                        ],
                        'section_b_admissions_log' => [
                            ['room_no' => 'Room 102', 'sex' => 'M', 'age' => 45, 'diagnosis' => 'Severe Pneumonia', 'source' => 'E.R'],
                            ['room_no' => 'Maternity 01', 'sex' => 'F', 'age' => 26, 'diagnosis' => 'Labor Stage 1 (G2P1)', 'source' => 'OPD'],
                        ],
                        'section_c_maternity' => [
                            'nvd_normal_delivery' => 3,
                            'c_section_delivery' => 1,
                            'newborn_male' => 2,
                            'newborn_female' => 2,
                            'twins_multiples' => 0,
                            'stillbirths_iufd' => 0,
                            'nicu_admissions' => 1,
                        ],
                        'section_d_surgeries' => [
                            'surgical_operations' => ['total' => 4, 'ophthalmology' => 1, 'gynecology' => 1, 'orthopedic' => 1, 'general_surgery' => 1, 'other' => 0],
                            'medical_services' => ['total' => 18, 'ophthalmology' => 2, 'gynecology' => 4, 'orthopedic' => 3, 'general_surgery' => 5, 'other' => 4],
                        ],
                        'section_e_critical_and_mortality' => [
                            'critical_cases_count' => 3,
                            'deaths_count' => $censusDeaths,
                            'deaths_detail' => $censusDeaths > 0 ? [
                                ['room_no' => 'ICU Bed 2', 'sex' => 'F', 'age' => 72, 'diagnosis' => 'Septic Shock / Multi-Organ Failure', 'time' => '06:45'],
                            ] : [],
                        ],
                        'section_f_needs' => [
                            'Syringe pumps (need 2 units calibration)',
                            'Maternity ward clean linen sets replenishment',
                        ],
                    ],
                    'summary_metrics' => [
                        'current_admissions' => $censusCurrentEnding,
                        'total_active_beds' => 60,
                        'occupancy_rate_percent' => round(($censusCurrentEnding / 60) * 100, 1),
                        'alos_days' => round($patientBedDays / max(1, $censusTotalDischarges), 1),
                        'c_section_rate_percent' => 25.0,
                        'dama_rate_percent' => round(($censusDama / max(1, $censusTotalDischarges)) * 100, 1),
                    ],
                ]
            );

            // 5. Laboratory Department
            $labOpdPts = 62 + rand(-4, 6);
            $labOpdTests = (int) round($labOpdPts * 2.3);
            $labErPts = 18 + rand(-2, 3);
            $labErTests = (int) round($labErPts * 2.8);
            $labIpdPts = 24 + rand(-3, 4);
            $labIpdTests = (int) round($labIpdPts * 3.1);
            $labWalkinPts = 14 + rand(-2, 3);
            $labPartnerPts = 19 + rand(-2, 4);
            $labOutsidePts = $labWalkinPts + $labPartnerPts;
            $labOutsideTests = (int) round($labOutsidePts * 2.1);

            $labTotalPts = $labOpdPts + $labErPts + $labIpdPts + $labOutsidePts;
            $labTotalTests = $labOpdTests + $labErTests + $labIpdTests + $labOutsideTests;

            DailyReport::updateOrCreate(
                [
                    'department' => 'laboratory',
                    'report_date' => $reportDate,
                    'shift' => '24-Hour Daily Summary',
                ],
                [
                    'form_code' => 'ANL-LAB-04',
                    'lead_staff_name' => 'Khadra Duale',
                    'status' => $status,
                    'created_by' => $labUser->id,
                    'submitted_by' => $labUser->id,
                    'audited_by' => $isPast ? $analyticsOfficer->id : null,
                    'submitted_at' => Carbon::parse("$reportDate 19:15:00"),
                    'audited_at' => $isPast ? Carbon::parse("$reportDate 19:45:00") : null,
                    'audit_feedback' => $isPast ? 'Tests-per-patient ratio is healthy at 2.5. Nil reagent CBC Lyse flagged for procurement.' : null,
                    'form_data' => [
                        'section_a_volume' => [
                            'opd_patients' => $labOpdPts,
                            'opd_tests' => $labOpdTests,
                            'er_patients' => $labErPts,
                            'er_tests' => $labErTests,
                            'ipd_patients' => $labIpdPts,
                            'ipd_tests' => $labIpdTests,
                            'outside_patients' => $labOutsidePts,
                            'outside_tests' => $labOutsideTests,
                            'outside_walkin_pts' => $labWalkinPts,
                            'outside_partner_pts' => $labPartnerPts,
                            'total_patients' => $labTotalPts,
                            'total_tests' => $labTotalTests,
                            'tests_per_patient_ratio' => round($labTotalTests / max(1, $labTotalPts), 2),
                        ],
                        'section_b_partners' => [
                            ['partner' => 'Al-Shifa Clinic', 'patients' => 8, 'tests' => 19],
                            ['partner' => 'Somali Diagnostic Center', 'patients' => 5, 'tests' => 11],
                            ['partner' => 'Hodan Family Practice', 'patients' => 4, 'tests' => 9],
                            ['partner' => 'Darasalam Medical Center', 'patients' => 2, 'tests' => 5],
                        ],
                        'section_c_benches_and_quality' => [
                            'hematology' => (int) round($labTotalTests * 0.32),
                            'biochemistry' => (int) round($labTotalTests * 0.28),
                            'serology_immunology' => (int) round($labTotalTests * 0.18),
                            'microbiology_parasitology' => (int) round($labTotalTests * 0.12),
                            'hormones_special' => (int) round($labTotalTests * 0.06),
                            'blood_bank' => (int) round($labTotalTests * 0.04),
                            'sample_rejections' => 3,
                            'delayed_tests_tat' => 5,
                            'critical_values_reported' => 4,
                        ],
                        'section_d_nil_items' => [
                            'nil_items_count' => 3,
                            'items' => [
                                'CBC Diluent / Lyse Reagent (Sysmex)',
                                'Electrolyte Analyzer ISE Calibrant Pack',
                                'Troponin I Quantitative Cassettes',
                            ],
                        ],
                        'section_e_lab_operations' => [
                            'incomplete_tests' => [
                                ['patient_id' => 'P-1042', 'sample_id' => 'RL-8821', 'patient_name' => 'Fartun Ali (IPD Rm 201)', 'test_name' => 'Blood Culture & Sensitivity', 'bench' => 'Microbiology', 'reason' => 'Incubation protocol in progress (24-48h)', 'status' => 'In Incubation', 'action_required' => 'Read plate colonies tomorrow morning 08:30 AM'],
                                ['patient_id' => 'P-2098', 'sample_id' => 'RL-8834', 'patient_name' => 'Omar Dahir (ER Ward)', 'test_name' => 'Serum Potassium (K+)', 'bench' => 'Biochemistry', 'reason' => 'Grossly hemolyzed sample - Redraw requested from ER', 'status' => 'Redraw Ordered', 'action_required' => 'STAT rerun upon redraw delivery from ER'],
                            ],
                            'equipment_troubleshooting' => [
                                ['equipment_name' => 'Mindray BS-240 Chemistry Analyzer', 'issue_description' => 'Photometer optical zero drift detected on 340nm filter during morning startup', 'troubleshooting_action' => 'Cleaned optical cuvettes with deproteinizing solution, replaced halogen bulb, re-ran water blank test', 'status' => 'Resolved & Online', 'engineer_notified' => 'Biomedical Eng. Ahmed notified'],
                            ],
                            'shift_handover' => [
                                'shift_notes' => 'Morning shift concluded with peak OPD workload managed successfully. All critical stats communicated to attending physicians. Culture plates in Incubator #2 logged for morning readout.',
                                'incoming_staff' => 'Mohamed Omar',
                                'manager_notes' => 'Reviewed & verified. Stock transfer of ISE Calibrant approved from central depot.',
                                'urgency_level' => 'routine',
                            ],
                        ],
                        'section_f_quality_control' => [
                            'calibrations' => [
                                ['test_name' => 'Blood Glucose (Fasting / Random)', 'analyzer' => 'Mindray BS-240', 'standard_absorption' => 0.385, 'measured_absorption' => 0.382, 'calibrator_lot' => 'GLU-26-09', 'qc_status' => 'passed', 'calibrated_by' => 'Khadra Duale', 'calibration_date' => $reportDate.' 07:30', 'corrective_action' => 'Target CV < 2%'],
                                ['test_name' => 'Serum Creatinine (Jaffe Kinetic)', 'analyzer' => 'Mindray BS-240', 'standard_absorption' => 0.290, 'measured_absorption' => 0.288, 'calibrator_lot' => 'CRE-26-11', 'qc_status' => 'passed', 'calibrated_by' => 'Khadra Duale', 'calibration_date' => $reportDate.' 07:35', 'corrective_action' => 'Optics zeroed with reagent blank'],
                                ['test_name' => 'Blood Urea Nitrogen (BUN) / Urea', 'analyzer' => 'Mindray BS-240', 'standard_absorption' => 0.415, 'measured_absorption' => 0.410, 'calibrator_lot' => 'URE-26-08', 'qc_status' => 'passed', 'calibrated_by' => 'Khadra Duale', 'calibration_date' => $reportDate.' 07:40', 'corrective_action' => 'Normal kinetic standard curve'],
                                ['test_name' => 'AST / SGOT (Transaminase)', 'analyzer' => 'Mindray BS-240', 'standard_absorption' => 0.175, 'measured_absorption' => 0.174, 'calibrator_lot' => 'ENZ-26-04', 'qc_status' => 'passed', 'calibrated_by' => 'Khadra Duale', 'calibration_date' => $reportDate.' 07:45', 'corrective_action' => 'Linearity verified'],
                                ['test_name' => 'ALT / SGPT (Transaminase)', 'analyzer' => 'Mindray BS-240', 'standard_absorption' => 0.185, 'measured_absorption' => 0.182, 'calibrator_lot' => 'ENZ-26-04', 'qc_status' => 'passed', 'calibrated_by' => 'Khadra Duale', 'calibration_date' => $reportDate.' 07:45', 'corrective_action' => 'Linearity verified'],
                                ['test_name' => 'Total Bilirubin (DSA Mod.)', 'analyzer' => 'Mindray BS-240', 'standard_absorption' => 0.520, 'measured_absorption' => 0.515, 'calibrator_lot' => 'BIL-26-03', 'qc_status' => 'passed', 'calibrated_by' => 'Khadra Duale', 'calibration_date' => $reportDate.' 07:50', 'corrective_action' => 'Protected from direct light'],
                                ['test_name' => 'Direct Bilirubin', 'analyzer' => 'Mindray BS-240', 'standard_absorption' => 0.470, 'measured_absorption' => 0.466, 'calibrator_lot' => 'BIL-26-03', 'qc_status' => 'passed', 'calibrated_by' => 'Khadra Duale', 'calibration_date' => $reportDate.' 07:52', 'corrective_action' => 'Valid'],
                                ['test_name' => 'Total Cholesterol (CHOD-PAP)', 'analyzer' => 'Mindray BS-240', 'standard_absorption' => 0.345, 'measured_absorption' => 0.342, 'calibrator_lot' => 'LIP-26-07', 'qc_status' => 'passed', 'calibrated_by' => 'Khadra Duale', 'calibration_date' => $reportDate.' 07:55', 'corrective_action' => 'Slope = 1.01'],
                                ['test_name' => 'Triglycerides (GPO-PAP)', 'analyzer' => 'Mindray BS-240', 'standard_absorption' => 0.315, 'measured_absorption' => 0.310, 'calibrator_lot' => 'LIP-26-07', 'qc_status' => 'passed', 'calibrated_by' => 'Khadra Duale', 'calibration_date' => $reportDate.' 07:58', 'corrective_action' => 'Slope = 0.99'],
                                ['test_name' => 'Serum Uric Acid', 'analyzer' => 'Mindray BS-240', 'standard_absorption' => 0.265, 'measured_absorption' => 0.262, 'calibrator_lot' => 'URI-26-02', 'qc_status' => 'passed', 'calibrated_by' => 'Khadra Duale', 'calibration_date' => $reportDate.' 08:00', 'corrective_action' => 'Optimal recovery'],
                                ['test_name' => 'Total Protein (Biuret)', 'analyzer' => 'Mindray BS-240', 'standard_absorption' => 0.440, 'measured_absorption' => 0.438, 'calibrator_lot' => 'PRO-26-05', 'qc_status' => 'passed', 'calibrated_by' => 'Khadra Duale', 'calibration_date' => $reportDate.' 08:05', 'corrective_action' => 'Within 1 SD'],
                                ['test_name' => 'Serum Albumin (BCG)', 'analyzer' => 'Mindray BS-240', 'standard_absorption' => 0.395, 'measured_absorption' => 0.392, 'calibrator_lot' => 'PRO-26-05', 'qc_status' => 'passed', 'calibrated_by' => 'Khadra Duale', 'calibration_date' => $reportDate.' 08:08', 'corrective_action' => 'Within 1 SD'],
                                ['test_name' => 'Alkaline Phosphatase (ALP)', 'analyzer' => 'Mindray BS-240', 'standard_absorption' => 0.210, 'measured_absorption' => 0.206, 'calibrator_lot' => 'ALP-26-01', 'qc_status' => 'passed', 'calibrated_by' => 'Khadra Duale', 'calibration_date' => $reportDate.' 08:12', 'corrective_action' => 'Linear check passed'],
                                ['test_name' => 'Electrolytes: Sodium (Na+ ISE)', 'analyzer' => 'ISE Electrolyte Pro', 'standard_absorption' => 140.0, 'measured_absorption' => 139.6, 'calibrator_lot' => 'ISE-CAL-A', 'qc_status' => 'passed', 'calibrated_by' => 'Khadra Duale', 'calibration_date' => $reportDate.' 08:15', 'corrective_action' => 'Slope -58.2 mV'],
                                ['test_name' => 'Electrolytes: Potassium (K+ ISE)', 'analyzer' => 'ISE Electrolyte Pro', 'standard_absorption' => 4.00, 'measured_absorption' => 4.03, 'calibrator_lot' => 'ISE-CAL-A', 'qc_status' => 'passed', 'calibrated_by' => 'Khadra Duale', 'calibration_date' => $reportDate.' 08:15', 'corrective_action' => 'Slope +56.8 mV'],
                                ['test_name' => 'Electrolytes: Chloride (Cl- ISE)', 'analyzer' => 'ISE Electrolyte Pro', 'standard_absorption' => 100.0, 'measured_absorption' => 99.5, 'calibrator_lot' => 'ISE-CAL-A', 'qc_status' => 'passed', 'calibrated_by' => 'Khadra Duale', 'calibration_date' => $reportDate.' 08:15', 'corrective_action' => 'Slope -52.4 mV'],
                                ['test_name' => 'CBC / 5-Part Control (Sysmex)', 'analyzer' => 'Sysmex XP-300', 'standard_absorption' => 1.000, 'measured_absorption' => 0.995, 'calibrator_lot' => 'EIGHTCHECK-26L', 'qc_status' => 'passed', 'calibrated_by' => 'Khadra Duale', 'calibration_date' => $reportDate.' 08:20', 'corrective_action' => 'Tri-level controls in target range'],
                                ['test_name' => 'HbA1c Glycated Hemoglobin', 'analyzer' => 'Mindray BS-240', 'standard_absorption' => 0.145, 'measured_absorption' => 0.142, 'calibrator_lot' => 'HBA1C-26-01', 'qc_status' => 'passed', 'calibrated_by' => 'Khadra Duale', 'calibration_date' => $reportDate.' 08:25', 'corrective_action' => 'R2 = 0.999'],
                            ],
                        ],
                        'section_g_inventory_supplies' => [
                            'received_items' => [
                                ['item_name' => 'CBC Diluent (20L Cubitainer Pack)', 'category' => 'Hematology Reagent', 'brand' => 'Sysmex', 'quantity' => 4, 'unit' => 'Box', 'lot_number' => 'LOT-26D-9102', 'expiry_date' => '2027-12-31', 'storage_condition' => 'Controlled Room Temp (15-25°C)', 'package_condition' => 'Intact & Sealed', 'who_received' => 'Khadra Duale', 'received_date' => $reportDate.' 10:30', 'supplier_name' => 'Mogadishu Medical & Diagnostics Ltd', 'supplier_representative' => 'Abdirahman Farah (Tel: +252 61 584 9201)', 'invoice_po_ref' => 'DN-9941 / PO-881', 'acceptance_status' => 'Accepted & Stocked'],
                                ['item_name' => 'Glucose GOD-POD Reagent Kit (4x50ml)', 'category' => 'Biochemistry Reagent', 'brand' => 'Spinreact', 'quantity' => 6, 'unit' => 'Kit', 'lot_number' => 'LOT-26G-4410', 'expiry_date' => '2027-08-30', 'storage_condition' => 'Cold Chain (2°C - 8°C)', 'package_condition' => 'Cold Chain Maintained (Ice Packs Frozen)', 'who_received' => 'Khadra Duale', 'received_date' => $reportDate.' 11:15', 'supplier_name' => 'Horn of Africa Pharma Importers', 'supplier_representative' => 'Hassan Warsame (Tel: +252 61 933 1120)', 'invoice_po_ref' => 'INV-2026-118 / PO-883', 'acceptance_status' => 'Accepted & Transferred to Cold Storage'],
                                ['item_name' => 'EDTA K2 Vacuum Blood Collection Tubes 4ml', 'category' => 'Consumables & Labware', 'brand' => 'BD Vacutainer', 'quantity' => 10, 'unit' => 'Box', 'lot_number' => 'LOT-26E-1002', 'expiry_date' => '2028-05-15', 'storage_condition' => 'Controlled Room Temp (15-25°C)', 'package_condition' => 'Intact & Undamaged', 'who_received' => 'Khadra Duale', 'received_date' => $reportDate.' 14:00', 'supplier_name' => 'Global Diagnostic Supplies', 'supplier_representative' => 'Farhan Qasim (Tel: +252 61 710 4488)', 'invoice_po_ref' => 'DN-4019 / PO-884', 'acceptance_status' => 'Accepted & Stocked'],
                            ],
                        ],
                        'section_e_f_suspensions' => [
                            'tests_suspended' => [
                                ['test' => 'Electrolytes (Na+/K+/Cl-)', 'reason' => 'Reagent Stockout (ISE Calibrant)'],
                            ],
                            'tests_restarted' => [
                                ['test' => 'Serum Creatinine', 'time' => '11:00 AM'],
                            ],
                        ],
                        'section_g_needs' => [
                            'UPS battery backup for Mindray BC-5380 analyzer',
                            'Temperature loggers for cold storage blood bank',
                        ],
                    ],
                    'summary_metrics' => [
                        'total_lab_patients' => $labTotalPts,
                        'total_lab_tests' => $labTotalTests,
                        'tests_per_patient_ratio' => round($labTotalTests / max(1, $labTotalPts), 2),
                        'partner_share_percent' => round(($labPartnerPts / max(1, $labTotalPts)) * 100, 1),
                        'nil_items_count' => 3,
                        'sample_rejection_rate' => round((3 / max(1, $labTotalPts)) * 100, 1),
                        'incomplete_tests_count' => 2,
                        'equipment_issues_count' => 1,
                        'calibrated_tests_count' => 18,
                        'supplies_received_count' => 3,
                    ],
                ]
            );
        }

        // 6. Audit Trail Samples
        $actions = [
            ['action' => 'LOGIN', 'user' => $hospitalAdmin, 'desc' => 'Logged into Executive Dashboard', 'ip' => '192.168.1.10'],
            ['action' => 'SUBMIT', 'user' => $pharmacyUser, 'desc' => 'Submitted ANL-PHM-01 Daily Pharmacy Summary', 'dept' => 'pharmacy', 'ip' => '192.168.1.42'],
            ['action' => 'APPROVE', 'user' => $analyticsOfficer, 'desc' => 'Approved ANL-IPD-03 Inpatient Bed Census with full reconciliation verification', 'dept' => 'inpatient', 'ip' => '192.168.1.25'],
            ['action' => 'UPDATE', 'user' => $hospitalAdmin, 'desc' => 'Updated Hospital submission deadline to 08:30 AM', 'ip' => '192.168.1.10'],
            ['action' => 'SUBMIT', 'user' => $erUser, 'desc' => 'Submitted ANL-ERD-02 Emergency Daily Report', 'dept' => 'emergency', 'ip' => '192.168.1.33'],
        ];

        foreach ($actions as $act) {
            AuditLog::create([
                'user_id' => $act['user']->id,
                'action' => $act['action'],
                'department' => $act['dept'] ?? null,
                'description' => $act['desc'],
                'ip_address' => $act['ip'],
                'created_at' => Carbon::now()->subHours(rand(1, 24)),
            ]);
        }

        // 7. Executive Insight Seed
        ExecutiveInsight::updateOrCreate(
            ['report_type' => 'daily', 'period_date' => $today->toDateString()],
            [
                'title' => 'Executive Performance & Cross-Departmental Bottleneck Brief',
                'executive_summary' => 'Overall clinical operations demonstrated high patient capture across Outpatient and Emergency departments. However, Pharmacy prescription fill rate experienced a 14.8% contraction due to stockouts in first-line injectable antibiotics (Ceftriaxone 1g). Inpatient bed census reconciled with 100% mathematical integrity across all wards.',
                'key_findings' => [
                    'Hospital total daily footfall averaged 142 patients across reception and walk-in routes.',
                    'Emergency-to-Inpatient conversion stabilized at 21.4%, effectively populating internal medical wards.',
                    'Laboratory handled 342 tests across 137 patients with an optimal 2.50 tests/patient density.',
                    'Doctor OPD consultation capture reached 84.6% against internal pharmacy orders.',
                ],
                'bottlenecks_and_root_causes' => [
                    'Pharmacy lost 14 prescriptions directly attributed to Ceftriaxone 1g stockout (listed nil for 4 consecutive days).',
                    'Electrolyte test panel temporarily suspended in Laboratory due to delayed delivery of ISE calibrant pack.',
                    'Average waiting time in OPD triage peaked at 38 minutes between 10:30 AM and 12:15 PM.',
                ],
                'action_plan' => [
                    'Issue immediate emergency local purchase order for 400 vials of Ceftriaxone 1g and 2 ISE calibrant packs.',
                    'Schedule second pharmacist shift lead starting at 09:30 AM to compress OPD peak queue waiting times.',
                    'Establish buffer threshold alert at 10 days remaining stock for all 25 critical formulary items.',
                ],
                'medical_benchmarks' => [
                    'Inpatient Bed Occupancy Rate (BOR): 71.7% (WHO Recommended Target: 70%–80%)',
                    'Average Length of Stay (ALOS): 4.6 Days (Regional Acute Hospital Benchmark: 4.2–5.1 Days)',
                    'Cesarean Section Rate: 25.0% (WHO Target Ceiling: 15%–25%)',
                ],
                'status' => 'published',
                'published_by' => $hospitalAdmin->id,
                'is_ai_generated' => false,
            ]
        );
    }
}
