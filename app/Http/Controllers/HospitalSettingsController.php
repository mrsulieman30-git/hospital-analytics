<?php

namespace App\Http\Controllers;

use App\Models\HospitalSetting;
use App\Services\AuditLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HospitalSettingsController extends Controller
{
    public function edit(): View
    {
        $user = Auth::user();
        if (! $user->isAdmin()) {
            abort(403);
        }

        $hospital = HospitalSetting::current();

        return view('settings.hospital.edit', compact('hospital'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (! $user->isAdmin()) {
            abort(403);
        }

        $hospital = HospitalSetting::current();

        $validated = $request->validate([
            'hospital_name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'primary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'currency' => ['required', 'string', 'max:10'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'submission_deadline' => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $oldData = $hospital->toArray();

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('images', 'public');
            $validated['logo_path'] = 'storage/'.$path;
        }

        unset($validated['logo']);

        $hospital->update($validated);

        AuditLoggerService::log(
            'UPDATE',
            "{$user->name} updated Hospital Profile and Branding Colors",
            'HospitalSetting',
            $hospital->id,
            null,
            ['before' => $oldData, 'after' => $validated]
        );

        return back()->with('success', 'Hospital branding, colors, and configuration updated successfully.');
    }
}
