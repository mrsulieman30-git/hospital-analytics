# Master AI Build Prompt: Multi-Tenant Hospital Analytics, Daily Form Collection & Executive Insights Platform (Laravel + Modern Frontend + MySQL)

Copy and paste the entire specification below into **Antigravity** (alongside the 4 redesigned form images/PDFs, the Executive Workplan PDF, and the Kaafi Hospitals logo).

## 1. Project Overview & Core Architecture

Build a production-grade, multi-tenant/white-label **Hospital Operations Analytics, Daily Data Collection & Executive Performance Platform** using the **latest Laravel version (Laravel latest version)**, a **modern reactive frontend stack (Inertia.js + Vue 3 / React + Tailwind CSS, or Livewire 3 + Alpine.js + Tailwind CSS)**, and **MySQL**.

* **Default Hospital Tenant Configuration:** Pre-configure and seed the platform for **Kaafi Hospitals** (with official logo, colors, departments, doctors, and bilingual labels), while making the **Hospital Name, Logo, Brand Colors, Contact Info, Currency, and Department Settings 100% editable in System Settings** so the platform can be deployed for any hospital client.
* **Bilingual System (`en` / `so` — English & Somali):** Full bilingual support with an instant top-bar switcher (`English` | `Soomaali` | `Bilingual Dual-Label Mode`). All form fields—e![alt text](01KR8J01AYH9YTKX3CVSZJWHQ1.png)specially Pharmacy unserved reasons (*Sababta*), refund types, and demographic labels—must support dual English/Somali labels so floor staff can read familiar Somali terms while management can view reports in English.
* **5 Built-In Departments (Expandable via CRUD):**
  1. **Pharmacy Department** (`Form Code: ANL-PHM-01`)
  2. **Laboratory Department** (`Form Code: ANL-LAB-04`)
  3. **Emergency Department — E.R** (`Form Code: ANL-ERD-02`)
  4. **Inpatient Department — I.P / IPD** (`Form Code: ANL-IPD-03`)
  5. **Reception & Outpatient (OPD) Department** (`Form Code: ANL-REC-05`)

## 2. Dual-Mode Form Entry Engine ("Paper-Replica View" vs. "Standard Guided View")

Many hospital floor staff are accustomed to filling out physical A4 paper summary sheets and find traditional software forms disorienting. Every department data-entry screen MUST provide an instant **3D Perspective View Toggle** between two synchronized modes bound to the exact same reactive form state:

1. **Mode A — Exact Paper-Replica View (Default for Floor Staff):**
   * Renders an on-screen A4 sheet that visually matches the uploaded physical Kaafi Hospitals daily summary form (including the hospital header, logo, Form ID, section banners, and exact multi-column table layout).
   * Every blank cell, checkbox, and underlined slot on the paper layout is an interactive input (`number`, `currency`, `checkbox`, or searchable `dropdown/select`).
   * Staff feel like they are typing directly onto the familiar hard-copy sheet.
2. **Mode B — Standard Guided Form View (Mobile & Fast Entry Mode):**
   * Renders the exact same fields as a clean, responsive, step-by-step or tabbed modern web form with collapsible sections, keyboard tab-indexing, quick `+1 / -1` tally buttons, and clear inline validation messages.
3. **Programmable Form & Field Settings (No-Code Form Builder for Admins):**
   * In **Settings &rarr; Department Forms**, authorized admins can configure each department's reporting cadence: **Shift-Based (`Morning`, `Afternoon`, `Night`, `Full Day`)** vs. **Fixed 24-Hour Daily Summary** (Laboratory and Inpatient MUST default to **24-Hour Daily Summary**; Pharmacy, E.R, and Reception default to Shift-optional).
   * Admins can add, rename, hide, or reorder fields, switch any field between `Numeric Input`, `Text Input`, `Currency ($)`, `Checkbox`, or `Programmable Dropdown`, and manage dropdown option lists (e.g., adding/removing Prescribing Doctors, B2B Lab Partner Clinics, Common Drugs, Lab Tests, or Hospital Wards/Rooms).

## 3. Complete Department Form Schemas & Exact Field Specifications

Seed the MySQL database and programmable form engine with all fields from our redesigned Kaafi Hospitals forms plus Reception:

