<?php

namespace App\Http\Controllers;

use App\Models\DeepseekConfig;
use App\Models\ExecutiveInsight;
use App\Models\HospitalSetting;
use App\Services\AnalyticsKpiService;
use App\Services\AuditLoggerService;
use App\Services\DeepSeekService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SuperAdminAiController extends Controller
{
    public function __construct(
        protected DeepSeekService $deepSeekService,
        protected AnalyticsKpiService $kpiService
    ) {}

    /**
     * Secret DeepSeek Executive Analyst Console.
     * Accessible ONLY by superadmin@reports.com.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        if (! $user || $user->email !== 'superadmin@reports.com') {
            abort(404); // Stealth 404 to hide endpoint existence completely
        }

        $config = DeepseekConfig::current();
        $hospital = HospitalSetting::current();

        $selectedPeriod = $request->query('period_type', 'daily');
        $selectedDate = $request->query('date', Carbon::today()->toDateString());

        [$startDate, $endDate] = match ($selectedPeriod) {
            'weekly' => [Carbon::parse($selectedDate)->startOfWeek()->toDateString(), Carbon::parse($selectedDate)->endOfWeek()->toDateString()],
            'monthly' => [Carbon::parse($selectedDate)->startOfMonth()->toDateString(), Carbon::parse($selectedDate)->endOfMonth()->toDateString()],
            'yearly' => [Carbon::parse($selectedDate)->startOfYear()->toDateString(), Carbon::parse($selectedDate)->endOfYear()->toDateString()],
            default => [$selectedDate, $selectedDate],
        };

        // Compute metrics for the selected period
        $metrics = $this->kpiService->getMetricsForPeriod($startDate, $endDate);

        // Fetch existing insight if already published for this period
        $existingInsight = ExecutiveInsight::where('report_type', $selectedPeriod)
            ->where('period_date', $selectedDate)
            ->first();

        return view('superadmin.ai.index', compact(
            'config',
            'hospital',
            'selectedPeriod',
            'selectedDate',
            'metrics',
            'existingInsight'
        ));
    }

    /**
     * AJAX endpoint to run One-Click Deep Hospital Data Analysis.
     */
    public function analyze(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user || $user->email !== 'superadmin@reports.com') {
            abort(404);
        }

        $periodType = $request->input('period_type', 'daily');
        $date = $request->input('date', Carbon::today()->toDateString());

        [$startDate, $endDate] = match ($periodType) {
            'weekly' => [Carbon::parse($date)->startOfWeek()->toDateString(), Carbon::parse($date)->endOfWeek()->toDateString()],
            'monthly' => [Carbon::parse($date)->startOfMonth()->toDateString(), Carbon::parse($date)->endOfMonth()->toDateString()],
            'yearly' => [Carbon::parse($date)->startOfYear()->toDateString(), Carbon::parse($date)->endOfYear()->toDateString()],
            default => [$date, $date],
        };

        $metrics = $this->kpiService->getMetricsForPeriod($startDate, $endDate);
        $analysis = $this->deepSeekService->generateExecutiveAnalysis($periodType, $date, $metrics);

        return response()->json([
            'status' => 'success',
            'data' => $analysis,
        ]);
    }

    /**
     * Publish AI Analysis as Official Insight for Management.
     */
    public function publish(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user || $user->email !== 'superadmin@reports.com') {
            abort(404);
        }

        $validated = $request->validate([
            'report_type' => ['required', 'string'],
            'period_date' => ['required', 'date'],
            'title' => ['required', 'string'],
            'executive_summary' => ['required', 'string'],
            'key_findings' => ['nullable', 'array'],
            'bottlenecks_and_root_causes' => ['nullable', 'array'],
            'action_plan' => ['nullable', 'array'],
            'medical_benchmarks' => ['nullable', 'array'],
        ]);

        $insight = $this->deepSeekService->publishInsight($validated, $user->id);

        AuditLoggerService::log(
            'APPROVE',
            "Published Official Executive Performance Insights for {$validated['period_date']} ({$validated['report_type']})",
            'ExecutiveInsight',
            $insight->id
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Insight successfully pushed as official Boardroom Executive Report.',
            'insight_id' => $insight->id,
        ]);
    }

    /**
     * Update DeepSeek API configuration.
     */
    public function updateConfig(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (! $user || $user->email !== 'superadmin@reports.com') {
            abort(404);
        }

        $config = DeepseekConfig::current();

        $validated = $request->validate([
            'api_key' => ['nullable', 'string'],
            'selected_model' => ['required', 'in:deepseek-chat,deepseek-reasoner'],
            'custom_system_prompt' => ['nullable', 'string'],
            'enable_web_search' => ['nullable', 'boolean'],
            'temperature' => ['required', 'numeric', 'between:0,1'],
        ]);

        $config->update([
            'api_key' => $validated['api_key'] ?? $config->api_key,
            'selected_model' => $validated['selected_model'],
            'custom_system_prompt' => $validated['custom_system_prompt'] ?? $config->custom_system_prompt,
            'enable_web_search' => $request->boolean('enable_web_search'),
            'temperature' => $validated['temperature'],
            'updated_by' => $user->id,
        ]);

        return back()->with('success', 'DeepSeek agent settings and model parameters saved.');
    }
}
