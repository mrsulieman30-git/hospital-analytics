<?php

namespace App\Http\Controllers;

use App\Models\HospitalSetting;
use App\Models\Sop;
use App\Services\SopDocumentParserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SopPortalController extends Controller
{
    public function __construct(
        protected SopDocumentParserService $parserService
    ) {}

    /**
     * Guard to enforce that only Super Admin can see anything when portal is off.
     */
    protected function authorizePortalAccess(): void
    {
        $hospital = HospitalSetting::current();
        $isPortalEnabled = $hospital->extra_metadata['sop_portal_enabled'] ?? true;
        $user = Auth::user();

        // If turned off, non-superadmins receive a stealth 404 (don't even know it exists)
        if (! $isPortalEnabled && (! $user || ! $user->isStealthSuperAdmin())) {
            abort(404);
        }
    }

    /**
     * Guard to ensure only superadmin can modify portal state or configurations.
     */
    protected function authorizeSuperAdminOnly(): void
    {
        $user = Auth::user();
        if (! $user || ! $user->isStealthSuperAdmin()) {
            abort(403, 'Unauthorized. Standard Operating Procedure governance is reserved for Super Admin.');
        }
    }

    /**
     * SOP Portal Directory
     */
    public function index(Request $request): View
    {
        $this->authorizePortalAccess();

        $hospital = HospitalSetting::current();
        $isPortalEnabled = $hospital->extra_metadata['sop_portal_enabled'] ?? true;
        $isSuperAdmin = Auth::user() && Auth::user()->isStealthSuperAdmin();

        $selectedDept = $request->query('department', 'all');
        $search = $request->query('search');

        $query = Sop::query()->whereIn('status', ['active', 'approved']);

        if ($selectedDept !== 'all') {
            $query->where('department', $selectedDept);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('sop_code', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('extracted_summary', 'like', "%{$search}%");
            });
        }

        $sops = $query->latest()->paginate(12)->withQueryString();

        // Department breakdown counts
        $deptCounts = [
            'all' => Sop::whereIn('status', ['active', 'approved'])->count(),
            'laboratory' => Sop::whereIn('status', ['active', 'approved'])->where('department', 'laboratory')->count(),
            'pharmacy' => Sop::whereIn('status', ['active', 'approved'])->where('department', 'pharmacy')->count(),
            'emergency' => Sop::whereIn('status', ['active', 'approved'])->where('department', 'emergency')->count(),
            'inpatient' => Sop::whereIn('status', ['active', 'approved'])->where('department', 'inpatient')->count(),
            'reception' => Sop::whereIn('status', ['active', 'approved'])->where('department', 'reception')->count(),
        ];

        return view('sops.index', compact('sops', 'hospital', 'isPortalEnabled', 'isSuperAdmin', 'selectedDept', 'search', 'deptCounts'));
    }

    /**
     * SOP Viewer (Extracted HTML Guide, Online Doc/PDF Reader, Test Kit Leaflets)
     */
    public function show(Sop $sop): View
    {
        $this->authorizePortalAccess();

        $hospital = HospitalSetting::current();
        $isSuperAdmin = Auth::user() && Auth::user()->isStealthSuperAdmin();

        return view('sops.show', compact('sop', 'hospital', 'isSuperAdmin'));
    }

    /**
     * Upload SOP Form (Super Admin or authorized staff)
     */
    public function create(): View
    {
        $this->authorizePortalAccess();
        $hospital = HospitalSetting::current();
        $isSuperAdmin = Auth::user() && Auth::user()->isStealthSuperAdmin();

        return view('sops.create', compact('hospital', 'isSuperAdmin'));
    }

    /**
     * Store and Parse Uploaded Document or Pasted Template Text
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizePortalAccess();

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'sop_code' => 'nullable|string|max:50|unique:sops,sop_code',
            'department' => 'nullable|string|in:laboratory,pharmacy,emergency,inpatient,reception,general',
            'category' => 'nullable|string|max:100',
            'version' => 'nullable|string|max:50',
            'sop_file' => 'nullable|file|mimes:docx,doc,pdf,txt|max:25600',
            'file' => 'nullable|file|mimes:docx,doc,pdf,txt|max:25600',
            'sop_text' => 'nullable|string',
            'leaflets.*' => 'nullable|image|max:10240',
            'is_downloadable' => 'nullable|boolean',
            'effective_date' => 'nullable|date',
            'review_date' => 'nullable|date',
            'author_name' => 'nullable|string|max:100',
        ]);

        $file = $request->file('sop_file') ?? $request->file('file');
        $sopText = $request->input('sop_text');

        if (! $file && empty(trim($sopText ?? ''))) {
            return back()->withErrors(['sop_file' => 'Please either upload a document file (.docx, .doc, .pdf, .txt) or paste SOP template text.'])->withInput();
        }

        $storedPath = '';
        $origName = '';
        $extension = 'docx';
        $size = 0;

        if ($file) {
            $origName = $file->getClientOriginalName();
            $extension = strtolower($file->getClientOriginalExtension());
            $size = $file->getSize();
            $storedPath = $file->store('sops', 'public');
            $absolutePath = Storage::disk('public')->path($storedPath);
            $parsed = $this->parserService->parse($absolutePath, $extension, $validated['title'] ?? '');
        } else {
            // Direct template text paste
            $parsed = $this->parserService->parseRawText($sopText, $validated['title'] ?? '');

            // Save as text file in sops storage so it can be viewed and downloaded
            $safeSlug = Str::slug($parsed['title'] ?: 'sop_protocol');
            $fileName = ($parsed['metadata']['document_code'] ?? 'SOP').'_'.$safeSlug.'.txt';
            $storedPath = 'sops/'.$fileName;
            Storage::disk('public')->put($storedPath, $sopText);
            $origName = $fileName;
            $extension = 'txt';
            $size = strlen($sopText);
        }

        // Auto-extract metadata if not explicitly provided
        $meta = $parsed['metadata'] ?? [];

        $title = ($validated['title'] ?? null)
            ?: ($parsed['title'] ?: 'Clinical Standard Operating Procedure');

        $sopCode = ! empty($validated['sop_code'] ?? null)
            ? strtoupper(trim($validated['sop_code']))
            : strtoupper(trim($meta['document_code'] ?? ($meta['sop_code'] ?? 'SOP-LAB-'.strtoupper(Str::random(5)))));

        // Ensure unique sop_code if generated
        if (Sop::where('sop_code', $sopCode)->exists()) {
            $sopCode .= '-'.rand(10, 99);
        }

        // Department mapping
        $deptInput = $validated['department'] ?? null;
        if (! $deptInput) {
            $deptMeta = strtolower($meta['department'] ?? '');
            if (str_contains($deptMeta, 'chemistry') || str_contains($deptMeta, 'pathology') || str_contains($deptMeta, 'lab') || str_contains($deptMeta, 'hematology') || str_contains($deptMeta, 'biochem')) {
                $deptInput = 'laboratory';
            } elseif (str_contains($deptMeta, 'pharmacy')) {
                $deptInput = 'pharmacy';
            } elseif (str_contains($deptMeta, 'emergency')) {
                $deptInput = 'emergency';
            } elseif (str_contains($deptMeta, 'inpatient') || str_contains($deptMeta, 'ward')) {
                $deptInput = 'inpatient';
            } elseif (str_contains($deptMeta, 'reception') || str_contains($deptMeta, 'opd')) {
                $deptInput = 'reception';
            } else {
                $deptInput = 'laboratory';
            }
        }

        // Category mapping
        $category = ($validated['category'] ?? null)
            ?: ($meta['category'] ?? ($meta['kit_brand'] ?? 'Clinical Protocol'));

        // Author mapping
        $authorName = ($validated['author_name'] ?? null)
            ?: ($meta['laboratory_manager'] ?? ($meta['author'] ?? ($meta['author_name'] ?? Auth::user()->name)));

        // Version mapping
        $version = ($validated['version'] ?? null)
            ?: ($meta['version'] ?? '1.0');

        // Handle leaflet images
        $leafletPaths = [];
        if ($request->hasFile('leaflets')) {
            foreach ($request->file('leaflets') as $leaflet) {
                $leafletPaths[] = $leaflet->store('sops/leaflets', 'public');
            }
        }

        $sop = Sop::create([
            'title' => $title,
            'sop_code' => $sopCode,
            'department' => $deptInput,
            'category' => $category,
            'version' => $version,
            'file_path' => $storedPath,
            'file_name' => $origName,
            'file_type' => strtolower($extension),
            'file_size' => $size,
            'is_downloadable' => $request->boolean('is_downloadable', true),
            'extracted_summary' => $parsed['summary'] ?: 'Standard clinical operating procedure protocol.',
            'extracted_html' => $parsed['html'],
            'extracted_metadata' => $parsed['metadata'],
            'leaflet_images' => $leafletPaths,
            'effective_date' => ($validated['effective_date'] ?? null) ?: now()->toDateString(),
            'review_date' => ($validated['review_date'] ?? null) ?: now()->addYear()->toDateString(),
            'author_name' => $authorName,
            'created_by' => Auth::id(),
            'status' => 'active',
        ]);

        return redirect()->route('sops.show', $sop)->with('success', 'SOP protocol parsed and added to repository successfully.');
    }

    /**
     * Upload additional kit leaflet images to an existing SOP
     */
    public function uploadLeaflet(Request $request, Sop $sop): RedirectResponse
    {
        $this->authorizePortalAccess();

        $request->validate([
            'leaflets' => 'required|array',
            'leaflets.*' => 'image|max:10240',
        ]);

        $currentLeaflets = $sop->leaflet_images ?? [];
        foreach ($request->file('leaflets') as $file) {
            $currentLeaflets[] = $file->store('sops/leaflets', 'public');
        }

        $sop->leaflet_images = $currentLeaflets;
        $sop->save();

        return redirect()->route('sops.show', ['sop' => $sop, 'tab' => 'leaflets'])
            ->with('success', 'Kit leaflet image uploaded successfully.');
    }

    /**
     * Download original file (controlled by Super Admin is_downloadable setting)
     */
    public function download(Sop $sop)
    {
        $this->authorizePortalAccess();

        if (! $sop->canBeDownloadedBy(Auth::user())) {
            abort(403, 'Document download is disabled for this procedure by the Medical Directorate.');
        }

        if (! Storage::disk('public')->exists($sop->file_path)) {
            abort(404, 'File not found on server.');
        }

        return Storage::disk('public')->download($sop->file_path, $sop->file_name);
    }

    /**
     * Stream file for the built-in online viewer (PDF or DOCX)
     */
    public function file(Sop $sop): BinaryFileResponse
    {
        $this->authorizePortalAccess();

        if (! Storage::disk('public')->exists($sop->file_path)) {
            abort(404, 'Document file not found.');
        }

        $path = Storage::disk('public')->path($sop->file_path);
        $mime = $sop->isPdf() ? 'application/pdf' : 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.$sop->file_name.'"',
        ]);
    }

    /**
     * Toggle download permission for a specific SOP (Super Admin only)
     */
    public function toggleDownload(Sop $sop): RedirectResponse
    {
        $this->authorizeSuperAdminOnly();

        $sop->is_downloadable = ! $sop->is_downloadable;
        $sop->save();

        $status = $sop->is_downloadable ? 'downloadable' : 'view-only (download disabled)';

        return back()->with('success', "SOP {$sop->sop_code} is now {$status}.");
    }

    /**
     * Toggle the entire SOP Portal ON/OFF globally (Super Admin only)
     */
    public function togglePortal(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdminOnly();

        $hospital = HospitalSetting::current();
        $meta = $hospital->extra_metadata ?? [];
        $current = $meta['sop_portal_enabled'] ?? true;
        $newState = ! $current;

        $meta['sop_portal_enabled'] = $newState;
        $hospital->extra_metadata = $meta;
        $hospital->save();

        $stateText = $newState ? 'ENABLED for all hospital staff' : 'DISABLED & HIDDEN from all users and administrators';

        return redirect()->route('sops.index')->with('success', "SOPs Section has been {$stateText}.");
    }

    /**
     * Delete an SOP (Super Admin only)
     */
    public function destroy(Sop $sop): RedirectResponse
    {
        $this->authorizeSuperAdminOnly();

        if (Storage::disk('public')->exists($sop->file_path)) {
            Storage::disk('public')->delete($sop->file_path);
        }

        if (! empty($sop->leaflet_images)) {
            foreach ($sop->leaflet_images as $img) {
                if (Storage::disk('public')->exists($img)) {
                    Storage::disk('public')->delete($img);
                }
            }
        }

        $sop->delete();

        return redirect()->route('sops.index')->with('success', 'SOP protocol removed from repository.');
    }
}
