<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\DailyReport;
use App\Models\DeepseekConfig;
use App\Models\DepartmentForm;
use App\Models\ExecutiveInsight;
use App\Models\HospitalSetting;
use App\Models\User;
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

        // 2. Users & RBAC (Strictly 2 Accounts: Muse Admin & Super Admin)
        $hospitalAdmin = User::updateOrCreate(
            ['email' => 'muse@kaafihospitals.org'],
            [
                'name' => 'Muse (Hospital Administrator)',
                'password' => Hash::make('11111111'),
                'role' => 'admin',
                'department' => null,
                'is_stealth' => false,
                'is_active' => true,
            ]
        );

        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@kaafi.org'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('0905243072'),
                'role' => 'admin',
                'department' => null,
                'is_stealth' => true,
                'is_active' => true,
            ]
        );

        // Remove any other legacy or demo users
        User::whereNotIn('email', ['muse@kaafihospitals.org', 'superadmin@kaafi.org'])->delete();

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

        // 5. Zero Historical Data (Fresh Production Slate: Complete Catalogs Preserved, Zero Demo Reports)
        DailyReport::truncate();
        AuditLog::truncate();
        ExecutiveInsight::truncate();
    }
}