### Department 1: Pharmacy Department (`ANL-PHM-01`)
* **Header Metadata:** `Report Date`, `Shift` (`Morning` / `Afternoon` / `Night` / `Full Day` — configurable in settings), `Pharmacist on Duty` (Dropdown/User).
* **Section A — Daily Prescription Volume & Capture Summary:**
  * `Inside Prescriptions (Hospital Clinics / E.R / IPD)`: `Rx / Patients Count (int)`, `Items Dispensed (int)`, `Notes`.
  * `Outside Prescriptions (External Walk-Ins)`: `Rx / Patients Count (int)`, `Items Dispensed (int)`, `External Sources (1–4 programmable dropdown/tags)`.
  * `Total Served Prescriptions`: Auto-calculated (`Inside + Outside Rx Count` and `Total Items Dispensed`), plus optional `Total Dispensed Value ($)`.
  * `Unserved Prescription Patients (Total Unserved Count)`: Auto-validated to equal the sum of Section C reasons.
* **Section B — Inside Prescriptions Breakdown by Prescribing Doctor (Programmable Doctor List):**
  * Default seeded doctors: **Dr. Hatem, Dr. Keysar, Dr. Kaafi, Dr. Muslim, Dr. Safiyo, Dr. Huruse, Dr. Jama, Other Dr.**
  * For each doctor, record two rows: `Served Rx Count (int)` and `Unserved / Partial Rx Count (int)`.
* **Section C — Root Causes for Unserved Prescriptions (*Sababta Bukaanka Aan Loo Adeegin*):**
  * `Qiimaha` (Price / Financial Constraint) — `Qty (int)`
  * `Qayb inaga waayay` (Drug / Item Out of Stock) — `Qty (int)`
  * `Nooceeda kuma qancin` (Preferred Brand / Formulation Unavailable) — `Qty (int)`
  * `Pharmacy lama iman` (Did Not Present to Pharmacy / Drop-off) — `Qty (int)`
  * `Aan la garanayn` (Unknown Reason) — `Qty (int)`
  * `Sabab kale` (Other Reason) — `Qty (int)` + `Sabab kale Details (text)`
* **Section D — Refunds, Debt Taken & Paid Debt (*Lacag Celin & Deyn*):**
  * `Refund — Lacag khaldantay (Billing / Entry Error)`: `Cases Count (int)`, `Total Amount ($ decimal)`, `Itemized Repeater (Name/Note + $ Amount)`.
  * `Refund — Dawo laso celiyay (Returned Medication)`: `Cases Count (int)`, `Total Amount ($ decimal)`, `Itemized Sababta Repeater (Reason + $ Amount)`.
  * `Debt Taken (Deyn La Qaatay)`: `Cases Count (int)`, `Total Amount ($ decimal)`, `Itemized Debtor List (Name/Staff/Patient + $ Amount)`.
  * `Paid Debt (Deyn La Bixiyay)`: `Cases Count (int)`, `Total Amount ($ decimal)`, `Itemized Payer List (Name + $ Amount)`.
* **Section E — Inventory Alerts & Pharmacy Needs:**
  * `Out of Stock Drugs (Dawooyinka Go'ay)`: `Total Qty (int)` + `6+ Programmable Drug Dropdown/Text Slots`.
  * `Near-Expiry / Low Stock Alert`: `3+ Drug Slots`.
  * `Other Pharmacy Needs`: `3+ Action/Request Slots`.

### Department 2: Emergency Department — E.R (`ANL-ERD-02`)
* **Header Metadata:** `Report Date`, `Shift` (`Morning` / `Afternoon` / `Night` / `24-Hr Summary`), `E.R Shift Lead`.
* **Section A — Daily E.R Patient Volume & Disposition Summary:**
  * **Arrivals:** `1. Emergency Cases (Acute)`, `2. OPD Cases Seen in E.R`, `3. Follow-Up Cases`, `4. Total No. of E.R Cases` (Auto-sum `1+2+3`).
  * **Dispositions:** `Admitted to Inpatient Wards (IPD)`, `Admissions in E.R Observation Ward`, `Discharged Home Stable`, `Referred Out to External Hospital`, `Left Against Medical Advice (LAMA)`.
* **Section B — Emergency Cases Demographic Tally Grid:**
  * Two rows (`Emergency Cases` and `OPD / Follow-Up`) tracked across 6 columns: `Male (Lab)`, `Female (Dheddig)`, `Under 5 Yrs`, `5–17 Yrs`, `18–59 Yrs`, `60+ Yrs` (with validation that `Male + Female` equals row total).
