@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-mono font-bold px-2.5 py-0.5 rounded-full bg-brand-primary/10 text-brand-primary border border-brand-primary/20">
                    Programmable Form Engine
                </span>
                <span class="text-xs text-slate-500 font-medium">No-Code Department Form Builder</span>
            </div>
            <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight mt-1">
                Department Forms & Cadence Settings
            </h2>
        </div>
    </div>

    <!-- Department Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($departments as $dept)
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs flex flex-col justify-between space-y-4">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-0.5 rounded-full font-mono text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                            {{ $dept->form_code }}
                        </span>
                        <span class="text-[11px] font-mono font-semibold px-2 py-0.5 rounded-md {{ $dept->cadence === '24_hour_daily' ? 'bg-cyan-500/10 text-cyan-600' : 'bg-amber-500/10 text-amber-600' }}">
                            {{ $dept->cadence === '24_hour_daily' ? '24-Hour Daily' : 'Shift-Based' }}
                        </span>
                    </div>

                    <h3 class="text-base font-bold text-slate-900 dark:text-white">
                        {{ $dept->name_en }}
                    </h3>
                    <p class="text-xs text-slate-500 font-medium">
                        {{ $dept->name_so }}
                    </p>

                    <div class="pt-2 text-xs space-y-1 text-slate-600 dark:text-slate-400">
                        <div>
                            <strong>Shifts:</strong> 
                            <span class="font-mono">{{ implode(', ', $dept->allowed_shifts ?? []) }}</span>
                        </div>
                        @if(!empty($dept->dropdown_options))
                            <div>
                                <strong>Configured Lists:</strong> 
                                <span class="text-slate-500">{{ count($dept->dropdown_options) }} custom option sets</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400">Status: Active</span>
                    <a href="{{ route('settings.forms.edit', $dept) }}" 
                       class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                        <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                        <span>Configure Fields & Lists</span>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
