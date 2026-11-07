@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12" x-data="{ 
    activeTab: 'extracted',
    lightboxOpen: false,
    lightboxImage: '',
    lightboxTitle: ''
}">

    <!-- Top Action Breadcrumb & Admin Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b pb-4" style="border-color: var(--hc-border);">
        <div class="flex items-center gap-3">
            <a href="{{ route('sops.index') }}" 
               class="p-2 rounded-lg transition"
               style="background: var(--hc-surface-alt); color: var(--hc-text);"
               title="Back to SOP Directory">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold" style="color: var(--hc-text-muted);">
                    <span>Standard Operating Procedures</span>
                    <span>/</span>
                    <span class="font-mono uppercase">{{ $sop->sop_code }}</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight" style="color: var(--hc-text);">
                    {{ $sop->title }}
                </h1>
            </div>
        </div>

        <!-- Super Admin Controls & Actions -->
        <div class="flex flex-wrap items-center gap-2">
            @if(auth()->user() && auth()->user()->isSuperAdmin())
                <!-- Toggle Download Permission -->
                <form action="{{ route('superadmin.sops.download.toggle', $sop) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" 
                            class="px-3 py-1.5 rounded-lg text-xs font-bold text-white transition flex items-center gap-1.5 shadow-xs"
                            style="background-color: {{ $sop->is_downloadable ? '#D97706' : '#059669' }};"
                            title="Toggle whether regular staff can download the original file">
                        <i data-lucide="{{ $sop->is_downloadable ? 'lock' : 'unlock' }}" class="w-3.5 h-3.5"></i>
                        <span>{{ $sop->is_downloadable ? 'Lock Downloads' : 'Allow Downloads' }}</span>
                    </button>
                </form>

                <!-- Delete SOP -->
                <form action="{{ route('superadmin.sops.destroy', $sop) }}" method="POST" class="inline" 
                      onsubmit="return confirm('Are you sure you want to permanently delete this SOP and all its leaflets?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-3 py-1.5 rounded-lg text-xs font-bold text-white transition flex items-center gap-1.5 shadow-xs"
                            style="background-color: #DC2626;"
                            title="Permanently remove this SOP">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        <span>Delete</span>
                    </button>
                </form>
            @endif

            <!-- Download Button (Subject to Directorate Permission) -->
            @if($sop->canBeDownloadedBy(auth()->user()))
                <a href="{{ route('sops.download', $sop) }}" 
                   class="px-3.5 py-1.5 rounded-lg text-xs font-bold text-white transition flex items-center gap-1.5 shadow-xs"
                   style="background-color: #124E78;">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                    <span>Download Original ({{ $sop->getFormattedFileSize() }})</span>
                </a>
            @else
                <span class="px-3 py-1.5 rounded-lg text-xs font-bold text-white flex items-center gap-1.5"
                      style="background-color: #64748B;"
                      title="Download restricted by Super Admin Directorate">
                    <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                    <span>Download Restricted</span>
                </span>
            @endif

            <button onclick="window.print()" 
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 no-print"
                    style="background: var(--hc-surface-alt); color: var(--hc-text); border: 1px solid var(--hc-border);">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                <span class="hidden sm:inline">Print Guide</span>
            </button>
        </div>
    </div>

    <!-- SOP Metadata Summary Strip (Solid Clean Styling) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Department -->
        <div class="p-3 rounded-xl border text-left" style="background: var(--hc-surface); border-color: var(--hc-border);">
            <div class="text-[11px] font-semibold uppercase tracking-wider" style="color: var(--hc-text-muted);">Department</div>
            <div class="mt-1 flex items-center gap-1.5">
                <span class="inline-block w-2.5 h-2.5 rounded-full" style="background-color: {{ $sop->getDepartmentBadgeColor() }};"></span>
                <span class="text-xs font-bold capitalize" style="color: var(--hc-text);">{{ $sop->department }}</span>
            </div>
        </div>

        <!-- Code & Version -->
        <div class="p-3 rounded-xl border text-left" style="background: var(--hc-surface); border-color: var(--hc-border);">
            <div class="text-[11px] font-semibold uppercase tracking-wider" style="color: var(--hc-text-muted);">Code & Version</div>
            <div class="mt-1 text-xs font-bold font-mono" style="color: var(--hc-text);">
                {{ $sop->sop_code }} <span class="px-1.5 py-0.5 rounded text-[10px] text-white" style="background-color: #475569;">v{{ $sop->version }}</span>
            </div>
        </div>

        <!-- Category -->
        <div class="p-3 rounded-xl border text-left" style="background: var(--hc-surface); border-color: var(--hc-border);">
            <div class="text-[11px] font-semibold uppercase tracking-wider" style="color: var(--hc-text-muted);">Category</div>
            <div class="mt-1 text-xs font-bold truncate" style="color: var(--hc-text);">{{ $sop->category ?? 'Standard SOP' }}</div>
        </div>

        <!-- Effective Date -->
        <div class="p-3 rounded-xl border text-left" style="background: var(--hc-surface); border-color: var(--hc-border);">
            <div class="text-[11px] font-semibold uppercase tracking-wider" style="color: var(--hc-text-muted);">Effective Date</div>
            <div class="mt-1 text-xs font-semibold" style="color: var(--hc-text);">
                {{ $sop->effective_date ? $sop->effective_date->format('M d, Y') : 'Active' }}
            </div>
        </div>

        <!-- Review Cycle -->
        <div class="p-3 rounded-xl border text-left" style="background: var(--hc-surface); border-color: var(--hc-border);">
            <div class="text-[11px] font-semibold uppercase tracking-wider" style="color: var(--hc-text-muted);">Review Cycle</div>
            <div class="mt-1 text-xs font-semibold" style="color: var(--hc-text);">
                {{ $sop->review_date ? $sop->review_date->format('M d, Y') : 'Annual' }}
            </div>
        </div>

        <!-- File Format & Leaflets -->
        <div class="p-3 rounded-xl border text-left" style="background: var(--hc-surface); border-color: var(--hc-border);">
            <div class="text-[11px] font-semibold uppercase tracking-wider" style="color: var(--hc-text-muted);">Kit Leaflets</div>
            <div class="mt-1 flex items-center gap-2">
                <span class="text-xs font-bold" style="color: var(--hc-text);">
                    {{ count($sop->leaflet_images ?? []) }} Photos
                </span>
                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold text-white uppercase" 
                      style="background-color: {{ $sop->isPdf() ? '#DC2626' : '#0284C7' }};">
                    {{ $sop->file_type }}
                </span>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs (Solid Color Pills - NO Gradients) -->
    <div class="border-b no-print" style="border-color: var(--hc-border);">
        <nav class="flex space-x-2" aria-label="Tabs">
            <button @click="activeTab = 'extracted'"
                    :class="activeTab === 'extracted' ? 'font-bold text-white' : 'font-semibold text-slate-600 dark:text-slate-400'"
                    :style="activeTab === 'extracted' ? 'background-color: #124E78;' : 'background-color: var(--hc-surface-alt);'"
                    class="px-4 py-2.5 rounded-t-lg text-xs sm:text-sm flex items-center gap-2 transition">
                <i data-lucide="book-open" class="w-4 h-4"></i>
                <span>Extracted Clinical Guide</span>
            </button>

            <button @click="activeTab = 'viewer'"
                    :class="activeTab === 'viewer' ? 'font-bold text-white' : 'font-semibold text-slate-600 dark:text-slate-400'"
                    :style="activeTab === 'viewer' ? 'background-color: #124E78;' : 'background-color: var(--hc-surface-alt);'"
                    class="px-4 py-2.5 rounded-t-lg text-xs sm:text-sm flex items-center gap-2 transition">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                <span>Online Document Viewer</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded text-white" style="background-color: #475569;">
                    {{ strtoupper($sop->file_type) }}
                </span>
            </button>

            <button @click="activeTab = 'leaflets'"
                    :class="activeTab === 'leaflets' ? 'font-bold text-white' : 'font-semibold text-slate-600 dark:text-slate-400'"
                    :style="activeTab === 'leaflets' ? 'background-color: #124E78;' : 'background-color: var(--hc-surface-alt);'"
                    class="px-4 py-2.5 rounded-t-lg text-xs sm:text-sm flex items-center gap-2 transition">
                <i data-lucide="image" class="w-4 h-4"></i>
                <span>Test Kit Leaflets & Inserts</span>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded text-white" 
                      style="background-color: {{ count($sop->leaflet_images ?? []) > 0 ? '#059669' : '#64748B' }};">
                    {{ count($sop->leaflet_images ?? []) }}
                </span>
            </button>
        </nav>
    </div>

    <!-- ═══════════════════════════════════════════════════════
         TAB 1: EXTRACTED CLINICAL HTML GUIDE
         ═══════════════════════════════════════════════════════ -->
    <div x-show="activeTab === 'extracted'" class="space-y-6">
        
        <!-- Clinical Protocol Banner (Solid Background) -->
        <div class="p-4 rounded-xl text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
             style="background-color: #124E78;">
            <div>
                <div class="text-xs uppercase tracking-wider font-semibold opacity-90">Controlled Quality Document</div>
                <h2 class="text-lg font-bold">{{ $sop->title }}</h2>
                <p class="text-xs opacity-90 mt-0.5">Author / Lead Specialist: {{ $sop->author_name ?? 'Clinical Director of Operations' }}</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="px-3 py-1 rounded text-xs font-mono font-bold bg-white text-slate-900">
                    {{ $sop->sop_code }}
                </span>
                <span class="px-2.5 py-1 rounded text-xs font-bold text-white" style="background-color: #059669;">
                    APPROVED
                </span>
            </div>
        </div>

        <!-- Formatted Extracted Content -->
        <div class="p-6 sm:p-8 rounded-2xl border shadow-xs" 
             style="background: var(--hc-surface); border-color: var(--hc-border); color: var(--hc-text);">
            @if($sop->extracted_html)
                <div class="prose max-w-none text-sm leading-relaxed" style="color: var(--hc-text);">
                    {!! $sop->extracted_html !!}
                </div>
            @else
                <div class="text-center py-12">
                    <i data-lucide="file-question" class="w-12 h-12 mx-auto text-slate-400 mb-3"></i>
                    <p class="text-sm font-semibold" style="color: var(--hc-text);">No text could be extracted from this document.</p>
                    <p class="text-xs text-slate-500 mt-1">Please view the file directly using the Online Document Viewer tab above.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════
         TAB 2: BUILTIN ONLINE DOCUMENT & PDF VIEWER
         ═══════════════════════════════════════════════════════ -->
    <div x-show="activeTab === 'viewer'" class="space-y-4">
        <div class="flex items-center justify-between p-3 rounded-xl border text-xs font-semibold"
             style="background: var(--hc-surface); border-color: var(--hc-border);">
            <div class="flex items-center gap-2" style="color: var(--hc-text);">
                <i data-lucide="eye" class="w-4 h-4 text-blue-600"></i>
                <span>Direct Online Viewer: <strong class="font-mono">{{ $sop->file_name }}</strong></span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('sops.file', $sop) }}" target="_blank" 
                   class="px-2.5 py-1 rounded text-xs font-semibold flex items-center gap-1 transition"
                   style="background: var(--hc-surface-alt); color: var(--hc-text); border: 1px solid var(--hc-border);">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    <span>Open in New Tab</span>
                </a>
            </div>
        </div>

        @if($sop->isPdf())
            <!-- Native PDF Viewer Frame -->
            <div class="w-full rounded-2xl border overflow-hidden shadow-xs bg-slate-900" style="border-color: var(--hc-border);">
                <iframe src="{{ route('sops.file', $sop) }}#toolbar=1&navpanes=1" 
                        class="w-full h-[850px] border-0" 
                        title="PDF Viewer for {{ $sop->title }}">
                </iframe>
            </div>
        @else
            <!-- DOCX Viewer Container -->
            <div class="rounded-2xl border p-6 space-y-4" style="background: var(--hc-surface); border-color: var(--hc-border);">
                <div class="p-4 rounded-xl flex items-start gap-3 text-white" style="background-color: #0284C7;">
                    <i data-lucide="file-check" class="w-5 h-5 shrink-0 mt-0.5"></i>
                    <div class="text-xs">
                        <div class="font-bold text-sm">Automated Document Parsing Active</div>
                        <p class="mt-0.5 opacity-90">
                            This Word document (.{{ $sop->file_type }}) has been extracted into the <strong>Extracted Clinical Guide</strong> tab for formatted high-resolution reading and printing.
                        </p>
                    </div>
                </div>

                <!-- Embedded Doc Viewer Frame via Google Docs Previewer or Direct Render -->
                <div class="w-full rounded-xl border overflow-hidden bg-slate-100 dark:bg-slate-900" style="border-color: var(--hc-border); height: 750px;">
                    <iframe src="https://docs.google.com/viewer?url={{ urlencode(route('sops.file', $sop)) }}&embedded=true"
                            class="w-full h-full border-0"
                            title="Online Document Viewer">
                    </iframe>
                </div>
            </div>
        @endif
    </div>

    <!-- ═══════════════════════════════════════════════════════
         TAB 3: TEST KIT LEAFLETS & PACKAGE INSERTS
         ═══════════════════════════════════════════════════════ -->
    <div x-show="activeTab === 'leaflets'" class="space-y-6">
        
        <!-- Header Banner & Upload Form Trigger -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 rounded-xl border"
             style="background: var(--hc-surface); border-color: var(--hc-border);">
            <div>
                <h3 class="text-sm font-bold" style="color: var(--hc-text);">Diagnostic Test Kit Package Inserts</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Original manufacturer product leaflets, test procedures, reference ranges, and calibration guides. Click any leaflet photo to zoom.
                </p>
            </div>
            
            <!-- Upload Leaflet Image Form -->
            <form action="{{ route('sops.leaflets.upload', $sop) }}" method="POST" enctype="multipart/form-data" 
                  class="flex items-center gap-2">
                @csrf
                <label class="px-3 py-1.5 rounded-lg text-xs font-bold text-white cursor-pointer transition flex items-center gap-1.5 shadow-xs"
                       style="background-color: #059669;">
                    <i data-lucide="camera" class="w-4 h-4"></i>
                    <span>+ Add Leaflet Photo</span>
                    <input type="file" name="leaflets[]" multiple accept="image/*" class="hidden" onchange="this.form.submit()">
                </label>
            </form>
        </div>

        <!-- Leaflet Images Grid -->
        @if(!empty($sop->leaflet_images) && count($sop->leaflet_images) > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($sop->leaflet_images as $index => $imgPath)
                    <div class="rounded-xl border overflow-hidden shadow-xs group transition hover:shadow-md"
                         style="background: var(--hc-surface); border-color: var(--hc-border);">
                        
                        <!-- Image Container with Zoom Trigger -->
                        <div class="relative bg-slate-900 cursor-pointer overflow-hidden aspect-4/3 flex items-center justify-center"
                             @click="lightboxOpen = true; lightboxImage = '{{ asset('storage/' . $imgPath) }}'; lightboxTitle = 'Leaflet Insert #{{ $index + 1 }} - {{ $sop->title }}';">
                            <img src="{{ asset('storage/' . $imgPath) }}" 
                                 alt="Kit Leaflet #{{ $index + 1 }}" 
                                 class="w-full h-full object-contain group-hover:scale-105 transition duration-200">
                            
                            <!-- Hover Overlay -->
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2 text-white">
                                <span class="p-2 rounded-full" style="background-color: #124E78;">
                                    <i data-lucide="zoom-in" class="w-5 h-5"></i>
                                </span>
                                <span class="text-xs font-bold">Inspect Photo</span>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="p-3 flex items-center justify-between text-xs" style="border-top: 1px solid var(--hc-border);">
                            <span class="font-semibold" style="color: var(--hc-text);">Leaflet Page #{{ $index + 1 }}</span>
                            <a href="{{ asset('storage/' . $imgPath) }}" target="_blank" 
                               class="text-xs font-bold hover:underline flex items-center gap-1"
                               style="color: #124E78;">
                                <span>High-Res</span>
                                <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="rounded-2xl border p-12 text-center" style="background: var(--hc-surface); border-color: var(--hc-border);">
                <div class="w-14 h-14 rounded-2xl mx-auto flex items-center justify-center text-white mb-3" style="background-color: #64748B;">
                    <i data-lucide="images" class="w-7 h-7"></i>
                </div>
                <h4 class="text-base font-bold" style="color: var(--hc-text);">No Kit Leaflet Photos Uploaded Yet</h4>
                <p class="text-xs text-slate-500 max-w-md mx-auto mt-1 mb-4">
                    Clinical staff can photograph or scan the physical test kit package insert (leaflet) and attach it to this SOP for immediate laboratory cross-reference.
                </p>
                <form action="{{ route('sops.leaflets.upload', $sop) }}" method="POST" enctype="multipart/form-data" class="inline-block">
                    @csrf
                    <label class="px-4 py-2 rounded-xl text-xs font-bold text-white cursor-pointer transition inline-flex items-center gap-2 shadow-xs"
                           style="background-color: #059669;">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        <span>Upload First Leaflet Photo</span>
                        <input type="file" name="leaflets[]" multiple accept="image/*" class="hidden" onchange="this.form.submit()">
                    </label>
                </form>
            </div>
        @endif
    </div>

    <!-- ═══════════════════════════════════════════════════════
         FULLSCREEN LIGHTBOX MODAL FOR HIGH-RES LEAFLETS
         ═══════════════════════════════════════════════════════ -->
    <div x-show="lightboxOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="lightboxOpen = false"
         class="fixed inset-0 z-50 flex flex-col bg-black/90 backdrop-blur-xs p-4 sm:p-6"
         style="display: none;">
        
        <!-- Lightbox Bar -->
        <div class="flex items-center justify-between text-white pb-3 border-b border-white/10 shrink-0">
            <div class="flex items-center gap-2">
                <i data-lucide="file-image" class="w-5 h-5 text-emerald-400"></i>
                <span class="text-sm font-bold truncate max-w-xl" x-text="lightboxTitle"></span>
            </div>
            <div class="flex items-center gap-3">
                <a :href="lightboxImage" target="_blank" class="px-3 py-1 rounded text-xs font-bold bg-white/20 hover:bg-white/30 text-white transition flex items-center gap-1">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    <span>Full Resolution</span>
                </a>
                <button @click="lightboxOpen = false" class="p-1 rounded-full hover:bg-white/20 text-white transition">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
        </div>

        <!-- Lightbox Image Stage -->
        <div class="flex-1 flex items-center justify-center p-2 sm:p-4 overflow-auto" @click.self="lightboxOpen = false">
            <img :src="lightboxImage" 
                 alt="Enlarged Leaflet Inspection" 
                 class="max-w-full max-h-[85vh] object-contain rounded-lg shadow-2xl border border-white/10">
        </div>
    </div>

</div>
@endsection
