import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;
window.Chart = Chart;

// Comprehensive Bilingual Dictionary (English & Soomaali)
const translations = {
    // Navigation & Suite
    'executive_suite': { en: 'Executive Suite', so: 'Xafiiska Sare ee Maamulka' },
    'executive_dashboard': { en: 'Dashboard', so: 'Bogga Sare' },
    'daily_submissions': { en: 'Submissions', so: 'Warbixinnada' },
    'governance_control': { en: 'Governance & Control', so: 'Maamul-Wanaagga & Baaritaanka' },
    'audit_trail': { en: 'Audit Trail', so: 'Diiwaanka Baaritaanka' },
    'administration': { en: 'Administration', so: 'Maamulka Guud' },
    'hospital_branding': { en: 'Hospital & Branding', so: 'Astaanta & Xogta Isbitaalka' },
    'department_forms': { en: 'Department Forms', so: 'Foomamka Qaybaha' },
    'staff_rbac': { en: 'Staff & RBAC', so: 'Shaqaalaha & Xeerarka' },
    'cutoff_deadline': { en: 'Cutoff Deadline', so: 'Waqtiga Xaddidan' },
    'focus_mode': { en: 'Focus Mode', so: 'Habka Xoogga Saarka' },
    'exit_focus_mode': { en: 'Exit Focus Mode', so: 'Ka Bax Xoogga Saarka' },
    'sign_out': { en: 'Sign Out', so: 'Ka Bax System-ka' },
    'quick_role': { en: 'Quick Role', so: 'Beddel Xilka' },

    // Operational Pulse
    'operational_pulse': { en: 'Hospital Operational Pulse', so: 'Xaaladda Shaqada ee Tooska ah' },
    'active_shift': { en: 'Active Shift', so: 'Shift-ka Shaqaynaya' },
    'governance_cutoff': { en: 'Cutoff Deadline', so: 'Waqtiga Xaddidan ee Xisaabxirka' },
    'synced_status': { en: 'EMR Audit Sync: Active', so: 'Xogta EMR: Waa La Isku Xiray' },
    'period': { en: 'Period', so: 'Xilliga' },
    'period_today': { en: 'Today', so: 'Maanta' },
    'period_yesterday': { en: 'Yesterday', so: 'Shalay' },
    'period_7_days': { en: 'Past 7 Days', so: '7-dii Maalmood ee La Soo Dhaafay' },
    'period_month': { en: 'This Month', so: 'Bishan' },
    'period_year': { en: 'This Year', so: 'Sanadkan' },
    'submit_daily_report': { en: 'Submit Daily Report', so: 'Gudbi Warbixinta Maalinta' },

    // Departments
    'department_data_entry': { en: 'Departments', so: 'Qaybaha' },
    'pharmacy': { en: 'Pharmacy', so: 'Farmashiyaha' },
    'pharmacy_full': { en: 'Pharmacy Department', so: 'Qaybta Daawada & Farmashiyaha' },
    'laboratory': { en: 'Laboratory', so: 'Shaybaarka' },
    'laboratory_full': { en: 'Laboratory Department', so: 'Qaybta Shaybaarka Guud' },
    'emergency': { en: 'Emergency (E.R)', so: 'Gurmadka Degdegga (E.R)' },
    'emergency_full': { en: 'Emergency Department (E.R)', so: 'Qaybta Gurmadka Degdegga ah (E.R)' },
    'inpatient': { en: 'Inpatient (IPD)', so: 'Bukaanka Jiifka (IPD)' },
    'inpatient_full': { en: 'Inpatient Department (IPD)', so: 'Qaybta Bukaanka Jiifka (IPD)' },
    'reception': { en: 'Reception (OPD)', so: 'Qaabilaadda (OPD)' },
    'reception_full': { en: 'Reception & Outpatient (OPD)', so: 'Qaybta Qaabilaadda & Bukaansocodka' },
    'lab_operations': { en: 'Lab Operations & Handover', so: 'Hawlgallada Shaybaarka & Wareejinta' },
    'quality_control': { en: 'Quality Control & Calibration', so: 'Xakamaynta Tayada & Cabirka' },
    'inventory_supplies': { en: 'Inventory & Supplies Receipt', so: 'Diiwaanka Qalabka & Alaabta La Helay' },
    'incomplete_tests': { en: 'Incomplete / Pending Tests', so: 'Baaritaannada Qabyada ah' },
    'equipment_troubleshooting': { en: 'Equipment Troubleshooting', so: 'Xallinta Ciladaha Qalabka' },
    'standard_absorption': { en: 'Standard Absorption (OD)', so: 'Nuugista Heerka Cabirka' },
    'measured_absorption': { en: 'Measured Absorption', so: 'Nuugista La Qiyaasay' },
    'calibrated_by': { en: 'Calibrated By', so: 'Qofka Cabiray' },
    'goods_received': { en: 'Goods & Supplies Received', so: 'Alaabta La Qabtay' },
    'supplier_representative': { en: 'Supplier Representative', so: 'Wakiilka Shirkadda' },
    'expiry_date': { en: 'Expiry Date', so: 'Taariikhda Dhicitaanka' },

    // Lab sub-pages
    'daily_report': { en: 'Daily Report', so: 'Warbixinta Maalinta' },
    'department_workspace': { en: 'Department Operational Workspace', so: 'Goobta Shaqada ee Qaybta' },
    'today_shift_status': { en: "Today's Shift Report Status", so: 'Xaaladda Warbixinta Shift-ka ee Maanta' },
    'not_started': { en: 'Not Started / Pending', so: 'Lama Bilaabin / Waa La Sugayaa' },
    'recent_reports': { en: 'Recent Submissions History', so: 'Taariikhda Warbixinnadii Dhowaa' },
    'view_all_submissions': { en: 'View All Submissions', so: 'Fiiri Dhammaan Warbixinnada' },

    // KPIs & Command Metrics
    'kpi_rx_fill_rate': { en: 'Prescription Fill Rate', so: 'Heerka Bixinta Daawada' },
    'kpi_bor': { en: 'Bed Occupancy (BOR)', so: 'Buuxsanaanta Sariiraha (BOR)' },
    'kpi_er_conv': { en: 'ER-to-IPD Conversion', so: 'U Beddelka E.R ee Jiifka' },
    'kpi_lab_density': { en: 'Lab Test Density', so: 'Cufnaanta Baaritaannada Shaybaarka' },
    'kpi_footfall': { en: 'Total Hospital Footfall', so: 'Wadarta Dadka Soo Galay' },
    'kpi_debt_exp': { en: 'Net Debt Exposure', so: 'Deynta & Lacag Celinta Nadiifka ah' },
    'kpi_stockout': { en: 'Stockout Losses', so: 'Bukaanka Dawo La\'aanta u Noqday' },
    'kpi_alos': { en: 'Avg Length of Stay (ALOS)', so: 'Celceliska Maalmaha Jiifka' },
    'kpi_c_section': { en: 'C-Section Rate', so: 'Heerka Qalliinka Umusha' },
    'kpi_in_er_mortality': { en: 'In-ER Mortality Rate', so: 'Heerka Dhimashada E.R' },
    'target': { en: 'Target', so: 'Bartilmaameedka' },
    'inside_rx': { en: 'Inside Hospital Prescriptions', so: 'Daawada Gudaha Isbitaalka' },
    'outside_rx': { en: 'Outside Prescriptions', so: 'Daawada Dibadda' },
    'occupied_beds': { en: 'Occupied Beds', so: 'Sariiraha Jiifka ee Buuxa' },
    'total_beds': { en: 'Bed Capacity', so: 'Wadarta Sariiraha' },
    'acute_cases': { en: 'Acute ER Cases', so: 'Xaaladaha Degdegga ah' },
    'admitted_cases': { en: 'Admitted to Inpatient', so: 'Loo Dhigay Jiifka' },
    'opd_consults': { en: 'Doctor Consultations', so: 'La Kulanka Dhakhaatiirta' },
    'debt_taken': { en: 'Debt Extended', so: 'Deynta La Bixiyay' },
    'debt_recovered': { en: 'Debt Recovered', so: 'Deynta La Soo Celiyay' },

    // 4-Point Cross Audit Station
    'audit_title': { en: '4-Point Cross-Departmental Audit Engine', so: 'Mishiinka 4-ta Qodob ee Baaritaanka Qaybaha' },
    'audit_subtitle': { en: 'Automated Triangulation & Discrepancy Detection Across Hospital Departments', so: 'Isku-Heliinka & Baaritaanka Is-Dhaafsiga Xogta ee Qaybaha Isbitaalka' },
    'audit_inpatient': { en: '1. Inpatient Bed Census Balance', so: '1. Isku-Dheellitirka Sariiraha Jiifka' },
    'audit_inpatient_desc': { en: 'Admissions + Midnight = Discharges + Deaths + Today Census', so: 'Soo Galeen + Habeenkii = Baxeen + Dhinteen + Tirada Maanta' },
    'audit_er_ipd': { en: '2. E.R-to-IPD Admission Reconciliation', so: '2. Isku-Heliinka Bukaanka E.R u Gudbay Jiifka' },
    'audit_er_ipd_desc': { en: 'E.R Dispositions to Inpatient == Inpatient Direct ER Admissions', so: 'Bukaanka E.R u diray Jiifka == Kuwa Jiifku ka qoray E.R' },
    'audit_doctor_rx': { en: '3. Doctor Consultation-to-Pharmacy Capture', so: '3. Qabashada Daawada ee Dhakhaatiirta OPD' },
    'audit_doctor_rx_desc': { en: 'OPD Doctor Prescriptions vs Pharmacy Served + Unserved', so: 'Daawada Dhakhaatiirtu qoreen vs Kuwa Farmashiyuhu bixiyay' },
    'audit_stockout': { en: '4. Stockout Persistence Tracker', so: '4. Dabagalka Dawooyinka Go\'an' },
    'audit_stockout_desc': { en: 'Unserved Stockouts flagged across consecutive reporting shifts', so: 'Dawooyinka go\'ay ee soo noqnoqday shaqooyinkii isxigay' },
    'inspect_audit_details': { en: 'Inspect Audit Formulas & Proof Logs', so: 'Fiiri Qaaciidooyinka & Diiwaanka Baaritaanka' },
    'hide_audit_details': { en: 'Hide Audit Formulas', so: 'Qari Faahfaahinta Baaritaanka' },
    'status_verified': { en: 'Verified (100% Balanced)', so: 'Waa Saxan (100% Isku Dheellitiran)' },
    'status_warning': { en: 'Discrepancy Detected', so: 'Kala Duwanaansho Ayaa Jira' },
    'status_missing_data': { en: 'Awaiting Dept Submissions', so: 'Waxaa La Sugayaa Warbixinnada' },

    // Operational Grids & Actions
    'dept_command_title': { en: 'Department Operational Stations', so: 'Xarumaha Shaqada ee Qaybaha Isbitaalka' },
    'dept_command_sub': { en: 'Daily reporting compliance, duty leads, and official sheet access', so: 'Gudbinta xogta maalinlaha ah, madaxda shift-ka, iyo waraaqaha rasmiga ah' },
    'mode_paper': { en: 'Mode A: Paper-Replica', so: 'Habka A: Waraaqda Rasmiga ah' },
    'mode_guided': { en: 'Mode B: Guided Form', so: 'Habka B: Foomka Fudud' },
    'save_draft': { en: 'Save Draft', so: 'Keydi Qabyo' },
    'submit_audit': { en: 'Submit for Audit', so: 'U Gudbi Baaritaan' },
    'audit_approve': { en: 'Audit & Approve', so: 'Baar oo Ansixi' },
    'return_revision': { en: 'Return for Revision', so: 'Dib ugu Celi Sixid' },
    'print_view': { en: 'A4 Print View', so: 'Daabaco A4 / PDF' },
    'view_sheet': { en: 'View Official Sheet', so: 'Fiiri Waraaqda Rasmiga ah' },
    'edit_form': { en: 'Edit / Fill Data', so: 'Geli / Wax ka Beddel Xogta' },
    'duty_lead': { en: 'Duty Lead', so: 'Madaxa Shift-ka' },
    'status_approved': { en: 'Approved & Audited', so: 'La Ansixiyay & La Baaray' },
    'status_submitted': { en: 'Submitted (Pending Audit)', so: 'La Gudbiyay (Sugaya Baaritaan)' },
    'status_draft': { en: 'Draft in Progress', so: 'Qabyo Lagu Jiro' },
    'status_returned': { en: 'Returned for Revision', so: 'Dib Loo Celiyay' },
    'status_missing': { en: 'Missing Submission', so: 'Lama Hayo Warbixinta' },

    // Department Form Sections (Strictly English or Somali)
    'phm_sec_a': { en: 'SECTION A — DAILY PRESCRIPTION VOLUME & DISPENSING SUMMARY', so: 'QAYBTA A — WADARTA DAWADA LA BIXIYAY' },
    'phm_sec_b': { en: 'SECTION B — PRESCRIBING DOCTOR PERFORMANCE & RECONCILIATION', so: 'QAYBTA B — XOGTA DHAKHAATIIRTA QORTAY DAAWADA' },
    'phm_sec_c': { en: 'SECTION C — ROOT CAUSES FOR UNSERVED PRESCRIPTIONS', so: 'QAYBTA C — SABABAHA DAWADA AAN LOO BIXIN' },
    'phm_sec_d': { en: 'SECTION D — FINANCIAL OPERATIONS, REFUNDS & DEBT EXPOSURE', so: 'QAYBTA D — XISAABAADKA, LACAG-CELINTA & DEYMAHA' },
    'phm_sec_e': { en: 'SECTION E — STOCKOUTS & NEAR-EXPIRY LOGISTICS ALERTS', so: 'QAYBTA E — DIGAANTA DAWOOYINKA GO\'AY & KUWA DHACAYA' },
    
    'cause_price': { en: 'Price / Financial Barrier', so: 'Kharashka / Qiimaha Sare' },
    'cause_stockout': { en: 'Complete Stockout', so: 'Daawo La\'aan Guud' },
    'cause_brand': { en: 'Specific Brand Unavailable', so: 'Nooca Gaarka ah Oo Maqan' },
    'cause_did_not_present': { en: 'Patient Did Not Present', so: 'Bukaanku Farmashiyaha Ma Iman' },
    'cause_unknown': { en: 'Unknown / Unspecified Reason', so: 'Sabab Aan La Aqoon' },
    'cause_other': { en: 'Other Operational Reason', so: 'Sabab Kale Oo Jirta' },

    'ipd_sec_a': { en: 'SECTION A — STRICT SELF-BALANCING DAILY BED CENSUS RECONCILIATION', so: 'QAYBTA A — DIIWAANKA IS-LE\'EG EE BUKAANKA JIIFKA' },
    'ipd_sec_b': { en: 'SECTION B — WARD-BY-WARD BED OCCUPANCY & UTILIZATION', so: 'QAYBTA B — BUUXSANAANTA SARIIRAHA EE QAYBAHA' },
    'ipd_sec_c': { en: 'SECTION C — MATERNITY, LABOR & NEWBORN DELIVERY REGISTER', so: 'QAYBTA C — UBAD DHALASHADA & UMULISADA' },

    'er_sec_a1': { en: 'SECTION A1 — EMERGENCY ARRIVALS BY ACUITY & CLINICAL STREAM', so: 'QAYBTA A1 — BUKAANKA GURMADKA EE SOO GALAY' },
    'er_sec_a2': { en: 'SECTION A2 — EMERGENCY DISPOSITIONS & PATIENT DESTINATIONS', so: 'QAYBTA A2 — NATIIJADA & MEELAHA LOO DIRAY BUKAANKA' },
    'er_sec_b': { en: 'SECTION B — DEMOGRAPHICS & PATIENT AGE BRACKETS', so: 'QAYBTA B — DA\'DA & JINSIGA BUKAANKA' },
    'er_sec_e': { en: 'SECTION E — IN-ER MORTALITY & DOA REGISTER', so: 'QAYBTA E — DIIWAANKA DHIMASHADA GURMADKA' },

    'rec_sec_a': { en: 'SECTION A — PATIENT REGISTRATION & FOOTFALL THROUGHPUT', so: 'QAYBTA A — WADARTA DIIWAANGALINTA & BUKAANSOUCODKA' },
    'rec_sec_b': { en: 'SECTION B — DOCTOR CONSULTATION APPOINTMENTS & OUTCOMES', so: 'QAYBTA B — WADA-TASHIGA DAKHAATIIRTA (OPD CLINICS)' },
    'rec_sec_c': { en: 'SECTION C — OPERATIONAL BOTTLENECKS & DELAY INCIDENTS', so: 'QAYBTA C — CILADAHA & DIB-U-DHACA SHAQADA' },

    // Charts & Analytics
    'chart_patient_volume': { en: '7-Day Patient Footfall & Admission Velocity', so: 'Dhaqdhaqaaqa Bukaanka & Jiifka 7-dii Maalmood' },
    'chart_quality_metrics': { en: 'Bed Occupancy (BOR) vs Pharmacy Fill Rate Target', so: 'Buuxsanaanta Sariiraha vs Bixinta Daawada' },
    'ai_insights_title': { en: 'Executive AI Daily Briefing & Policy Advisory', so: 'Warbixinta Maalinlaha ah ee DeepSeek AI & Talooyinka Siyaasadda' },
};

