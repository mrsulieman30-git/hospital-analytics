<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\DepartmentForm;
use App\Models\ExecutiveInsight;
use App\Models\HospitalSetting;
use App\Services\AnalyticsKpiService;
use App\Services\CrossDepartmentAuditService;
use App\Services\LmsLabReportParserService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected AnalyticsKpiService $kpiService,
        protected CrossDepartmentAuditService $auditService,
        protected LmsLabReportParserService $lmsParser
    ) {}

    public function index(Request $request): View
    {
        $hospital = HospitalSetting::current();
        $user = $request->user();
        $isRestricted = $user && ! ($user->isAdmin() || $user->isAnalyticsOfficer());

        $userDepartmentSlug = $isRestricted ? ($user->department ?? 'pharmacy') : null;
        $userDepartment = $userDepartmentSlug ? DepartmentForm::where('slug', $userDepartmentSlug)->first() : null;

        if ($isRestricted && $userDepartmentSlug) {
            $departments = DepartmentForm::where('is_active', true)->where('slug', $userDepartmentSlug)->get();
        } else {
            $departments = DepartmentForm::where('is_active', true)->orderBy('sort_order')->get();
        }

        // Date range handling
        $filter = $request->query('filter', '7_days');
        $today = Carbon::today();

        [$startDate, $endDate, $filterLabel] = match ($filter) {
            'today' => [$today->toDateString(), $today->toDateString(), 'Today'],
            'yesterday' => [$today->copy()->subDay()->toDateString(), $today->copy()->subDay()->toDateString(), 'Yesterday'],
            '30_days' => [$today->copy()->subDays(29)->toDateString(), $today->toDateString(), 'Past 30 Days'],
            'this_month' => [$today->copy()->startOfMonth()->toDateString(), $today->toDateString(), 'This Month'],
            'this_year' => [$today->copy()->startOfYear()->toDateString(), $today->toDateString(), 'This Year'],
            'custom' => [
                $request->query('start_date', $today->copy()->subDays(7)->toDateString()),
                $request->query('end_date', $today->toDateString()),
                'Custom Range',
            ],
            default => [$today->copy()->subDays(6)->toDateString(), $today->toDateString(), 'Past 7 Days'],
        };

        // Compute KPIs & smart alerts
        $metrics = $this->kpiService->getMetricsForPeriod($startDate, $endDate);

        // Run 4-Point cross-departmental audit on the target date (only for executive leadership)
        $targetAuditDate = $request->query('audit_date', $endDate);
        $auditResult = $isRestricted ? null : $this->auditService->runAuditForDate($targetAuditDate);

        // Fetch official published Executive Insight or generate dynamic real-time synthesis
        $officialInsight = $isRestricted ? null : ExecutiveInsight::where('period_date', $targetAuditDate)
            ->latest()
            ->first();

        $dynamicExecutiveSummary = $isRestricted ? null : $this->kpiService->generateExecutiveSummary($metrics, $startDate, $endDate, $filterLabel);

        // Department daily submission grid for the target audit date (strictly scoped if restricted)
        $submissionsQuery = DailyReport::where('report_date', $targetAuditDate);
        if ($isRestricted && $userDepartmentSlug) {
            $submissionsQuery->where('department', $userDepartmentSlug);
        }
        $dailySubmissions = $submissionsQuery->get()->keyBy('department');

        // Department-specific workflow items for restricted staff
        $todayDeptReport = null;
        $targetDeptReport = null;
        $recentDepartmentReports = collect();

        if ($isRestricted && $userDepartmentSlug) {
            $todayDeptReport = DailyReport::where('department', $userDepartmentSlug)
                ->where('report_date', $today->toDateString())
                ->latest()
                ->first();

            $targetDeptReport = $dailySubmissions->get($userDepartmentSlug);

            $recentDepartmentReports = DailyReport::where('department', $userDepartmentSlug)
                ->with(['creator', 'submitter', 'auditor'])
                ->latest('report_date')
                ->take(10)
                ->get();
        }

        // Trend series for Chart.js matching filtered period
        $weeklyTrend = $this->kpiService->getWeeklyTrend($startDate, $endDate);

        // Universal Live Lab Tests Stock Market Chart initial payload (Google Finance 1D default)
        $labTestTimeline = $this->lmsParser->getTimelineData('1d', ['CBC', 'GLUCOSE (RBS)', 'URINE ANALYSIS', 'TYPHOID IGM/IGG', 'TSH'], 'total');
        $labCatalog = LmsLabReportParserService::getFormattedCatalog();
        $labTypesMeta = LmsLabReportParserService::LAB_TYPES;

        return view('dashboard.index', compact(
            'hospital',
            'departments',
            'filter',
            'filterLabel',
            'startDate',
            'endDate',
            'targetAuditDate',
            'metrics',
            'auditResult',
            'officialInsight',
            'dynamicExecutiveSummary',
            'dailySubmissions',
            'weeklyTrend',
            'isRestricted',
            'userDepartmentSlug',
            'userDepartment',
            'todayDeptReport',
            'targetDeptReport',
            'recentDepartmentReports',
            'labTestTimeline',
            'labCatalog',
            'labTypesMeta'
        ));
    }

    /**
     * Live API Endpoint for Universal Stock Market Lab Tests Chart.
     * Supports timeline filters: day, week, month, year, 5_years.
     * Supports single or multi-test selections, or total trend.
     */
    public function labTestChartData(Request $request): JsonResponse
    {
        $period = (string) $request->query('period', 'week');
        $metric = (string) $request->query('metric', 'total');
        $rawTests = $request->query('tests', 'CBC,GLUCOSE (RBS),URINE ANALYSIS,TYPHOID IGM/IGG,TSH');

        $selectedTests = is_array($rawTests)
            ? $rawTests
            : array_values(array_filter(array_map('trim', explode(',', (string) $rawTests))));

        $labType = $request->query('lab_type');

        $data = $this->lmsParser->getTimelineData($period, $selectedTests, $metric, $labType);

        return response()->json($data);
    }
}
