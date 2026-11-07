@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Top Header Banner (Single Solid Colors - NO Gradients) --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-md bg-[#124E78] text-white font-mono font-bold text-xs uppercase tracking-wider">
                    Clinical Governance Portal
                </span>
                <span class="px-2 py-0.5 rounded-md bg-[#059669] text-white font-bold text-[11px]">
                    ISO 15189 / JCI Compliant
                </span>
                @if($isSuperAdmin)
                    <span class="px-2 py-0.5 rounded-md {{ $isPortalEnabled ? 'bg-[#10B981] text-white' : 'bg-[#DC2626] text-white' }} font-bold text-[11px] font-mono">
                        Portal: {{ $isPortalEnabled ? 'ENABLED (Visible to Staff)' : 'DISABLED (Hidden from Staff & Admin)' }}
                    </span>
                @endif
            </div>

            <h1 class="text-xl lg:text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1.5 flex items-center gap-2">
                <i data-lucide="book-open-check" class="w-6 h-6 text-[#124E78] dark:text-sky-400"></i>
                <span>Standard Operating Procedures (SOPs)</span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Clinical test instructions, analyzer manuals, equipment calibration protocols, and kit package inserts.
            </p>
        </div>

        {{-- Super Admin Controls & Upload Button --}}
        <div class="flex flex-wrap items-center gap-2">
            @if($isSuperAdmin)
                {{-- Super Admin Portal Master Switch --}}
                <form action="{{ route('superadmin.sops.toggle') }}" method="POST" class="inline"
                      onsubmit="return confirm('Change SOP Portal visibility? When disabled, administrators and staff cannot access or view SOPs.');">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition shadow-xs {{ $isPortalEnabled ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                        <i data-lucide="power" class="w-3.5 h-3.5"></i>
                        <span>{{ $isPortalEnabled ? 'Turn Portal OFF' : 'Turn Portal ON' }}</span>
                    </button>
                </form>
            @endif

            <a href="{{ route('sops.create') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-[#124E78] hover:bg-[#0e3f61] text-white transition shadow-xs">
                <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                <span>Upload New SOP</span>
            </a>
        </div>
    </div>

    {{-- Filter Bar & Search --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs space-y-3">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            {{-- Department Filter Pills (Single Solid Colors) --}}
            <div class="flex items-center gap-1.5 overflow-x-auto text-xs font-bold pb-1 lg:pb-0">
                <a href="{{ route('sops.index', ['department' => 'all', 'search' => $search]) }}"
                   class="px-3 py-1.5 rounded-lg transition whitespace-nowrap {{ $selectedDept === 'all' ? 'bg-[#124E78] text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    All SOPs ({{ $deptCounts['all'] }})
                </a>
                <a href="{{ route('sops.index', ['department' => 'laboratory', 'search' => $search]) }}"
                   class="px-3 py-1.5 rounded-lg transition whitespace-nowrap {{ $selectedDept === 'laboratory' ? 'bg-[#7C3AED] text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    Laboratory ({{ $deptCounts['laboratory'] }})
                </a>
                <a href="{{ route('sops.index', ['department' => 'pharmacy', 'search' => $search]) }}"
                   class="px-3 py-1.5 rounded-lg transition whitespace-nowrap {{ $selectedDept === 'pharmacy' ? 'bg-[#10B981] text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    Pharmacy ({{ $deptCounts['pharmacy'] }})
                </a>
                <a href="{{ route('sops.index', ['department' => 'emergency', 'search' => $search]) }}"
                   class="px-3 py-1.5 rounded-lg transition whitespace-nowrap {{ $selectedDept === 'emergency' ? 'bg-[#D97706] text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    Emergency ({{ $deptCounts['emergency'] }})
                </a>
                <a href="{{ route('sops.index', ['department' => 'inpatient', 'search' => $search]) }}"
                   class="px-3 py-1.5 rounded-lg transition whitespace-nowrap {{ $selectedDept === 'inpatient' ? 'bg-[#0EA5E9] text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    Inpatient ({{ $deptCounts['inpatient'] }})
                </a>
                <a href="{{ route('sops.index', ['department' => 'reception', 'search' => $search]) }}"
                   class="px-3 py-1.5 rounded-lg transition whitespace-nowrap {{ $selectedDept === 'reception' ? 'bg-[#2563EB] text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    Reception ({{ $deptCounts['reception'] }})
                </a>
            </div>

            {{-- Search Bar --}}
            <form action="{{ route('sops.index') }}" method="GET" class="flex items-center gap-2 shrink-0">
                <input type="hidden" name="department" value="{{ $selectedDept }}">
                <div class="relative w-full sm:w-64">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by test, code, analyzer..."
                           class="w-full pl-9 pr-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-xs font-medium focus:outline-hidden">
                </div>
                @if($search)
                    <a href="{{ route('sops.index', ['department' => $selectedDept]) }}" class="btn-ghost btn-xs text-xs font-bold text-slate-500">
                        Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- SOP Grid Cards --}}
    @if($sops->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($sops as $sop)
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between space-y-4 hover:border-slate-300 dark:hover:border-slate-700 transition">
                    <div class="space-y-3">
                        {{-- Top Badge Strip --}}
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2.5 py-0.5 rounded-md font-mono text-xs font-bold {{ $sop->getDepartmentBadgeColor() }}">
                                {{ $sop->sop_code }}
                            </span>
                            <div class="flex items-center gap-1.5">
                                <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 uppercase">
                                    {{ $sop->file_type }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[11px] font-mono font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500">
                                    v{{ $sop->version }}
                                </span>
                            </div>
                        </div>

                        {{-- SOP Title --}}
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white leading-snug">
                                <a href="{{ route('sops.show', $sop) }}" class="hover:text-[#124E78] dark:hover:text-sky-400 transition">
                                    {{ $sop->title }}
                                </a>
                            </h3>
                            <div class="flex items-center gap-2 mt-1 text-xs text-slate-500">
                                <span class="font-medium text-slate-700 dark:text-slate-300">{{ ucfirst($sop->department) }}</span>
                                <span>&bull;</span>
                                <span class="text-slate-400">{{ $sop->category }}</span>
                            </div>
                        </div>

                        {{-- Summary Excerpt --}}
                        <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-3 leading-relaxed">
                            {{ $sop->extracted_summary ?: 'Standard clinical operating procedure protocol verified by medical directorate.' }}
                        </p>

                        {{-- Metadata Chips: Leaflets & Downloadability --}}
                        <div class="pt-2 flex flex-wrap items-center gap-1.5 text-[11px]">
                            @if(!empty($sop->leaflet_images) && count($sop->leaflet_images) > 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 font-semibold">
                                    <i data-lucide="image" class="w-3 h-3"></i>
                                    <span>{{ count($sop->leaflet_images) }} Kit Leaflet(s)</span>
                                </span>
                            @endif

                            @if($sop->is_downloadable)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 font-semibold">
                                    <i data-lucide="download" class="w-3 h-3"></i>
                                    <span>Download Allowed</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 font-semibold">
                                    <i data-lucide="lock" class="w-3 h-3"></i>
                                    <span>View-Only (Restricted)</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Card Footer & Actions --}}
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                        <span class="text-[11px] font-mono text-slate-400">
                            {{ $sop->getFormattedFileSize() }}
                        </span>

                        <div class="flex items-center gap-1.5">
                            @if($sop->canBeDownloadedBy(auth()->user()))
                                <a href="{{ route('sops.download', $sop) }}" 
                                   class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-600 dark:text-slate-300 transition"
                                   title="Download File">
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                </a>
                            @endif

                            <a href="{{ route('sops.show', $sop) }}" 
                               class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-[#124E78] hover:bg-[#0e3f61] text-white transition shadow-xs">
                                <span>Read Protocol</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </a>

                            @if($isSuperAdmin)
                                {{-- Super Admin Quick Toggle for Download --}}
                                <form action="{{ route('sops.toggle-download', $sop) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="p-1.5 rounded-lg {{ $sop->is_downloadable ? 'text-emerald-600 hover:bg-emerald-50' : 'text-rose-600 hover:bg-rose-50' }} transition"
                                            title="{{ $sop->is_downloadable ? 'Click to make View-Only' : 'Click to allow Download' }}">
                                        <i data-lucide="{{ $sop->is_downloadable ? 'unlock' : 'lock' }}" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $sops->links() }}
        </div>
    @else
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-12 text-center space-y-3">
            <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 mx-auto flex items-center justify-center">
                <i data-lucide="file-question" class="w-6 h-6"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">No SOP documents found</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">
                No Standard Operating Procedures match your selected department or search terms. Upload a new SOP document to build the portal repository.
            </p>
            <div class="pt-2">
                <a href="{{ route('sops.create') }}" class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-[#124E78] text-white shadow-xs">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Upload First SOP</span>
                </a>
            </div>
        </div>
    @endif

</div>
@endsection
