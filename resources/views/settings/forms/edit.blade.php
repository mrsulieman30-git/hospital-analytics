@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-mono font-bold px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                    {{ $department->form_code }}
                </span>
                <span class="text-xs text-slate-500 font-medium">{{ $department->name_en }}</span>
            </div>
            <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight mt-1">
                Configure Reporting Cadence & Selectable Dropdown Lists
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Manage pre-configured options so hospital staff can select items with 1 click instead of typing.
            </p>
        </div>
        <a href="{{ route('settings.forms.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-white flex items-center gap-1">
            <span>&larr;</span> Back to Forms
        </a>
    </div>

    <form action="{{ route('settings.forms.update', $department) }}" method="POST" 
          class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs space-y-8"
          x-data="{ showNewOption: false }">
        @csrf
        @method('PUT')

        <!-- Cadence Settings -->
        <div class="space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        Reporting Cadence
                    </h3>
                    <p class="text-xs text-slate-500">Determine whether this department submits per shift or once per 24 hours.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <label class="p-4 rounded-xl border {{ $department->cadence === 'shift_based' ? 'border-brand-primary bg-brand-primary/5 dark:bg-brand-primary/10' : 'border-slate-200 dark:border-slate-700' }} cursor-pointer flex items-start space-x-3 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                    <input type="radio" name="cadence" value="shift_based" {{ $department->cadence === 'shift_based' ? 'checked' : '' }} class="mt-0.5">
                    <div>
                        <div class="font-bold text-slate-900 dark:text-white">Shift-Based Reporting</div>
                        <p class="text-slate-500 mt-1">Allows staff to submit separate logs for Morning, Afternoon, Night, or Full Day shifts.</p>
                    </div>
                </label>

                <label class="p-4 rounded-xl border {{ $department->cadence === '24_hour_daily' ? 'border-brand-primary bg-brand-primary/5 dark:bg-brand-primary/10' : 'border-slate-200 dark:border-slate-700' }} cursor-pointer flex items-start space-x-3 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                    <input type="radio" name="cadence" value="24_hour_daily" {{ $department->cadence === '24_hour_daily' ? 'checked' : '' }} class="mt-0.5">
                    <div>
                        <div class="font-bold text-slate-900 dark:text-white">Fixed 24-Hour Daily Summary</div>
                        <p class="text-slate-500 mt-1">Single consolidated 24-hour summary sheet (Mandatory for Inpatient Bed Census & Lab benches).</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Programmable Dropdown Lists Manager -->
        <div class="space-y-5 pt-4 border-t border-slate-100 dark:border-slate-800">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-2 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <span>Selectable Option Lists</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-brand-primary/10 text-brand-primary font-mono">
                            {{ count($department->dropdown_options ?? []) }} configured
                        </span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        These options populate dropdowns and autocomplete selectors in {{ $department->name_en }} forms so users avoid typing.
                    </p>
                </div>
                <button type="button" @click="showNewOption = !showNewOption"
                        class="px-3.5 py-1.5 rounded-xl font-bold text-xs bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition flex items-center gap-1.5 self-start">
                    <span x-text="showNewOption ? '− Cancel' : '+ Add New Option List'"></span>
                </button>
            </div>

            <!-- New Option List Creator Drawer -->
            <div x-show="showNewOption" x-cloak class="p-4 rounded-xl border border-brand-primary/40 bg-brand-primary/5 dark:bg-brand-primary/10 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="font-bold text-xs text-brand-primary uppercase tracking-wider flex items-center gap-1.5">
                        <span>+ Add New Dropdown Category</span>
                    </div>
                    <span class="text-[10px] text-slate-500">e.g. analyzers, qc_tests, doctors, delay_reasons</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                            List Identifier / Key Name
                        </label>
                        <input type="text" name="new_option_key" placeholder="e.g. analyzers or equipment_names"
                               class="w-full text-xs font-mono p-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Option Items (Comma-separated)
                        </label>
                        <input type="text" name="new_option_values" placeholder="Option 1, Option 2, Option 3..."
                               class="w-full text-xs p-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                    </div>
                </div>
            </div>

            @if(!empty($department->dropdown_options))
                <div class="grid grid-cols-1 gap-4">
                    @foreach($department->dropdown_options as $listKey => $items)
                        @php
                            $itemsArray = is_array($items) ? $items : array_filter(array_map('trim', explode(',', $items)));
                        @endphp
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 space-y-3 text-xs"
                             x-data="{ openEdit: false }">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900 dark:text-white text-xs uppercase tracking-wider">
                                        {{ ucwords(str_replace('_', ' ', $listKey)) }}
                                    </span>
                                    <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold">
                                        {{ count($itemsArray) }} items
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-mono">({{ $listKey }})</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="openEdit = !openEdit"
                                            class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 transition">
                                        <span x-text="openEdit ? 'Collapse' : 'Edit Items'"></span>
                                    </button>
                                </div>
                            </div>

                            <!-- Preview Badges -->
                            <div class="flex flex-wrap gap-1.5" x-show="!openEdit">
                                @foreach($itemsArray as $item)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        {{ $item }}
                                    </span>
                                @endforeach
                            </div>

                            <!-- Editing Area -->
                            <div x-show="openEdit" class="space-y-2 pt-1">
                                <textarea name="dropdown_options[{{ $listKey }}]" rows="3"
                                          class="w-full text-xs font-mono p-3 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-1 focus:ring-brand-primary">{{ implode(', ', $itemsArray) }}</textarea>
                                <div class="flex items-center justify-between text-[11px] text-slate-400">
                                    <span>Separate options with commas or newlines. Emptying the text removes this list.</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="pt-4 flex items-center justify-between border-t border-slate-100 dark:border-slate-800">
            <span class="text-xs text-slate-500">
                Changes apply instantly across all live forms for {{ $department->name_en }}.
            </span>
            <button type="submit" 
                    class="px-6 py-2.5 rounded-xl font-bold text-xs bg-brand-primary text-white shadow-md hover:opacity-90 transition">
                Save Form Options & Cadence
            </button>
        </div>
    </form>

</div>
@endsection