// Global App Store for Localization, Theme, and Focus Mode
Alpine.store('app', {
    // Strictly either 'en' or 'so' - no dual translations
    locale: (localStorage.getItem('hospital_locale') === 'so') ? 'so' : 'en',
    theme: localStorage.getItem('hospital_theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'),
    focusMode: false,
    mobileMenuOpen: false,

    init() {
        this.applyTheme();
    },

    setLocale(loc) {
        this.locale = loc === 'so' ? 'so' : 'en';
        localStorage.setItem('hospital_locale', this.locale);
    },

    t(key, fallback = '') {
        const item = translations[key];
        if (!item) return fallback || key;
        if (this.locale === 'so') return item.so || item.en || fallback;
        return item.en || fallback;
    },

    toggleTheme() {
        this.theme = this.theme === 'light' ? 'dark' : 'light';
        localStorage.setItem('hospital_theme', this.theme);
        this.applyTheme();
    },

    applyTheme() {
        if (this.theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    },

    toggleFocusMode() {
        this.focusMode = !this.focusMode;
    },

    toggleMobileMenu() {
        this.mobileMenuOpen = !this.mobileMenuOpen;
    },
});

// Translation helper callable anywhere
window.t = function(key, fallback) {
    return Alpine.store('app').t(key, fallback);
};

// Initialize Lucide icons on DOM update
window.refreshIcons = function() {
    createIcons({ icons });
};

// Immediate Theme Application before Alpine starts
const initialTheme = localStorage.getItem('hospital_theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
if (initialTheme === 'dark') {
    document.documentElement.classList.add('dark');
} else {
    document.documentElement.classList.remove('dark');
}

document.addEventListener('DOMContentLoaded', () => {
    Alpine.store('app').applyTheme();
    Alpine.start();
    createIcons({ icons });
});