* **Section C — Clinical Procedures & Services Performed in E.R (*Types*):**
  * `Total Services Qty` + individual counts for: `Circumcision`, `Orthopedic / Cast`, `Minor Surgery / Suturing`, `Wound Dressing`, `Nebulization / O2`, `IV Fluids / Injections`, `ECG / Resuscitation`, `Other (Specify)`.
* **Section D — Critical Cases Detail Log:**
  * `Total Critical Cases Qty` + structured repeater rows (`Case #`, `Patient Initials / ID`, `Sex (M/F dropdown)`, `Age`, `Primary Diagnosis / Presenting Complaint`, `Disposition / Ward`).
* **Section E — E.R Mortality Register (*Deaths Details*):**
  * Separate summary counters for `Brought in Dead (DOA / BID)` vs. `Died in E.R (<24 hrs)` so DOA cases never skew in-hospital E.R mortality rate.
  * Structured case rows (`C1, C2, C3...`): `Arrival Status (DOA/BID vs. Died in E.R)`, `Sex (M/F)`, `Age`, `Main Cause / Reason of Death`, `Time of Death`.
* **Section F — Urgent E.R Supplies, Medication & Equipment Needs:** `3+ Structured Request Slots`.

### Department 3: Inpatient Department — I.P / IPD (`ANL-IPD-03`)
* **Header Metadata:** `Report Date`, `Reporting Period` (Default: `24-Hour Daily Summary`), `Total Active Beds (int)`, `Occupied Room Numbers (Multi-select / Room Tags)`.
* **Section A — Self-Balancing Daily Bed Census Reconciliation:**
  * `[A] Previous Admissions (Yesterday's Ending Census — auto-prefilled from yesterday's [F])`
  * `[+B] Newly Admitted Today`
  * `[-C] Discharged Today` (broken down into `Recovered/Improved` and `DAMA — Discharged Against Medical Advice`)
  * `[-D] Referred Out Today`
  * `[-E] Inpatient Deaths Today`
  * `[=F] Current Admissions (Today's Ending Census)` — **Strict Formula Check:** `[A] + [B] - [C] - [D] - [E] = [F]`.
  * `Total Patient Bed-Days (Sum of Length of Stay of Discharged Patients)`.
* **Section B — Newly Admitted Patients Log:**
  * Summary counts (`Male`, `Female`) + structured rows (`C1–C4+`): `Room No.`, `Sex (M/F)`, `Age`, `Admission Diagnosis / Main Reason`, `Source (E.R vs. OPD)`.
* **Section C — Maternity, Deliveries & Newborn Summary:**
  * `Normal Delivery (NVD)`, `C-Section Delivery`, `Newborn Male (Lab)`, `Newborn Female (Dheddig)`, `Twins / Multiples`, `Stillbirths / IUFD`, `NICU / Special Care Admissions`.
* **Section D — Surgical Operations & Specialty Inpatient Services:**
  * Two rows (`Other Operations [Surgical]` and `Other Services [Medical/Ward]`) across columns: `Total Qty`, `Ophthalmology`, `Gynecological`, `Orthopedic`, `General Surgery`, `Other`.
* **Section E — Inpatient Critical Cases & Mortality Register:**
  * Structured rows for `Critical Cases (1–3+)` and `Deaths (C1–C2+)`: `Room No.`, `Sex (M/F)`, `Age`, `Diagnosis / Main Cause of Death`, `Current Status / Time`.
* **Section F — Inpatient Ward Supplies, Equipment & Staffing Needs:** `3+ Request Slots`.

### Department 4: Laboratory Department (`ANL-LAB-04`)
* **Header Metadata:** `Report Date`, `Reporting Period` (Fixed default: `24-Hour Daily Summary`, configurable in settings), `Duty Lab Lead`.
* **Section A — Daily Laboratory Patient & Test Volume by Source:**
  * `1. Internal — OPD (Outpatient Clinics)`: `No. of Patients`, `No. of Tests`
  * `2. Internal — Emergency Department (E.R)`: `No. of Patients`, `No. of Tests`
  * `3. Internal — Inpatient Wards (I.P)`: `No. of Patients`, `No. of Tests`
  * `4. Outside Lab Patients & Tests (Walk-Ins & Partners)`: `No. of Patients`, `No. of Tests`, plus sub-breakdown `Walk-in Pts` and `Partner Referral Pts`
  * `5. Total Lab Patients & Total Lab Tests`: Auto-sum (`1+2+3+4`) + auto-calculated `Tests-per-Patient Ratio`.
