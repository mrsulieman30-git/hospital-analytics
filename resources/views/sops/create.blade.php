@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12">

    <!-- Top Breadcrumb & Header -->
    <div class="flex items-center gap-3 border-b pb-4" style="border-color: var(--hc-border);">
        <a href="{{ route('sops.index') }}" 
           class="p-2 rounded-lg transition"
           style="background: var(--hc-surface-alt); color: var(--hc-text);"
           title="Back to Directory">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold" style="color: var(--hc-text-muted);">
                <span>Standard Operating Procedures</span>
                <span>/</span>
                <span>Upload New Protocol</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight" style="color: var(--hc-text);">
                Upload Clinical SOP & Extract Procedure
            </h1>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl text-white text-xs font-medium space-y-1" style="background-color: #DC2626;">
            <div class="font-bold text-sm">Please resolve the following errors:</div>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Upload Form -->
    <form action="{{ route('sops.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6"
          x-data="{ uploadMode: 'file' }">
        @csrf

        <!-- Section 1: Document Upload / Template Paste Box -->
        <div class="rounded-2xl border p-6 space-y-4 shadow-xs" 
             style="background: var(--hc-surface); border-color: var(--hc-border);">
            
            <div class="flex items-center justify-between pb-3 border-b" style="border-color: var(--hc-border);">
                <div class="flex items-center gap-2">
                    <span class="p-2 rounded-lg text-white" style="background-color: #124E78;">
                        <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold" style="color: var(--hc-text);">1. SOP Source Document or Template</h3>
                        <p class="text-xs" style="color: var(--hc-text-muted);">Upload a document or paste your SOP template text. Tables, parameters, and reference ranges are extracted automatically.</p>
                    </div>
                </div>

                <!-- Mode Switcher (Solid Color Pills) -->
                <div class="flex items-center p-1 rounded-xl" style="background: var(--hc-surface-alt); border: 1px solid var(--hc-border);">
                    <button type="button" @click="uploadMode = 'file'" 
                            :class="uploadMode === 'file' ? 'text-white font-bold' : 'text-slate-500 font-semibold'"
                            :style="uploadMode === 'file' ? 'background-color: #124E78;' : ''"
                            class="px-3 py-1 rounded-lg text-xs transition flex items-center gap-1.5">
                        <i data-lucide="file" class="w-3.5 h-3.5"></i>
                        <span>Upload File</span>
                    </button>
                    <button type="button" @click="uploadMode = 'paste'" 
                            :class="uploadMode === 'paste' ? 'text-white font-bold' : 'text-slate-500 font-semibold'"
                            :style="uploadMode === 'paste' ? 'background-color: #124E78;' : ''"
                            class="px-3 py-1 rounded-lg text-xs transition flex items-center gap-1.5">
                        <i data-lucide="clipboard-paste" class="w-3.5 h-3.5"></i>
                        <span>Paste Template Text</span>
                    </button>
                </div>
            </div>

            <!-- Mode 1: File Dropper -->
            <div x-show="uploadMode === 'file'">
                <label for="sop_file" class="block text-xs font-bold uppercase tracking-wider mb-2" style="color: var(--hc-text);">
                    Select Document File (.docx, .doc, .pdf, .txt)
                </label>
                <div class="border-2 border-dashed rounded-xl p-6 text-center cursor-pointer transition hover:border-[#124E78]"
                     style="border-color: var(--hc-border); background: var(--hc-surface-alt);">
                    <input type="file" name="sop_file" id="sop_file" accept=".docx,.doc,.pdf,.txt" class="w-full text-xs font-semibold cursor-pointer"
                           style="color: var(--hc-text);">
                    <div class="mt-2 text-xs" style="color: var(--hc-text-muted);">
                        Supported file formats: <strong>.docx, .doc, .pdf, .txt</strong> (Maximum size: 25MB)
                    </div>
                </div>
            </div>

            <!-- Mode 2: Direct Template Paste -->
            <div x-show="uploadMode === 'paste'" style="display: none;">
                <label for="sop_text" class="block text-xs font-bold uppercase tracking-wider mb-2" style="color: var(--hc-text);">
                    Paste SOP Document / Template Content
                </label>
                <textarea name="sop_text" id="sop_text" rows="10" 
                          placeholder="Paste your benchtop SOP template here (e.g. Document Control table, Sample Type, System Parameters, Analytical Procedure, Reference Ranges, Troubleshooting)..."
                          class="w-full p-3.5 rounded-xl text-xs font-mono border transition"
                          style="background: var(--hc-surface-alt); border-color: var(--hc-border); color: var(--hc-text);"></textarea>
                <div class="mt-1 text-[11px] text-slate-500">
                    The system will automatically parse tables, numbered procedure steps, specimen criteria, and reference intervals into colorful minimalist cards.
                </div>
            </div>
        </div>

        <!-- Section 2: Clinical SOP Metadata -->
        <div class="rounded-2xl border p-6 space-y-5 shadow-xs" 
             style="background: var(--hc-surface); border-color: var(--hc-border);">
            
            <div class="flex items-center gap-2 pb-2 border-b" style="border-color: var(--hc-border);">
                <span class="p-2 rounded-lg text-white" style="background-color: #7C3AED;">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                </span>
                <div>
                    <h3 class="text-sm font-bold" style="color: var(--hc-text);">2. Protocol Identification & Governance</h3>
                    <p class="text-xs" style="color: var(--hc-text-muted);">Optional override: Any fields left blank will be extracted directly from the document.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Title -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--hc-text);">
                        SOP Title <span class="text-[11px] text-slate-400 font-normal lowercase">(auto-extracted if blank)</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title') }}" 
                           class="w-full px-3.5 py-2 rounded-xl text-xs font-semibold border transition"
                           style="background: var(--hc-surface); border-color: var(--hc-border); color: var(--hc-text);"
                           placeholder="e.g. CREATINE KINASE-MB (CK-MB) — FINECARE™ FIA">
                </div>

                <!-- SOP Code -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--hc-text);">
                        SOP Identification Code <span class="text-[11px] text-slate-400 font-normal lowercase">(auto-extracted if blank)</span>
                    </label>
                    <input type="text" name="sop_code" value="{{ old('sop_code') }}" 
                           class="w-full px-3.5 py-2 rounded-xl text-xs font-mono font-bold border uppercase transition"
                           style="background: var(--hc-surface); border-color: var(--hc-border); color: var(--hc-text);"
                           placeholder="e.g. KH-LAB-SOP-009">
                </div>

                <!-- Department -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--hc-text);">
                        Clinical Department
                    </label>
                    <select name="department" 
                            class="w-full px-3.5 py-2 rounded-xl text-xs font-semibold border transition"
                            style="background: var(--hc-surface); border-color: var(--hc-border); color: var(--hc-text);">
                        <option value="" selected>Auto-detect from document</option>
                        <option value="laboratory" {{ old('department') === 'laboratory' ? 'selected' : '' }}>Laboratory (Pathology & Biomarkers)</option>
                        <option value="pharmacy" {{ old('department') === 'pharmacy' ? 'selected' : '' }}>Pharmacy & Therapeutics</option>
                        <option value="emergency" {{ old('department') === 'emergency' ? 'selected' : '' }}>Emergency Department (E.R)</option>
                        <option value="inpatient" {{ old('department') === 'inpatient' ? 'selected' : '' }}>Inpatient (IPD / Ward)</option>
                        <option value="reception" {{ old('department') === 'reception' ? 'selected' : '' }}>Reception & Outpatient (OPD)</option>
                        <option value="general" {{ old('department') === 'general' ? 'selected' : '' }}>General Hospital Governance</option>
                    </select>
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--hc-text);">
                        Category / Section
                    </label>
                    <input type="text" name="category" value="{{ old('category') }}" 
                           class="w-full px-3.5 py-2 rounded-xl text-xs font-semibold border transition"
                           style="background: var(--hc-surface); border-color: var(--hc-border); color: var(--hc-text);"
                           placeholder="e.g. Hematology, Quality Control, Dispensing">
                </div>

                <!-- Version -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--hc-text);">
                        Revision / Version
                    </label>
                    <input type="text" name="version" value="{{ old('version', '1.0') }}" 
                           class="w-full px-3.5 py-2 rounded-xl text-xs font-semibold border transition"
                           style="background: var(--hc-surface); border-color: var(--hc-border); color: var(--hc-text);"
                           placeholder="1.0">
                </div>

                <!-- Author Name -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--hc-text);">
                        Author / Lead Specialist
                    </label>
                    <input type="text" name="author_name" value="{{ old('author_name', auth()->user()?->name ?? '') }}" 
                           class="w-full px-3.5 py-2 rounded-xl text-xs font-semibold border transition"
                           style="background: var(--hc-surface); border-color: var(--hc-border); color: var(--hc-text);"
                           placeholder="e.g. Clinical Pathologist">
                </div>

                <!-- Effective Date -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--hc-text);">
                        Effective Date
                    </label>
                    <input type="date" name="effective_date" value="{{ old('effective_date', now()->format('Y-m-d')) }}" 
                           class="w-full px-3.5 py-2 rounded-xl text-xs font-semibold border transition"
                           style="background: var(--hc-surface); border-color: var(--hc-border); color: var(--hc-text);">
                </div>

                <!-- Review Date -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--hc-text);">
                        Next Scheduled Review
                    </label>
                    <input type="date" name="review_date" value="{{ old('review_date', now()->addYear()->format('Y-m-d')) }}" 
                           class="w-full px-3.5 py-2 rounded-xl text-xs font-semibold border transition"
                           style="background: var(--hc-surface); border-color: var(--hc-border); color: var(--hc-text);">
                </div>
            </div>
        </div>

        <!-- Section 3: Diagnostic Test Kit Leaflets (Optional Initial Photos) -->
        <div class="rounded-2xl border p-6 space-y-4 shadow-xs" 
             style="background: var(--hc-surface); border-color: var(--hc-border);">
            
            <div class="flex items-center gap-2 pb-2 border-b" style="border-color: var(--hc-border);">
                <span class="p-2 rounded-lg text-white" style="background-color: #059669;">
                    <i data-lucide="camera" class="w-5 h-5"></i>
                </span>
                <div>
                    <h3 class="text-sm font-bold" style="color: var(--hc-text);">3. Diagnostic Kit Leaflets & Package Inserts (Optional)</h3>
                    <p class="text-xs" style="color: var(--hc-text-muted);">Attach photos or scans of the reagent/kit package insert leaflet. You can also add more photos anytime later.</p>
                </div>
            </div>

            <div>
                <label for="leaflets" class="block text-xs font-bold uppercase tracking-wider mb-2" style="color: var(--hc-text);">
                    Select Leaflet Images
                </label>
                <div class="border-2 border-dashed rounded-xl p-5 text-center cursor-pointer transition"
                     style="border-color: var(--hc-border); background: var(--hc-surface-alt);">
                    <input type="file" name="leaflets[]" id="leaflets" multiple accept="image/*" class="w-full text-xs font-semibold cursor-pointer"
                           style="color: var(--hc-text);">
                    <div class="mt-2 text-xs" style="color: var(--hc-text-muted);">
                        Attach multiple JPEG, PNG, or WEBP photos of the reagent package inserts.
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4: Super Admin Access Controls -->
        @if(auth()->user() && auth()->user()->isSuperAdmin())
            <div class="rounded-2xl border p-6 space-y-3 shadow-xs" 
                 style="background: var(--hc-surface); border-color: var(--hc-border);">
                
                <div class="flex items-center gap-2 pb-2 border-b" style="border-color: var(--hc-border);">
                    <span class="p-2 rounded-lg text-white" style="background-color: #D97706;">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold" style="color: var(--hc-text);">4. Super Admin Download Permission</h3>
                        <p class="text-xs" style="color: var(--hc-text-muted);">Control whether non-superadmin staff are allowed to download the raw document file.</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <input type="checkbox" name="is_downloadable" id="is_downloadable" value="1" checked
                           class="w-4 h-4 rounded text-[#124E78] focus:ring-0 cursor-pointer">
                    <label for="is_downloadable" class="text-xs font-bold cursor-pointer" style="color: var(--hc-text);">
                        Allow Staff to Download Original Document File
                    </label>
                </div>
                <p class="text-[11px] text-slate-500 pl-7">
                    If unchecked, staff can only view the procedure online in the portal; file download links will be disabled and access to direct download blocked.
                </p>
            </div>
        @endif

        <!-- Submit & Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t" style="border-color: var(--hc-border);">
            <a href="{{ route('sops.index') }}" 
               class="px-5 py-2.5 rounded-xl text-xs font-semibold transition"
               style="background: var(--hc-surface-alt); color: var(--hc-text); border: 1px solid var(--hc-border);">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 rounded-xl text-xs font-bold text-white transition flex items-center gap-2 shadow-sm"
                    style="background-color: #124E78;">
                <i data-lucide="upload" class="w-4 h-4"></i>
                <span>Upload & Extract SOP</span>
            </button>
        </div>
    </form>
</div>
@endsection
