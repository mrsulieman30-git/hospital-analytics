{{-- =========================================================================
     TAB 1: DAILY DIAGNOSTIC VOLUME & BENCHES
     ========================================================================= --}}
<div class="space-y-6">

    {{-- Section A: Volume by Patient Source --}}
    <div class="hc-card">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-[var(--hc-border)]">
            <div class="flex items-center gap-2">
                <span class="hc-badge hc-badge-purple">SECTION A</span>
                <h3 class="text-sm font-bold tracking-tight text-[var(--hc-text)]">
                    Diagnostic Volume by Patient Source
                </h3>
            </div>
            <div class="text-xs font-mono text-[var(--hc-text-muted)]">
                Ratio: <strong class="text-[var(--hc-text)] font-bold" x-text="testsPerPatientRatio + 'x tests/patient'"></strong>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="hc-table">
                <thead>
                    <tr>
                        <th class="w-1/3">Patient Origin Source</th>
                        <th class="w-1/4 text-center">Patients Sampled</th>
                        <th class="w-1/4 text-center">Tests Conducted</th>
                        <th class="w-1/6 text-right">Avg Tests/Pt</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-semibold">
                            <div class="flex items-center gap-2">
                                <span class="status-dot status-dot-brand"></span>
                                <div>
                                    <span class="block text-[var(--hc-text)]">Outpatient Department (OPD)</span>
                                    <span class="text-[11px] text-[var(--hc-text-muted)] font-normal">General & Specialist Clinics</span>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <input type="number" min="0" x-model.number="opdPts" class="hc-input text-center font-bold text-sm w-28 mx-auto" placeholder="0">
                        </td>
                        <td class="text-center">
                            <input type="number" min="0" x-model.number="opdTests" class="hc-input text-center font-bold text-sm w-28 mx-auto" placeholder="0">
                        </td>
                        <td class="text-right font-mono font-semibold text-xs text-[var(--hc-text-muted)]">
                            <span x-text="opdPts > 0 ? (opdTests / opdPts).toFixed(2) + 'x' : '0.00x'"></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="font-semibold">
                            <div class="flex items-center gap-2">
                                <span class="status-dot status-dot-warning"></span>
                                <div>
                                    <span class="block text-[var(--hc-text)]">Emergency Department (E.R)</span>
                                    <span class="text-[11px] text-[var(--hc-text-muted)] font-normal">STAT Critical Care & Resuscitation</span>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <input type="number" min="0" x-model.number="erPts" class="hc-input text-center font-bold text-sm w-28 mx-auto" placeholder="0">
                        </td>
                        <td class="text-center">
                            <input type="number" min="0" x-model.number="erTests" class="hc-input text-center font-bold text-sm w-28 mx-auto" placeholder="0">
                        </td>
                        <td class="text-right font-mono font-semibold text-xs text-[var(--hc-text-muted)]">
                            <span x-text="erPts > 0 ? (erTests / erPts).toFixed(2) + 'x' : '0.00x'"></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="font-semibold">
                            <div class="flex items-center gap-2">
                                <span class="status-dot status-dot-brand"></span>
                                <div>
                                    <span class="block text-[var(--hc-text)]">Inpatient Wards (IPD)</span>
                                    <span class="text-[11px] text-[var(--hc-text-muted)] font-normal">Medical, Surgical, Pediatric & Maternity</span>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <input type="number" min="0" x-model.number="ipdPts" class="hc-input text-center font-bold text-sm w-28 mx-auto" placeholder="0">
                        </td>
                        <td class="text-center">
                            <input type="number" min="0" x-model.number="ipdTests" class="hc-input text-center font-bold text-sm w-28 mx-auto" placeholder="0">
                        </td>
                        <td class="text-right font-mono font-semibold text-xs text-[var(--hc-text-muted)]">
                            <span x-text="ipdPts > 0 ? (ipdTests / ipdPts).toFixed(2) + 'x' : '0.00x'"></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="font-semibold">
                            <div class="flex items-center gap-2">
                                <span class="status-dot status-dot-success"></span>
                                <div>
                                    <span class="block text-[var(--hc-text)]">Outside: Walk-In Self-Referrals</span>
                                    <span class="text-[11px] text-[var(--hc-text-muted)] font-normal">Direct community requests & checkups</span>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <input type="number" min="0" x-model.number="walkinPts" class="hc-input text-center font-bold text-sm w-28 mx-auto" placeholder="0">
                        </td>
                        <td class="text-center" rowspan="2">
                            <input type="number" min="0" x-model.number="outsideTests" class="hc-input text-center font-bold text-sm w-28 mx-auto" placeholder="0">
                            <span class="block text-[10px] text-[var(--hc-text-muted)] mt-1 font-mono">Combined Outside Tests</span>
                        </td>
                        <td class="text-right font-mono font-semibold text-xs text-[var(--hc-text-muted)]" rowspan="2">
                            <span x-text="totalOutsidePts > 0 ? (outsideTests / totalOutsidePts).toFixed(2) + 'x' : '0.00x'"></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="font-semibold">
                            <div class="flex items-center gap-2">
                                <span class="status-dot status-dot-brand"></span>
                                <div>
                                    <span class="block text-[var(--hc-text)]">Outside: B2B Referral Clinics</span>
                                    <span class="text-[11px] text-[var(--hc-text-muted)] font-normal">External partner clinics and laboratories</span>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <input type="number" min="0" x-model.number="partnerPts" class="hc-input text-center font-bold text-sm w-28 mx-auto" placeholder="0">
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="bg-[var(--hc-surface-alt)] font-bold">
                        <td class="text-[var(--hc-text)] uppercase tracking-wide text-xs">Total Diagnostic Volume</td>
                        <td class="text-center font-mono text-base text-[var(--color-primary)]" x-text="totalLabPatients"></td>
                        <td class="text-center font-mono text-base text-[var(--color-primary)]" x-text="totalLabTests"></td>
                        <td class="text-right font-mono text-sm text-[var(--hc-text)]" x-text="testsPerPatientRatio + 'x'"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Section B: B2B Referral Partner Breakdown --}}
    <div class="hc-card">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-[var(--hc-border)]">
            <div class="flex items-center gap-2">
                <span class="hc-badge hc-badge-brand">SECTION B</span>
                <div>
                    <h3 class="text-sm font-bold tracking-tight text-[var(--hc-text)]">
                        B2B Partner Referrals Breakdown
                    </h3>
                    <p class="text-xs text-[var(--hc-text-muted)]">
                        Itemized external volume and diagnostic partner clinics
                    </p>
                </div>
            </div>
            <button type="button" @click="addPartner()" class="btn btn-secondary btn-sm">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Add Partner Entry</span>
            </button>
        </div>

        <template x-if="partners.length === 0">
            <div class="p-8 text-center border border-dashed border-[var(--hc-border)] rounded-md bg-[var(--hc-surface-alt)]">
                <i data-lucide="building-2" class="w-8 h-8 mx-auto text-[var(--hc-text-muted)] mb-2"></i>
                <p class="text-xs font-semibold text-[var(--hc-text)]">No external partner referrals logged for this date.</p>
                <p class="text-[11px] text-[var(--hc-text-muted)] mt-1">Click "Add Partner Entry" if samples were referred by partner clinics.</p>
            </div>
        </template>

        <template x-if="partners.length > 0">
            <div class="overflow-x-auto">
                <table class="hc-table">
                    <thead>
                        <tr>
                            <th>Partner Facility / Clinic Name</th>
                            <th class="w-32 text-center">Patients</th>
                            <th class="w-32 text-center">Tests</th>
                            <th class="w-20 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(part, idx) in partners" :key="'partner-'+idx">
                            <tr>
                                <td>
                                    <input type="text" x-model="part.partner" class="hc-input font-medium" placeholder="Clinic or Doctor Name">
                                </td>
                                <td>
                                    <input type="number" min="0" x-model.number="part.patients" class="hc-input text-center font-bold" placeholder="0">
                                </td>
                                <td>
                                    <input type="number" min="0" x-model.number="part.tests" class="hc-input text-center font-bold" placeholder="0">
                                </td>
                                <td class="text-center">
                                    <button type="button" @click="removePartner(idx)" class="btn btn-danger btn-xs" title="Remove row">
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

    {{-- Section C: Bench Breakdown & Quality Indicators --}}
    <div class="space-y-6">
        {{-- LMS Success Toast Banner --}}
        <div x-show="lmsSuccessToast"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="p-4 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center shrink-0">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wide text-emerald-300">LMS Shift Export Successfully Ingested</h4>
                    <p class="text-xs text-emerald-400/90 font-medium" x-text="lmsSuccessToast"></p>
                </div>
            </div>
            <button type="button" @click="lmsSuccessToast = ''" class="btn btn-ghost btn-xs text-emerald-400 hover:text-emerald-300">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Diagnostic Bench Breakdown: 13 Official LMS Specialties --}}
            <div class="lg:col-span-2 hc-card space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-[var(--hc-border)]">
                    <div class="flex items-center gap-2">
                        <span class="hc-badge hc-badge-purple">SECTION C</span>
                        <div>
                            <h3 class="text-sm font-bold tracking-tight text-[var(--hc-text)]">
                                Diagnostic Bench Breakdown (Tests by Specialty)
                            </h3>
                            <p class="text-xs text-[var(--hc-text-muted)]">
                                Official 13 laboratory divisions auto-aggregated from LMS reports
                            </p>
                        </div>
                    </div>

                    {{-- LMS Shift Report Upload Trigger --}}
                    <div class="flex items-center gap-2">
                        <input type="file"
                               x-ref="lmsFileInput"
                               @change="handleLmsFileUpload($event)"
                               accept=".xlsx,.xls,.csv,.txt"
                               class="hidden">

                        <button type="button"
                                @click="triggerLmsUpload()"
                                :disabled="isUploadingLms"
                                class="btn btn-primary btn-sm flex items-center gap-1.5 font-bold shadow-xs">
                            <template x-if="isUploadingLms">
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i>
                                    <span>Parsing LMS Report...</span>
                                </span>
                            </template>
                            <template x-if="!isUploadingLms">
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                                    <span>Upload LMS Shift Report (.xlsx, .csv)</span>
                                </span>
                            </template>
                        </button>
                    </div>
                </div>

                {{-- 13 LMS Lab Types Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3.5">
                    {{-- 1. HAEMATOLOGY --}}
                    <div class="p-3 rounded-lg border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] hover:border-rose-500/40 transition">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold text-rose-400">1. HAEMATOLOGY</span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-rose-500/10 text-rose-300">HAEM</span>
                        </div>
                        <input type="number" min="0" x-model.number="benchHematology" class="hc-input font-bold text-sm" placeholder="0">
                        <span class="text-[10px] text-[var(--hc-text-muted)] mt-1 block truncate">Sysmex / CBC, Blood Film, ESR, HB</span>
                    </div>

                    {{-- 2. BIOCHEMISTRY --}}
                    <div class="p-3 rounded-lg border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] hover:border-blue-500/40 transition">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold text-blue-400">2. BIOCHEMISTRY</span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-300">BIO</span>
                        </div>
                        <input type="number" min="0" x-model.number="benchBiochemistry" class="hc-input font-bold text-sm" placeholder="0">
                        <span class="text-[10px] text-[var(--hc-text-muted)] mt-1 block truncate">Mindray BS-240 / LFT, RFT, Electrolytes</span>
                    </div>

                    {{-- 3. SEROLOGY --}}
                    <div class="p-3 rounded-lg border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] hover:border-amber-500/40 transition">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold text-amber-400">3. SEROLOGY</span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-300">SERO</span>
                        </div>
                        <input type="number" min="0" x-model.number="benchSerology" class="hc-input font-bold text-sm" placeholder="0">
                        <span class="text-[10px] text-[var(--hc-text-muted)] mt-1 block truncate">Typhoid, Brucella, Viral Hepatitis, HIV</span>
                    </div>

                    {{-- 4. HORMONES --}}
                    <div class="p-3 rounded-lg border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] hover:border-purple-500/40 transition">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold text-purple-400">4. HORMONES</span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-purple-500/10 text-purple-300">HORM</span>
                        </div>
                        <input type="number" min="0" x-model.number="benchHormones" class="hc-input font-bold text-sm" placeholder="0">
                        <span class="text-[10px] text-[var(--hc-text-muted)] mt-1 block truncate">TSH, T3, T4, Fertility (FSH/LH/PRL)</span>
                    </div>

                    {{-- 5. URINE --}}
                    <div class="p-3 rounded-lg border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] hover:border-yellow-500/40 transition">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold text-yellow-400">5. URINE ANALYSIS</span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-yellow-500/10 text-yellow-300">URN</span>
                        </div>
                        <input type="number" min="0" x-model.number="benchUrine" class="hc-input font-bold text-sm" placeholder="0">
                        <span class="text-[10px] text-[var(--hc-text-muted)] mt-1 block truncate">Routine Chemistry Strip & Microscopy</span>
                    </div>

                    {{-- 6. STOOL --}}
                    <div class="p-3 rounded-lg border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] hover:border-orange-500/40 transition">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold text-orange-400">6. STOOL</span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-orange-500/10 text-orange-300">STL</span>
                        </div>
                        <input type="number" min="0" x-model.number="benchStool" class="hc-input font-bold text-sm" placeholder="0">
                        <span class="text-[10px] text-[var(--hc-text-muted)] mt-1 block truncate">Stool Analysis, H. Pylori Ag, FOB, S. Typhi</span>
                    </div>

                    {{-- 7. QUANTITATIVE TEST --}}
                    <div class="p-3 rounded-lg border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] hover:border-cyan-500/40 transition">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold text-cyan-400">7. QUANTITATIVE TEST</span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-cyan-500/10 text-cyan-300">QUANT</span>
                        </div>
                        <input type="number" min="0" x-model.number="benchQuantitative" class="hc-input font-bold text-sm" placeholder="0">
                        <span class="text-[10px] text-[var(--hc-text-muted)] mt-1 block truncate">Vit D, B12, CRP Quant, Total IgE, PSA</span>
                    </div>

                    {{-- 8. COAGULATION PROFILE --}}
                    <div class="p-3 rounded-lg border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] hover:border-emerald-500/40 transition">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold text-emerald-400">8. COAGULATION PROFILE</span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-300">COAG</span>
                        </div>
                        <input type="number" min="0" x-model.number="benchCoagulation" class="hc-input font-bold text-sm" placeholder="0">
                        <span class="text-[10px] text-[var(--hc-text-muted)] mt-1 block truncate">PT, INR, PTT, Clotting Time, D-Dimer</span>
                    </div>

                    {{-- 9. MICROBIOLOGY AND PARASITOLOGY --}}
                    <div class="p-3 rounded-lg border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] hover:border-teal-500/40 transition">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold text-teal-400 truncate">9. MICROBIOLOGY & PARA</span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-teal-500/10 text-teal-300">MICR</span>
                        </div>
                        <input type="number" min="0" x-model.number="benchMicrobiology" class="hc-input font-bold text-sm" placeholder="0">
                        <span class="text-[10px] text-[var(--hc-text-muted)] mt-1 block truncate">Urine / Stool / Pus C/S, KOH, Swabs</span>
                    </div>

                    {{-- 10. BLOOD BANK --}}
                    <div class="p-3 rounded-lg border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] hover:border-red-500/40 transition">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold text-red-400">10. BLOOD BANK</span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-red-500/10 text-red-300">BBK</span>
                        </div>
                        <input type="number" min="0" x-model.number="benchBloodBank" class="hc-input font-bold text-sm" placeholder="0">
                        <span class="text-[10px] text-[var(--hc-text-muted)] mt-1 block truncate">Blood Transfusion, Crossmatch, Screening</span>
                    </div>

                    {{-- 11. SEMEN ANALYSIS --}}
                    <div class="p-3 rounded-lg border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] hover:border-indigo-500/40 transition">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold text-indigo-400 truncate">11. SEMEN ANALYSIS</span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-indigo-500/10 text-indigo-300">SEMN</span>
                        </div>
                        <input type="number" min="0" x-model.number="benchSemen" class="hc-input font-bold text-sm" placeholder="0">
                        <span class="text-[10px] text-[var(--hc-text-muted)] mt-1 block truncate">Semen Analysis & Sperm Morphology</span>
                    </div>

                    {{-- 12. TB ANALYSIS --}}
                    <div class="p-3 rounded-lg border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] hover:border-amber-600/40 transition">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold text-amber-500">12. TB ANALYSIS</span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-amber-600/10 text-amber-400">TB</span>
                        </div>
                        <input type="number" min="0" x-model.number="benchTb" class="hc-input font-bold text-sm" placeholder="0">
                        <span class="text-[10px] text-[var(--hc-text-muted)] mt-1 block truncate">Sputum AFB & GeneXpert Molecular</span>
                    </div>

                    {{-- 13. BODY FLUIDS --}}
                    <div class="p-3 rounded-lg border border-[var(--hc-border)] bg-[var(--hc-surface-alt)] hover:border-sky-500/40 transition">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold text-sky-400 truncate">13. BODY FLUIDS</span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-sky-500/10 text-sky-300">FLUD</span>
                        </div>
                        <input type="number" min="0" x-model.number="benchBodyFluids" class="hc-input font-bold text-sm" placeholder="0">
                        <span class="text-[10px] text-[var(--hc-text-muted)] mt-1 block truncate">Pleural, Ascitic, Synovial Fluid</span>
                    </div>
                </div>

                {{-- Bench Total Sum & Balance Verification Bar --}}
                <div class="pt-3 border-t border-[var(--hc-border)] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                    <div class="flex items-center gap-2">
                        <i data-lucide="calculator" class="w-4 h-4 text-[var(--color-primary)]"></i>
                        <span class="font-bold text-[var(--hc-text)]">Diagnostic Specialty Sum:</span>
                        <span class="font-mono font-bold text-sm text-[var(--color-primary)]" x-text="Number(totalBenchTests).toLocaleString() + ' Tests'"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <template x-if="totalBenchTests === totalLabTests && totalLabTests > 0">
                            <span class="hc-badge hc-badge-success flex items-center gap-1">
                                <i data-lucide="check" class="w-3 h-3"></i>
                                <span>Perfect Balance (Benches = Total Section A Volume)</span>
                            </span>
                        </template>
                        <template x-if="totalBenchTests !== totalLabTests && totalLabTests > 0">
                            <span class="hc-badge hc-badge-warning flex items-center gap-1">
                                <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                                <span x-text="'Variance: ' + Math.abs(totalBenchTests - totalLabTests) + ' tests'"></span>
                            </span>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Quality Incident Flags --}}
            <div class="hc-card">
                <div class="flex items-center gap-2 pb-3 mb-4 border-b border-[var(--hc-border)]">
                    <span class="hc-badge hc-badge-danger">QUALITY FLAGS</span>
                    <h3 class="text-sm font-bold tracking-tight text-[var(--hc-text)]">
                        Shift Incidents
                    </h3>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="hc-label text-[var(--hc-danger)]">Sample Rejections (Clotted / Hemolyzed)</label>
                        <input type="number" min="0" x-model.number="sampleRejections" class="hc-input font-bold text-sm" placeholder="0">
                        <span class="text-[10px] text-[var(--hc-text-muted)] mt-1 block">Requires immediate redraw request</span>
                    </div>
                    <div>
                        <label class="hc-label text-[var(--hc-warning)]">Delayed Tests Exceeding TAT (> 2h)</label>
                        <input type="number" min="0" x-model.number="delayedTat" class="hc-input font-bold text-sm" placeholder="0">
                        <span class="text-[10px] text-[var(--hc-text-muted)] mt-1 block">Beyond published turn-around benchmark</span>
                    </div>
                    <div>
                        <label class="hc-label text-[var(--hc-danger)]">Critical Panic Values Escalated</label>
                        <input type="number" min="0" x-model.number="criticalValues" class="hc-input font-bold text-sm" placeholder="0">
                        <span class="text-[10px] text-[var(--hc-text-muted)] mt-1 block">Direct verbal doctor alert executed</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Smart Shift Tests Register (Individual Test Level Detail) --}}
        <div class="hc-card space-y-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 pb-3 border-b border-[var(--hc-border)]">
                <div class="flex items-center gap-2">
                    <span class="hc-badge hc-badge-purple">SMART TEST REGISTER</span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold tracking-tight text-[var(--hc-text)]">
                                Individual Tests Examined (Shift Breakdown)
                            </h3>
                            <span class="hc-badge hc-badge-info font-mono text-[11px]" x-text="orderedTests.length + ' Tests Ordered Today'"></span>
                        </div>
                        <p class="text-xs text-[var(--hc-text-muted)] flex flex-wrap items-center gap-2 mt-0.5">
                            <span>LMS uploads on same date are <strong>cumulative</strong> (e.g. morning 4 + afternoon 5 &rarr; total 5).</span>
                            <span class="text-emerald-500 font-medium">&bull; Manual entries are <strong>additive (old + new)</strong>.</span>
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    {{-- Search Input --}}
                    <div class="relative">
                        <i data-lucide="search" class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-[var(--hc-text-muted)]"></i>
                        <input type="text"
                               x-model="testSearchTerm"
                               placeholder="Filter test e.g. CBC, Troponin..."
                               class="hc-input pl-8 py-1.5 text-xs w-48 sm:w-56">
                    </div>

                    {{-- Specialty Filter --}}
                    <select x-model="testFilterType" class="hc-input hc-select text-xs py-1.5 px-2.5 w-auto">
                        <option value="all">All Specialties (13)</option>
                        @foreach($labTypesMeta as $code => $meta)
                            <option value="{{ $code }}">{{ $meta['label'] ?? ($meta['short'] ?? ucfirst($code)) }}</option>
                        @endforeach
                    </select>

                    {{-- Toggle Show All Catalog vs Smart View --}}
                    <button type="button"
                            @click="showAllTests = !showAllTests"
                            class="btn btn-secondary btn-sm flex items-center gap-1.5 text-xs">
                        <i :data-lucide="showAllTests ? 'eye-off' : 'layers'" class="w-3.5 h-3.5"></i>
                        <span x-text="showAllTests ? 'Smart Ordered Only' : 'Show Full Catalog (' + allCatalogTests.length + ')'"></span>
                    </button>
                </div>
            </div>

            {{-- Empty State when no tests ordered today and smart filter active --}}
            <template x-if="visibleTests.length === 0">
                <div class="p-8 text-center border border-dashed border-[var(--hc-border)] rounded-lg bg-[var(--hc-surface-alt)]">
                    <div class="w-12 h-12 rounded-full bg-purple-500/10 text-purple-400 flex items-center justify-center mx-auto mb-2">
                        <i data-lucide="file-spreadsheet" class="w-6 h-6"></i>
                    </div>
                    <h4 class="text-sm font-bold text-[var(--hc-text)]">No Active Tests Matching Current Filter</h4>
                    <p class="text-xs text-[var(--hc-text-muted)] max-w-md mx-auto mt-1 mb-4">
                        Upload your LMS shift report above to automatically populate test tallies, or toggle to full catalog mode to enter counts manually.
                    </p>
                    <div class="flex items-center justify-center gap-2">
                        <button type="button" @click="triggerLmsUpload()" class="btn btn-primary btn-sm">
                            <i data-lucide="upload-cloud" class="w-3.5 h-3.5"></i>
                            <span>Upload LMS Shift Report</span>
                        </button>
                        <button type="button" @click="showAllTests = true" class="btn btn-secondary btn-sm">
                            <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                            <span>Browse Full 135 Tests Catalog</span>
                        </button>
                    </div>
                </div>
            </template>

            {{-- Table of Visible Tests --}}
            <template x-if="visibleTests.length > 0">
                <div class="overflow-x-auto rounded-lg border border-[var(--hc-border)] max-h-96 overflow-y-auto">
                    <table class="hc-table">
                        <thead class="sticky top-0 bg-[var(--hc-surface)] z-10 shadow-xs">
                            <tr>
                                <th class="w-10 text-center">#</th>
                                <th>Test Examination Name</th>
                                <th class="w-40">LMS Lab Type</th>
                                <th class="w-32 text-center">Examined Count</th>
                                <th class="w-52 text-center">+ Add Manual Tests (Old + New)</th>
                                <th class="w-32 text-right">Quick Steppers</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(test, idx) in visibleTests" :key="test.test_name">
                                <tr class="hover:bg-[var(--hc-surface-alt)] transition">
                                    <td class="text-center font-mono text-xs text-[var(--hc-text-muted)]" x-text="idx + 1"></td>
                                    <td>
                                        <div class="font-bold text-xs text-[var(--hc-text)]" x-text="test.test_name"></div>
                                    </td>
                                    <td>
                                        <span class="hc-badge text-[10px] font-mono uppercase"
                                              :class="{
                                                  'hc-badge-danger': test.lab_type === 'haematology' || test.lab_type === 'blood_bank',
                                                  'hc-badge-info': test.lab_type === 'biochemistry' || test.lab_type === 'body_fluids',
                                                  'hc-badge-warning': test.lab_type === 'serology' || test.lab_type === 'tb_analysis',
                                                  'hc-badge-purple': test.lab_type === 'hormones' || test.lab_type === 'semen_analysis',
                                                  'hc-badge-success': test.lab_type === 'coagulation_profile' || test.lab_type === 'microbiology_parasitology',
                                                  'hc-badge-cyan': test.lab_type === 'quantitative_test' || test.lab_type === 'urine' || test.lab_type === 'stool'
                                              }"
                                              x-text="test.lab_type_name || test.lab_type"></span>
                                    </td>
                                    <td class="text-center">
                                        <input type="number"
                                               min="0"
                                               :value="test.count || 0"
                                               @input="updateTestCount(test.test_name, $event.target.value)"
                                               class="hc-input font-mono font-bold text-sm text-center py-1 w-24 mx-auto"
                                               placeholder="0"
                                               title="Total tests examined (or type +5 to add 5)">
                                    </td>
                                    <td class="text-center" x-data="{ manualAddQty: '' }">
                                        <div class="inline-flex items-center gap-1.5">
                                            <div class="relative w-20">
                                                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-xs font-bold text-emerald-500">+</span>
                                                <input type="number"
                                                       min="1"
                                                       x-model.number="manualAddQty"
                                                       @keydown.enter.prevent="if(manualAddQty > 0) { addTestCountManual(test.test_name, manualAddQty); manualAddQty = ''; }"
                                                       class="hc-input font-mono font-bold text-xs pl-5 pr-1 py-1 text-center w-full bg-emerald-500/5 border-emerald-500/30 focus:border-emerald-500"
                                                       placeholder="Qty">
                                            </div>
                                            <button type="button"
                                                    @click="if(manualAddQty > 0) { addTestCountManual(test.test_name, manualAddQty); manualAddQty = ''; }"
                                                    :disabled="!manualAddQty || manualAddQty <= 0"
                                                    class="btn btn-xs bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-2 py-1 shadow-xs disabled:opacity-30 disabled:pointer-events-none"
                                                    title="Add tests (adds to existing count: old + new)">
                                                + Add
                                            </button>
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <div class="inline-flex items-center gap-1">
                                            <button type="button"
                                                    @click="updateTestCount(test.test_name, Math.max(0, (parseInt(test.count) || 0) - 1))"
                                                    class="btn btn-secondary btn-xs px-1.5 py-1"
                                                    title="Decrease 1">
                                                -1
                                            </button>
                                            <button type="button"
                                                    @click="addTestCountManual(test.test_name, 1)"
                                                    class="btn btn-secondary btn-xs px-1.5 py-1 text-emerald-500 font-bold"
                                                    title="Add +1 to existing total">
                                                +1
                                            </button>
                                            <button type="button"
                                                    @click="addTestCountManual(test.test_name, 5)"
                                                    class="btn btn-secondary btn-xs px-1.5 py-1 text-emerald-500 font-bold"
                                                    title="Add +5 to existing total">
                                                +5
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </template>
        </div>
    </div>

    {{-- Date Mismatch Warning Modal --}}
    <div x-show="openDateMismatchModal"
         x-cloak
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 z-50 text-left"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        <div @click.away="openDateMismatchModal = false"
             class="bg-[var(--hc-surface)] rounded-xl max-w-lg w-full border border-amber-500/40 shadow-2xl p-6 space-y-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-[var(--hc-text)]">
                        LMS Report Date Mismatch Warning
                    </h3>
                    <p class="text-xs text-[var(--hc-text-muted)] mt-1">
                        The uploaded LMS report date differs from your current form report date.
                    </p>
                </div>
            </div>

            <div class="p-3.5 rounded-lg bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[var(--hc-text-muted)]">Extracted LMS Print Date:</span>
                    <strong class="font-mono text-amber-400 text-sm" x-text="mismatchInfo.extractedDate"></strong>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-[var(--hc-text-muted)]">Current Form Report Date:</span>
                    <strong class="font-mono text-[var(--color-primary)] text-sm" x-text="reportDate"></strong>
                </div>
                <div class="pt-2 border-t border-[var(--hc-border)] text-[11px] text-amber-300/90 leading-relaxed" x-text="mismatchInfo.warning"></div>
            </div>

            <div class="text-xs text-[var(--hc-text-muted)]">
                Would you like to change the form date to match the LMS report date, import data into today's report anyway, or cancel?
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-end gap-2 pt-2 border-t border-[var(--hc-border)]">
                <button type="button"
                        @click="openDateMismatchModal = false; isUploadingLms = false;"
                        class="btn btn-secondary btn-sm w-full sm:w-auto">
                    Cancel Upload
                </button>
                <button type="button"
                        @click="reportDate = mismatchInfo.extractedDate; confirmMismatchImport();"
                        class="btn btn-warning btn-sm w-full sm:w-auto font-bold">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                    <span>Change Form Date &amp; Apply</span>
                </button>
                <button type="button"
                        @click="confirmMismatchImport();"
                        class="btn btn-primary btn-sm w-full sm:w-auto font-bold">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    <span>Apply Anyway</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Section D: Nil Items / Reagent Stockouts --}}
    <div class="hc-card">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-[var(--hc-border)]">
            <div class="flex items-center gap-2">
                <span class="hc-badge hc-badge-warning">SECTION D</span>
                <div>
                    <h3 class="text-sm font-bold tracking-tight text-[var(--hc-text)]">
                        Nil Items & Reagents Out of Stock
                    </h3>
                    <p class="text-xs text-[var(--hc-text-muted)]">
                        Diagnostics unserved or referred outside due to reagent depletion
                    </p>
                </div>
            </div>
            <button type="button" @click="addNilItem()" class="btn btn-secondary btn-sm">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Report Nil Reagent</span>
            </button>
        </div>

        <template x-if="nilItems.length === 0">
            <div class="p-6 text-center border border-dashed border-[var(--hc-border)] rounded-md bg-[var(--hc-surface-alt)]">
                <i data-lucide="check-circle" class="w-6 h-6 mx-auto text-[var(--hc-success)] mb-1"></i>
                <p class="text-xs font-semibold text-[var(--hc-text)]">Zero Stockouts: All laboratory testing reagents available.</p>
            </div>
        </template>

        <template x-if="nilItems.length > 0">
            <div class="overflow-x-auto">
                <table class="hc-table">
                    <thead>
                        <tr>
                            <th>Reagent / Test Name</th>
                            <th class="w-40">Bench</th>
                            <th>Reason for Depletion</th>
                            <th class="w-36">Est. Restock Date</th>
                            <th class="w-16 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(nil, idx) in nilItems" :key="'nil-'+idx">
                            <tr>
                                <td>
                                    <input type="text" x-model="nil.item_name" class="hc-input font-semibold" placeholder="e.g. Troponin-I FIA Kits">
                                </td>
                                <td>
                                    <select x-model="nil.bench" class="hc-input hc-select text-xs">
                                        <option value="Biochemistry">Biochemistry</option>
                                        <option value="Hematology">Hematology</option>
                                        <option value="Serology">Serology</option>
                                        <option value="Microbiology">Microbiology</option>
                                        <option value="Hormones">Hormones</option>
                                        <option value="Blood Bank">Blood Bank</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" x-model="nil.reason" class="hc-input" placeholder="Supplier delayed / unexpected spike">
                                </td>
                                <td>
                                    <input type="date" x-model="nil.expected_restock" class="hc-input font-mono text-xs">
                                </td>
                                <td class="text-center">
                                    <button type="button" @click="removeNilItem(idx)" class="btn btn-danger btn-xs">
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
