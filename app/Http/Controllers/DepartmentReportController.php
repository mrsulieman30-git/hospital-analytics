<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\DepartmentForm;
use App\Models\HospitalSetting;
use App\Models\User;
use App\Services\AuditLoggerService;
use App\Services\CrossDepartmentAuditService;
use App\Services\LmsLabReportParserService;
use App\Services\PdfGeneratorService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DepartmentReportController extends Controller
{
    public function __construct(
        protected CrossDepartmentAuditService $auditService
    ) {}

    /**
     * List all reports with filters.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $departmentSlug = $request->query('department');
        $status = $request->query('status');
        $date = $request->query('date');

        $query = DailyReport::with(['creator', 'submitter', 'auditor'])->latest('report_date');

        // Department access control: if user is department supervisor or data entry restricted to a dept
        if ($user->role === 'department_supervisor' || $user->role === 'data_entry') {
            $departmentSlug = $user->department ?? 'pharmacy';
            $query->where('department', $departmentSlug);
            $departments = DepartmentForm::where('slug', $departmentSlug)->get();
        } else {
            if ($departmentSlug) {
                $query->where('department', $departmentSlug);
            }
            $departments = DepartmentForm::where('is_active', true)->orderBy('sort_order')->get();
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($date) {
            $query->where('report_date', $date);
        }

        $reports = $query->paginate(15)->withQueryString();
        $hospital = HospitalSetting::current();

        return view('reports.index', compact('reports', 'departments', 'departmentSlug', 'status', 'date', 'hospital'));
    }

    /**
     * Create a report for a specific department.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        $slug = $request->query('department', $user->department ?? 'pharmacy');

        if (! $user->canAccessDepartment($slug)) {
            abort(403, 'You do not have permission to access reports for another department.');
        }

        $departmentForm = DepartmentForm::forSlug($slug);
        if (! $departmentForm) {
            return redirect()->route('reports.index')->with('error', 'Department not found.');
        }

        $date = $request->query('date', Carbon::today()->toDateString());

        // Automatically determine shift based on hospital schedule:
        // Morning: 08:00 - 14:00 | Noon: 14:00 - 21:00 | Night: 21:00 - 08:00
        $autoShift = DailyReport::determineCurrentShift();
        $defaultShift = $departmentForm->cadence === '24_hour_daily'
            ? ($departmentForm->allowed_shifts[0] ?? '24-Hour Daily Summary')
            : $autoShift;

        if (! empty($departmentForm->allowed_shifts) && ! in_array($defaultShift, $departmentForm->allowed_shifts)) {
            $defaultShift = $departmentForm->allowed_shifts[0];
        }

        $shift = $request->query('shift', $defaultShift);
        if ($shift === 'Noon') {
            $shift = 'Afternoon';
        }

        // Check if report already exists for this date and shift
        $existing = DailyReport::where('department', $slug)
            ->whereDate('report_date', $date)
            ->where('shift', $shift)
            ->first();

        if ($existing) {
            return redirect()->route('reports.edit', $existing)->with('info', "Report for {$slug} ({$date} - {$shift}) already exists. Opening editor.");
        }

        $hospital = HospitalSetting::current();
        $departmentStaff = User::where('department', $slug)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view("reports.forms.{$slug}", [
            'mode' => 'create',
            'report' => null,
            'departmentForm' => $departmentForm,
            'departmentStaff' => $departmentStaff,
            'date' => $date,
            'shift' => $shift,
            'autoShift' => $autoShift,
            'hospital' => $hospital,
        ]);
    }

    /**
     * Store new report.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department' => ['required', 'string'],
            'report_date' => ['required', 'date'],
            'shift' => ['required', 'string'],
            'form_code' => ['required', 'string'],
            'lead_staff_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,submitted'],
            'form_data' => ['required', 'array'],
        ]);

        $user = Auth::user();
        if (! $user->canAccessDepartment($validated['department'])) {
            abort(403, 'Unauthorized department access.');
        }

        if (($validated['shift'] ?? '') === 'Noon') {
            $validated['shift'] = 'Afternoon';
        }

        $now = now();
        $isSubmitting = ($validated['status'] === 'submitted');

        $dateFormatted = Carbon::parse($validated['report_date'])->toDateString();
        $report = DailyReport::where('department', $validated['department'])
            ->whereDate('report_date', $dateFormatted)
            ->where('shift', $validated['shift'])
            ->first();

        if ($report) {
            $report->update([
                'form_code' => $validated['form_code'],
                'lead_staff_name' => $user->name,
                'status' => $validated['status'],
                'submitted_by' => $isSubmitting ? $user->id : $report->submitted_by,
                'submitted_at' => $isSubmitting ? $now : $report->submitted_at,
                'form_data' => $validated['form_data'],
                'summary_metrics' => $this->extractSummaryMetrics($validated['department'], $validated['form_data']),
            ]);
        } else {
            $report = DailyReport::create([
                'department' => $validated['department'],
                'report_date' => $dateFormatted,
                'shift' => $validated['shift'],
                'form_code' => $validated['form_code'],
                'lead_staff_name' => $user->name,
                'status' => $validated['status'],
                'created_by' => $user->id,
                'submitted_by' => $isSubmitting ? $user->id : null,
                'submitted_at' => $isSubmitting ? $now : null,
                'form_data' => $validated['form_data'],
                'summary_metrics' => $this->extractSummaryMetrics($validated['department'], $validated['form_data']),
            ]);
        }

        AuditLoggerService::log(
            $isSubmitting ? 'SUBMIT' : 'CREATE',
            "{$user->name} ".($isSubmitting ? 'submitted' : 'saved draft of')." {$validated['form_code']} ({$validated['report_date']})",
            'DailyReport',
            $report->id,
            $validated['department'],
            ['form_data' => $validated['form_data']]
        );

        return redirect()->route('reports.show', $report)
            ->with('success', $isSubmitting ? 'Report submitted successfully for Analytics Audit.' : 'Draft saved.');
    }

    /**
     * Edit an existing report.
     */
    public function edit(DailyReport $report): View|RedirectResponse
    {
        $user = Auth::user();
        if (! $user->canAccessDepartment($report->department)) {
            abort(403, 'You do not have permission to edit reports for another department.');
        }

        if ($report->status === 'approved' && ! $user->isAnalyticsOfficer()) {
            return redirect()->route('reports.show', $report)->with('info', 'This report has already been approved and is locked for editing.');
        }

        $departmentForm = DepartmentForm::forSlug($report->department);
        $hospital = HospitalSetting::current();
        $departmentStaff = User::where('department', $report->department)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view("reports.forms.{$report->department}", [
            'mode' => 'edit',
            'report' => $report,
            'departmentForm' => $departmentForm,
            'departmentStaff' => $departmentStaff,
            'date' => $report->report_date->toDateString(),
            'shift' => $report->shift,
            'autoShift' => DailyReport::determineCurrentShift(),
            'hospital' => $hospital,
        ]);
    }

    /**
     * Update report data.
     */
    public function update(Request $request, DailyReport $report): RedirectResponse
    {
        $user = Auth::user();
        if (! $user->canAccessDepartment($report->department)) {
            abort(403, 'Unauthorized department access.');
        }

        $validated = $request->validate([
            'shift' => ['nullable', 'string', 'max:50'],
            'lead_staff_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,submitted'],
            'form_data' => ['required', 'array'],
        ]);

        $oldData = $report->form_data;
        $isSubmitting = ($validated['status'] === 'submitted' && $report->status !== 'submitted');

        $newShift = ! empty($validated['shift'])
            ? ($validated['shift'] === 'Noon' ? 'Afternoon' : $validated['shift'])
            : $report->shift;

        $report->update([
            'shift' => $newShift,
            'lead_staff_name' => $report->lead_staff_name ?: $user->name,
            'status' => $validated['status'],
            'submitted_by' => $isSubmitting ? $user->id : $report->submitted_by,
            'submitted_at' => $isSubmitting ? now() : $report->submitted_at,
            'form_data' => $validated['form_data'],
            'summary_metrics' => $this->extractSummaryMetrics($report->department, $validated['form_data']),
        ]);

        AuditLoggerService::log(
            $isSubmitting ? 'SUBMIT' : 'UPDATE',
            "{$user->name} updated {$report->form_code} ({$report->report_date->toDateString()})",
            'DailyReport',
            $report->id,
            $report->department,
            ['before' => $oldData, 'after' => $validated['form_data']]
        );

        return redirect()->route('reports.show', $report)
            ->with('success', $isSubmitting ? 'Report submitted successfully for Analytics Audit.' : 'Report updated successfully.');
    }

    /**
     * View report summary, 3D replica preview, and audit checklist.
     */
    public function show(DailyReport $report): View
    {
        $user = Auth::user();
        if (! $user->canAccessDepartment($report->department)) {
            abort(403, 'You do not have permission to view reports for another department.');
        }

        $hospital = HospitalSetting::current();
        $departmentForm = DepartmentForm::forSlug($report->department);
        $auditResult = $this->auditService->runAuditForDate($report->report_date->toDateString());

        return view('reports.show', compact('report', 'hospital', 'departmentForm', 'auditResult'));
    }

    /**
     * Audit action: Approve report.
     */
    public function approve(Request $request, DailyReport $report): RedirectResponse
    {
        $user = Auth::user();
        if (! $user->isAnalyticsOfficer()) {
            abort(403, 'Only Analytics & Performance Officers can approve departmental reports.');
        }

        $report->update([
            'status' => 'approved',
            'audited_by' => $user->id,
            'audited_at' => now(),
            'audit_feedback' => $request->input('audit_feedback', 'Audited and approved with full operational compliance.'),
        ]);

        AuditLoggerService::log(
            'APPROVE',
            "{$user->name} approved {$report->form_code} ({$report->report_date->toDateString()})",
            'DailyReport',
            $report->id,
            $report->department
        );

        return back()->with('success', "Report {$report->form_code} has been officially approved.");
    }

    /**
     * Audit action: Return for revision.
     */
    public function returnForRevision(Request $request, DailyReport $report): RedirectResponse
    {
        $user = Auth::user();
        if (! $user->isAnalyticsOfficer()) {
            abort(403, 'Only Analytics & Performance Officers can return reports for revision.');
        }

        $request->validate([
            'audit_feedback' => ['required', 'string', 'min:5'],
        ]);

        $report->update([
            'status' => 'returned',
            'audited_by' => $user->id,
            'audited_at' => now(),
            'audit_feedback' => $request->input('audit_feedback'),
        ]);

        AuditLoggerService::log(
            'RETURN',
            "{$user->name} returned {$report->form_code} for revision: {$request->input('audit_feedback')}",
            'DailyReport',
            $report->id,
            $report->department
        );

        return back()->with('warning', "Report {$report->form_code} was returned to supervisor for revision.");
    }

    /**
     * A4 Print & PDF View.
     */
    public function print(DailyReport $report): View
    {
        $user = Auth::user();
        if (! $user->canAccessDepartment($report->department)) {
            abort(403, 'You do not have permission to view or print reports for another department.');
        }

        $hospital = HospitalSetting::current();
        $departmentForm = DepartmentForm::forSlug($report->department);

        return view('reports.print', compact('report', 'hospital', 'departmentForm'));
    }

    /**
     * Dedicated direct fast download action.
     * Generates and downloads the official standalone PDF report named by date, department, and downloading user.
     */
    public function download(DailyReport $report, PdfGeneratorService $pdfGenerator): Response
    {
        $user = Auth::user();
        if (! $user->canAccessDepartment($report->department)) {
            abort(403, 'You do not have permission to download reports for another department.');
        }

        $hospital = HospitalSetting::current();
        $departmentForm = DepartmentForm::forSlug($report->department);
        $downloadTime = now();

        $renderedHtml = view('reports.print', [
            'report' => $report,
            'hospital' => $hospital,
            'departmentForm' => $departmentForm,
            'downloadMeta' => [
                'downloaded_by' => $user->name,
                'downloaded_at' => $downloadTime->format('Y-m-d H:i:s'),
                'user_role' => $user->role,
            ],
        ])->render();

        $pdfBinary = $pdfGenerator->renderHtmlToPdf($renderedHtml);

        // Filename format: YYYY-MM-DD_{department}_{user_name}_{form_code}.pdf
        $dateStr = $report->report_date->toDateString();
        $deptStr = Str::slug($report->department, '_');
        $userSlug = Str::slug($user->name, '_');
        $filename = "{$dateStr}_{$deptStr}_{$userSlug}_{$report->form_code}.pdf";

        // Save persistent copy partitioned by date and department
        try {
            Storage::disk('local')->put("downloads/{$dateStr}/{$deptStr}/{$filename}", $pdfBinary);
        } catch (\Throwable) {
            // Non-blocking if storage path is restricted
        }

        AuditLoggerService::log(
            'DOWNLOAD',
            "{$user->name} downloaded official PDF report {$report->form_code} for {$report->department} ({$dateStr})",
            'DailyReport',
            $report->id,
            $report->department
        );

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Length' => strlen($pdfBinary),
            'Cache-Control' => 'no-cache, private',
        ]);
    }

    /**
     * Parse uploaded hospital LMS lab examination report.
     */
    public function parseLmsReport(Request $request, LmsLabReportParserService $parser): JsonResponse
    {
        $currentDate = $request->input('current_date') ?: now()->toDateString();
        $content = '';

        if ($request->hasFile('lms_file')) {
            $file = $request->file('lms_file');
            $realPath = $file->getRealPath();
            $ext = strtolower($file->getClientOriginalExtension());

            if ($ext === 'xlsx' || $ext === 'xls') {
                $content = $parser->convertXlsxToCsv($realPath);
            } else {
                $raw = (string) file_get_contents($realPath);
                if (str_starts_with($raw, "PK\x03\x04")) {
                    $content = $parser->convertXlsxToCsv($realPath);
                } else {
                    $content = $raw;
                }
            }
        } elseif ($request->filled('content')) {
            $content = (string) $request->input('content');
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No file or content provided for LMS report parsing.',
            ], 422);
        }

        $result = $parser->parseLmsContent($content, $currentDate);

        return response()->json($result);
    }

    /**
     * Quick metric extraction for indexing.
     */
    private function extractSummaryMetrics(string $department, array $data): array
    {
        return match ($department) {
            'pharmacy' => [
                'total_served_rx' => $data['section_a']['total_served_rx'] ?? 0,
                'total_unserved_patients' => $data['section_a']['total_unserved_patients'] ?? 0,
                'fill_rate' => (($data['section_a']['total_served_rx'] ?? 0) + ($data['section_a']['total_unserved_patients'] ?? 0)) > 0
                    ? round((($data['section_a']['total_served_rx'] ?? 0) / (($data['section_a']['total_served_rx'] ?? 0) + ($data['section_a']['total_unserved_patients'] ?? 0))) * 100, 1)
                    : 0,
            ],
            'emergency' => [
                'total_er_cases' => $data['section_a']['total_er_cases'] ?? 0,
                'admitted_to_ipd' => $data['section_a']['disposition_admitted_ipd'] ?? 0,
            ],
            'inpatient' => [
                'current_admissions' => $data['section_a_census']['current_admissions_f'] ?? 0,
                'discharged' => $data['section_a_census']['total_discharged_c'] ?? 0,
            ],
            'laboratory' => [
                'total_patients' => $data['section_a_volume']['total_patients'] ?? 0,
                'total_tests' => $data['section_a_volume']['total_tests'] ?? 0,
                'incomplete_tests_count' => count($data['section_e_lab_operations']['incomplete_tests'] ?? []),
                'equipment_issues_count' => count($data['section_e_lab_operations']['equipment_troubleshooting'] ?? []),
                'calibrated_tests_count' => count($data['section_f_quality_control']['calibrations'] ?? []),
                'supplies_received_count' => count($data['section_g_inventory_supplies']['received_items'] ?? []),
                'ordered_tests_count' => count(array_filter($data['section_c_benches_and_quality']['tests'] ?? [], fn ($t) => ($t['count'] ?? 0) > 0)),
            ],
            'reception' => [
                'total_footfall' => $data['section_a']['total_footfall'] ?? 0,
            ],
            default => [],
        };
    }
}
