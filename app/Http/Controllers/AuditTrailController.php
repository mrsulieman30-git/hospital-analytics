<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\DepartmentForm;
use App\Models\HospitalSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuditTrailController extends Controller
{
    public function index(Request $request): View
    {
        $viewer = Auth::user();
        if (! $viewer->isAdmin() && ! $viewer->isAnalyticsOfficer()) {
            abort(403, 'Unauthorized access to Audit Trail.');
        }

        $query = AuditLog::with('user')->visibleTo($viewer)->latest('created_at');

        // Search query
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Action filter
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Department filter
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        // User filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Date Presets & Custom Date
        if ($request->filled('preset')) {
            switch ($request->preset) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'yesterday':
                    $query->whereDate('created_at', today()->subDay());
                    break;
                case '7_days':
                    $query->where('created_at', '>=', today()->subDays(7));
                    break;
                case 'month':
                    $query->where('created_at', '>=', today()->startOfMonth());
                    break;
            }
        } elseif ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->paginate(25)->withQueryString();

        $departments = DepartmentForm::where('is_active', true)->get();
        $users = User::visibleTo($viewer)->orderBy('name')->get();
        $hospital = HospitalSetting::current();

        $actions = [
            'SUBMIT' => 'Report Submissions',
            'APPROVE' => 'Report Approvals',
            'UPDATE' => 'Record Updates',
            'CREATE' => 'Drafts & Creation',
            'RETURN' => 'Returned for Revision',
            'LOGIN' => 'User Authentication',
            'DELETE' => 'Deletions',
            'EXPORT' => 'Data Exports',
        ];

        // Summary Statistics for the Admin
        $today = today();
        $stats = [
            'total_today' => AuditLog::visibleTo($viewer)->whereDate('created_at', $today)->count(),
            'submissions_today' => AuditLog::visibleTo($viewer)->whereIn('action', ['SUBMIT', 'APPROVE'])->whereDate('created_at', $today)->count(),
            'updates_today' => AuditLog::visibleTo($viewer)->where('action', 'UPDATE')->whereDate('created_at', $today)->count(),
            'active_users_today' => AuditLog::visibleTo($viewer)->whereDate('created_at', $today)->distinct('user_id')->count('user_id'),
        ];

        return view('audit.index', compact('logs', 'departments', 'users', 'actions', 'hospital', 'stats'));
    }
}