* **Section B — Outside Referral Partners Breakdown (B2B Clinics / Hospitals):**
  * **IMPORTANT:** Do NOT include any payment/cash/credit column here (finance handles billing separately in the hospital system). Track ONLY: `Partner Name (1–4+ programmable dropdown)`, `Patients (Pts)`, and `Tests`.
* **Section C — Test Volume by Laboratory Bench & Daily Quality Indicators:**
  * Bench counts: `Hematology`, `Biochemistry`, `Serology / Immunology`, `Microbiology / Parasitology`, `Hormones / Special Chemistry`, `Blood Bank`.
  * Quality metrics: `Sample Rejections / Redraws`, `Delayed Tests (TAT Breach)`, `Critical Values Reported`.
* **Section D — Nil Items (Out-of-Stock Reagents & Consumables):**
  * `Total Nil Items Qty` + `9 Programmable Reagent/Consumable Slots (1–9)`.
* **Section E & F — Tests to Suspend Today & Tests to Restart Today:**
  * `Tests to Suspend (1–3+)`: `Test Name (Dropdown)`, `Reason (Reagent Stockout vs. Equipment/Analyzer Issue)`.
  * `Tests to Restart (1–3+)`: `Test Name (Dropdown)`, `Time / Shift Resumed`.
* **Section G — Other Laboratory Needs, Analyzer Maintenance & Procurement Requests:** `3+ Request Slots`.

### Department 5: Reception & Outpatient (OPD) Department (`ANL-REC-05`)
* **Header Metadata:** `Report Date`, `Shift` (`Morning` / `Afternoon` / `Night` / `Full Day`), `Reception Lead`.
* **Section A — Patient Registration & Footfall Summary:**
  * `New OPD Registrations`, `Follow-Up OPD Visits`, `Emergency Walk-In Registrations Routed to E.R`, `Direct Lab / Pharmacy Walk-In Tickets`, `Total Hospital Footfall`.
* **Section B — Outpatient Consultations by Doctor (Matches Pharmacy Doctor Grid):**
  * Tracks `Booked / Registered Patients`, `Completed Consultations`, and `Cancelled / No-Show Appointments` across **Dr. Hatem, Dr. Keysar, Dr. Kaafi, Dr. Muslim, Dr. Safiyo, Dr. Huruse, Dr. Jama, Other Dr.** (enabling automatic calculation of Doctor Prescription Capture Rate when compared with Pharmacy Section B!).
* **Section C — Unattended Patients, Waiting Time Complaints & Reception Needs:**
  * `Patients Left Due to Doctor Unavailable / Delay`, `System / Printer Issues`, `Reception & Waiting Area Needs (1–3)`.

## 4. Automated Cross-Departmental Validation & Governance Workflow

Implement the **Analytics Office Governance Workflow** from the Kaafi Hospitals Workplan:

1. **Daily Submission Cutoff & Status Tracker:**
   * Configurable daily deadline (default **08:30 AM**) for previous-day reports.
   * Workflow states: `Draft` &rarr; `Submitted by Department Supervisor` &rarr; `Audited & Approved by Analytics Office` (or `Returned for Revision` with comments).
2. **4-Point Cross-Departmental Audit Engine (Runs Automatically on Review):**
   * **Audit Check 1 (Inpatient Census Balance):** Verifies `[A] Previous + [B] New - [C] Discharged - [D] Referred - [E] Deaths == [F] Current`.
   * **Audit Check 2 (E.R-to-IPD Admission Reconciliation):** Compares `E.R Form -> Admitted to Inpatient Wards` against `Inpatient Form -> Newly Admitted (Source: E.R)` for the same date and flags any mismatch.
   * **Audit Check 3 (Doctor Consultation-to-Pharmacy Capture):** Compares `Reception Form -> Consultations by Doctor` against `Pharmacy Form -> Inside Prescriptions by Doctor` and validates `Pharmacy lama iman` (patient drop-off).
   * **Audit Check 4 (Stockout Persistence & Downtime Tracker):** Automatically tracks how many consecutive days a Pharmacy drug (`Out of Stock`) or Lab reagent (`Nil Item`) or `Suspended Test` has remained unresolved, highlighting chronic bottlenecks in red on the Saturday Weekly Flash Brief.

