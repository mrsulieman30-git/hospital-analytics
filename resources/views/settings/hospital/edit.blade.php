@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-mono font-bold px-2.5 py-0.5 rounded-full bg-brand-primary/10 text-brand-primary border border-brand-primary/20">
                    Tenant Customization
                </span>
                <span class="text-xs text-slate-500 font-medium">100% White-Label Hospital Settings</span>
            </div>
            <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight mt-1">
                Hospital Profile, Branding & Brand Colors
            </h2>
        </div>
        <img src="{{ asset($hospital->logo_path ?? 'images/kaafi_logo.png') }}" alt="Hospital Logo" class="h-10 w-auto object-contain rounded">
    </div>

    <form action="{{ route('settings.hospital.update') }}" method="POST" enctype="multipart/form-data" 
          x-data="{
              primaryColor: '{{ $hospital->primary_color }}',
              secondaryColor: '{{ $hospital->secondary_color }}',
              accentColor: '{{ $hospital->accent_color }}'
          }" 
          class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-6">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider border-b border-slate-100 dark:border-slate-800 pb-2">
                Hospital Identity & Contact Details
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Hospital Official Name</label>
                    <input type="text" name="hospital_name" value="{{ old('hospital_name', $hospital->hospital_name) }}" required
                           class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-semibold">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tagline / Mission</label>
                    <input type="text" name="tagline" value="{{ old('tagline', $hospital->tagline) }}"
                           class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Contact Email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $hospital->contact_email) }}"
                           class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-mono">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Contact Phone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $hospital->contact_phone) }}"
                           class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs">
                </div>

                <div class="md:col-span-2">
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Hospital Physical Address</label>
                    <input type="text" name="address" value="{{ old('address', $hospital->address) }}"
                           class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs">
                </div>
            </div>
        </div>

        <!-- 3 Brand Colors Inherited System -->
        <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider border-b border-slate-100 dark:border-slate-800 pb-2">
                3 Brand Colors Inheritance Engine (Strict Theme)
            </h3>
            <p class="text-xs text-slate-500">These 3 hex colors are inherited dynamically across all UI headers, charts, badges, and A4 print reports.</p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <!-- Primary Color -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 space-y-2">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">1. Primary Clinical Color</label>
                    <div class="flex items-center space-x-2">
                        <input type="color" x-model="primaryColor" name="primary_color" class="h-8 w-12 rounded cursor-pointer border-0 bg-transparent">
                        <input type="text" x-model="primaryColor" class="px-2 py-1 font-mono text-xs uppercase rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 w-24">
                    </div>
                    <div class="h-4 rounded" :style="`background-color: ${primaryColor}`"></div>
                </div>

                <!-- Accent Color -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 space-y-2">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">2. Accent / Urgent Red</label>
                    <div class="flex items-center space-x-2">
                        <input type="color" x-model="accentColor" name="accent_color" class="h-8 w-12 rounded cursor-pointer border-0 bg-transparent">
                        <input type="text" x-model="accentColor" class="px-2 py-1 font-mono text-xs uppercase rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 w-24">
                    </div>
                    <div class="h-4 rounded" :style="`background-color: ${accentColor}`"></div>
                </div>

                <!-- Secondary Color -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 space-y-2">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">3. Slate / Neutral Secondary</label>
                    <div class="flex items-center space-x-2">
                        <input type="color" x-model="secondaryColor" name="secondary_color" class="h-8 w-12 rounded cursor-pointer border-0 bg-transparent">
                        <input type="text" x-model="secondaryColor" class="px-2 py-1 font-mono text-xs uppercase rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 w-24">
                    </div>
                    <div class="h-4 rounded" :style="`background-color: ${secondaryColor}`"></div>
                </div>
            </div>
        </div>

        <!-- System Governance Settings -->
        <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider border-b border-slate-100 dark:border-slate-800 pb-2">
                Governance, Submission Cutoff & Currency
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Daily Submission Cutoff Time</label>
                    <input type="text" name="submission_deadline" value="{{ old('submission_deadline', $hospital->submission_deadline) }}" required placeholder="08:30"
                           class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-mono font-bold">
                    <span class="text-[10px] text-slate-400">Strict previous-day report submission cutoff (e.g. 08:30)</span>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Operating Currency</label>
                    <input type="text" name="currency" value="{{ old('currency', $hospital->currency) }}" required
                           class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-bold font-mono">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Upload New Hospital Logo</label>
                    <input type="file" name="logo" accept="image/*"
                           class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 dark:file:bg-slate-800 file:text-slate-700 dark:file:text-slate-200">
                </div>
            </div>
        </div>

        <div class="pt-4 flex justify-end">
            <button type="submit" 
                    class="px-5 py-2.5 rounded-xl font-bold text-xs bg-brand-primary text-white shadow-md hover:opacity-90 transition">
                Save & Apply Hospital Branding
            </button>
        </div>
    </form>

</div>
@endsection
