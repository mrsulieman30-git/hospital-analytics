<?php

namespace App\Http\Controllers;

use App\Models\DepartmentForm;
use App\Models\HospitalSetting;
use App\Services\AuditLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FormBuilderController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        if (! $user->isAdmin()) {
            abort(403, 'Unauthorized access to Form Settings.');
        }

        $departments = DepartmentForm::orderBy('sort_order')->get();
        $hospital = HospitalSetting::current();

        return view('settings.forms.index', compact('departments', 'hospital'));
    }

    public function edit(DepartmentForm $department): View
    {
        $user = Auth::user();
        if (! $user->isAdmin()) {
            abort(403);
        }

        $hospital = HospitalSetting::current();

        return view('settings.forms.edit', compact('department', 'hospital'));
    }

    public function update(Request $request, DepartmentForm $department): RedirectResponse
    {
        $user = Auth::user();
        if (! $user->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'cadence' => ['required', 'in:shift_based,24_hour_daily'],
            'allowed_shifts' => ['nullable', 'array'],
            'dropdown_options' => ['nullable', 'array'],
            'new_option_key' => ['nullable', 'string', 'max:100'],
            'new_option_values' => ['nullable', 'string'],
            'delete_options' => ['nullable', 'array'],
        ]);

        $oldData = [
            'cadence' => $department->cadence,
            'allowed_shifts' => $department->allowed_shifts,
            'dropdown_options' => $department->dropdown_options,
        ];

        // Process dropdown options: convert comma- or newline-separated strings into trimmed arrays
        $processedOptions = [];
        $rawOptions = $request->input('dropdown_options', []);
        $deleteKeys = $request->input('delete_options', []);

        if (is_array($rawOptions)) {
            foreach ($rawOptions as $key => $values) {
                $cleanKey = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '_', (string) $key)));
                if (empty($cleanKey) || in_array($cleanKey, $deleteKeys, true)) {
                    continue;
                }

                if (is_string($values)) {
                    $items = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $values)), fn ($item) => $item !== ''));
                } elseif (is_array($values)) {
                    $items = array_values(array_filter(array_map('trim', $values), fn ($item) => $item !== ''));
                } else {
                    $items = [];
                }

                if (! empty($items)) {
                    $processedOptions[$cleanKey] = array_values(array_unique($items));
                }
            }
        }

        // Handle dynamically added new option list
        if ($request->filled('new_option_key') && $request->filled('new_option_values')) {
            $newKey = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '_', (string) $request->input('new_option_key'))));
            if (! empty($newKey)) {
                $newItems = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', (string) $request->input('new_option_values'))), fn ($item) => $item !== ''));
                if (! empty($newItems)) {
                    $processedOptions[$newKey] = array_values(array_unique($newItems));
                }
            }
        }

        $department->update([
            'cadence' => $validated['cadence'],
            'allowed_shifts' => $validated['allowed_shifts'] ?? $department->allowed_shifts,
            'dropdown_options' => $processedOptions,
        ]);

        AuditLoggerService::log(
            'UPDATE',
            "{$user->name} updated form settings and selectable option lists for {$department->name_en}",
            'DepartmentForm',
            $department->id,
            $department->slug,
            ['before' => $oldData, 'after' => [
                'cadence' => $department->cadence,
                'dropdown_options' => $processedOptions,
            ]]
        );

        return redirect()->route('settings.forms.index')->with('success', "Form configuration and selectable options for {$department->name_en} updated successfully.");
    }
}