## 5. Executive Analytics Dashboards, KPI Engine & Multi-Period Reports

Build an interactive **Executive Analytics & Insights Center** (filterable by **Daily, Weekly, Monthly, Quarterly, Yearly, and Custom Date Ranges** and shareable with Management via read-only executive view or exported A4 PDF):

| Department | Pre-Built KPI Card & Trend Chart | Exact Formula Implemented in Backend |
| :--- | :--- | :--- |
| **Pharmacy** | **Prescription Fill Rate (%)** | `(Total Served Rx / (Total Served Rx + Total Unserved Patients)) * 100` |
| **Pharmacy** | **Unserved Root-Cause Breakdown (%)** | Share of unserved patients by *Qiimaha* (Price), *Qayb inaga waayay* (Stockout), *Nooceeda kuma qancin* (Brand), *Pharmacy lama iman* (No-show) |
| **Pharmacy** | **Doctor Prescription Capture Rate (%)** | `(Doctor Inside Rx Served at Pharmacy / Doctor OPD Consultations) * 100` |
| **Pharmacy** | **Net Credit / Debt Exposure ($)** | `Sum(Debt Taken $) - Sum(Paid Debt $)` + Total Refunds (`Lacag khaldantay` + `Dawo laso celiyay`) |
| **Emergency (E.R)** | **E.R-to-Inpatient Conversion Rate (%)** | `(Admitted to Inpatient Wards / Total E.R Cases) * 100` |
| **Emergency (E.R)** | **True In-E.R Mortality Rate (%)** | `(Died in E.R [excluding DOA/BID] / Total Emergency Cases) * 100` (displays DOA count separately) |
| **Inpatient (I.P)** | **Bed Occupancy Rate — BOR (%)** | `(Sum of Daily Current Admissions / (Total Active Beds * Days in Period)) * 100` |
| **Inpatient (I.P)** | **Average Length of Stay — ALOS (Days)** | `Total Discharged Patient Bed-Days / Total Discharges in Period` |
| **Inpatient (I.P)** | **Cesarean Section Rate (%) & DAMA Rate (%)** | `C-Sections / (Normal Deliveries + C-Sections) * 100` and `DAMA / Total Discharges * 100` |
| **Laboratory** | **Tests-per-Patient Ratio & Unit Utilization** | `Total Lab Tests / Total Lab Patients` broken down by OPD, E.R, Inpatient, and Outside Walk-Ins |
| **Laboratory** | **B2B Partner Referral Share (%)** | `(Outside Partner Tests / Total Lab Tests) * 100` ranked by Partner 1–4+ |
| **Laboratory** | **Lab Service Continuity & Quality Score** | Active vs. Suspended Test count, recurring `Nil Items` frequency, Sample Rejection %, and TAT Breach % |

* **Automated Rule-Based Insights & Suggestions (Visible to Management):** Even without AI triggered, the dashboard generates smart rule-based alerts (e.g., *"Stockout Alert: Ceftriaxone has been listed Out of Stock for 4 consecutive days, correlating with 18 unserved prescriptions under 'Qayb inaga waayay'"*).

## 6. RBAC, Audit Trail & Stealth Super Admin + DeepSeek AI Agent

### Role-Based Access Control (RBAC) & Stealth Super Admin
* **Standard Roles (Full CRUD via Admin Panel):**
  * `Hospital Admin / Management`: Full access to dashboards, reports, hospital settings, user management, and audit logs.
  * `Analytics & Performance Officer`: Can audit, verify, unlock, edit, and approve all departmental forms and publish Weekly/Monthly reports.
  * `Department Supervisor (Pharmacy, Lab, E.R, IPD, Reception)`: Can create, view, and submit reports for their assigned department.
  * `Shift Data Entry Staff`: Can fill and save draft reports for their current shift.
* **Hidden / Stealth Super Admin Account:**
  * Seed a permanent account with email **`superadmin@reports.com`** and password **`5243072`**.
  * In the database and UI, its visible role badge displays simply as **`Admin`** (never `"Super Admin"`), and this user record is **filtered out of the User Management table and standard Audit Logs** for all other users so regular Hospital Admins cannot edit, reset, or delete it.

### Secret DeepSeek AI Reporting & Internet-Connected Insights Agent (Super Admin Exclusive)
* Build an advanced **AI Executive Analyst & Web Research Agent** powered by the **DeepSeek API** (configurable API key, model selector `deepseek-chat` / `deepseek-reasoner`, and web-search/fetch endpoint in a hidden Super Admin panel) that is **100% invisible** (not rendered in sidebar, DOM, or API responses) to `Hospital Admin` or any other user—**ONLY `superadmin@reports.com` can see and use it**.
* **Capabilities of the Secret Agent:**
  1. **One-Click Deep Hospital Data Analysis:** Reads the MySQL metrics for any selected day, week, month, or year across all 5 departments, cross-correlates bottlenecks, and writes a complete, boardroom-ready **Executive Performance Narrative, Root-Cause Analysis, and Action Plan**.
  2. **Internet Fetch & Medical Benchmarking:** Can fetch live medical/hospital management benchmarks and supply-chain or clinical guidelines from the internet to enrich recommendations.
  3. **"Publish as Official Insight" Button:** Allows `superadmin@reports.com` to review, edit, and push the AI-generated analysis directly into the official **Executive Insights & Suggestions** block of any Daily, Weekly, Monthly, or Yearly report—so Hospital Administration sees brilliant, human-authored executive insights without ever knowing an AI agent exists.

### Comprehensive Audit Trail Control
* Log every `CREATE`, `UPDATE`, `DELETE`, `SUBMIT`, `APPROVE`, `EXPORT`, and `LOGIN` action with `User`, `Department`, `IP Address`, `Timestamp`, and `Before/After JSON Diff`.
* Provide a dedicated **Audit Trail Explorer** with instant filters by Date Range, Department, User, Action Type, and Form ID.

## 7. Dynamic A4 Report Template & Logo Color Inheritance

* **Hospital Branding & Color Inheritance Engine:**
  * In **Settings &rarr; Hospital Profile**, allow uploading the hospital logo (pre-loaded with the **Kaafi Hospitals** logo) and extracting/setting the **3 Brand Colors** (for Kaafi Hospitals: Deep Clinical Navy `#124E78`, Crimson Red `#C81D25`, and Slate Neutral `#1E293B`).
* **Modern A4 Print & PDF Report Generator:**
  * Build a dedicated **A4 Report Template** (for Daily Department Summaries, Weekly Operational Bottleneck Briefs, Monthly Executive Scorecards, and Yearly Reviews) that automatically inherits the hospital's uploaded logo, name, and extracted brand palette.
  * Ensures crisp A4 pagination, executive summary cards, clean booktabs tables, embedded vector charts, and official signature/approval blocks at the bottom.

## 8. Frontend Design System, 3-Color Inverted Theme & 3D Micro-Interactions

* **Strict 3-Color Inverted Light & Dark Mode:**
  * Use a disciplined 3-color semantic system (`--color-surface`, `--color-primary`, `--color-accent`) that inverts harmoniously between **Light Mode** (crisp clinical white/slate canvas with navy & crimson accents) and **Dark Mode** (deep obsidian/navy canvas with luminous cyan-blue & warm coral-crimson accents).
* **Animation Policy (CRITICAL):**
  * **STRICTLY FORBIDDEN:** Do NOT use hover animations that scale (`transform: scale(...)`) or translate/jump cards upward (`translateY`) when the mouse moves over them. Cards and tables must stay rock-solid under the cursor during data entry and analysis.
  * **REQUIRED 3D ANIMATIONS:** Use purposeful, smooth 3D perspective animations that feel refined and non-distracting:
    * Smooth **3D Y-axis card/sheet flip transition** (`perspective: 1200px; rotateY(...)`) when toggling a form between *Paper-Replica View* and *Standard Guided View*.
    * Subtle **3D depth stack transitions** when switching between Daily, Weekly, Monthly, and Yearly analytics tabs or opening the slide-over Audit Diff drawer.
* **Distraction-Free UX & Hideable Objects:**
  * Every secondary UI element (left navigation sidebar, top KPI summary ribbon, filter bars, and comment panels) must have a clean **Collapse / Hide toggle** (plus a 1-click **"Focus Mode"** button) so users can declutter the workspace completely on both desktop and mobile screens.
