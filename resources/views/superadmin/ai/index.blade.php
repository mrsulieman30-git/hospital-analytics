@extends('layouts.app')

@section('content')
<div x-data="{
    periodType: '{{ $selectedPeriod }}',
    date: '{{ $selectedDate }}',
    loading: false,
    publishing: false,
    publishedSuccess: false,
    
    // AI Generated Report State
    report: {{ json_encode($existingInsight ? [
        'title' => $existingInsight->title,
        'executive_summary' => $existingInsight->executive_summary,
        'key_findings' => $existingInsight->key_findings,
        'bottlenecks_and_root_causes' => $existingInsight->bottlenecks_and_root_causes,
        'action_plan' => $existingInsight->action_plan,
        'medical_benchmarks' => $existingInsight->medical_benchmarks,
    ] : null) }},

    runAnalysis() {
        this.loading = true;
        this.publishedSuccess = false;
        fetch('{{ route('superadmin.deepseek.analyze') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                period_type: this.periodType,
                date: this.date
            })
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                this.report = res.data;
            }
            this.loading = false;
        })
        .catch(err => {
            console.error(err);
            this.loading = false;
        });
    },

    publishToBoardroom() {
        if (!this.report) return;
        this.publishing = true;
        fetch('{{ route('superadmin.deepseek.publish') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                report_type: this.periodType,
                period_date: this.date,
                title: this.report.title,
                executive_summary: this.report.executive_summary,
                key_findings: this.report.key_findings,
                bottlenecks_and_root_causes: this.report.bottlenecks_and_root_causes,
                action_plan: this.report.action_plan,
                medical_benchmarks: this.report.medical_benchmarks
            })
        })
        .then(res => res.json())
        .then(res => {
            this.publishing = false;
            if (res.status === 'success') {
                this.publishedSuccess = true;
            }
        })
        .catch(err => {
            console.error(err);
            this.publishing = false;
        });
    }
}" class="max-w-6xl mx-auto space-y-6">

    <!-- Stealth Super Admin Header -->
    <div class="bg-gradient-to-r from-purple-900 via-slate-900 to-indigo-950 p-6 rounded-3xl border border-purple-800/40 shadow-2xl text-white flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center space-x-1 px-3 py-1 rounded-full text-xs font-mono font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    <span>Super Admin Secret Agent Console</span>
                </span>
                <span class="text-xs text-purple-300 font-mono font-semibold">100% Invisible to Hospital Staff</span>
            </div>
            <h2 class="text-2xl font-black tracking-tight mt-1">
                DeepSeek AI Executive Analyst & Medical Benchmarking
            </h2>
            <p class="text-xs text-slate-300 mt-1 max-w-2xl">
                Synthesizes all 5 hospital departments' daily metrics, runs root-cause bottleneck cross-correlations, simulates clinical international benchmarks, and publishes boardroom-ready narratives.
            </p>
        </div>

        <div class="flex items-center space-x-2 self-start md:self-auto">
            <button @click="runAnalysis()" :disabled="loading"
                    class="px-5 py-2.5 rounded-xl font-bold text-xs bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white shadow-lg flex items-center space-x-2 transition disabled:opacity-50">
                <template x-if="loading">
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                </template>
                <template x-if="!loading">
                    <i data-lucide="brain-circuit" class="w-4 h-4"></i>
                </template>
                <span x-text="loading ? 'Analyzing All Departments...' : '1-Click Deep Hospital Analysis'"></span>
            </button>
        </div>
    </div>

    <!-- Parameter Selectors & DeepSeek Settings Bar -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Controls Column -->
        <div class="lg:col-span-1 space-y-5">
            <!-- Period & Date Selector -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Analysis Target Period</h3>
                
                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block font-semibold mb-1">Cadence / Scope</label>
                        <select x-model="periodType" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white font-semibold">
                            <option value="daily">Daily Departmental Review</option>
                            <option value="weekly">Weekly Operational Bottleneck Brief</option>
                            <option value="monthly">Monthly Executive Scorecard</option>
                            <option value="yearly">Yearly Institutional Review</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold mb-1">Target Evaluation Date</label>
                        <input type="date" x-model="date" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white font-mono font-semibold">
                    </div>

                    <button @click="runAnalysis()" :disabled="loading"
                            class="w-full py-2.5 rounded-xl text-xs font-bold bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900 hover:opacity-90 transition">
                        Run Synthesis On This Date
                    </button>
                </div>
            </div>

            <!-- DeepSeek Model Configuration Form -->
            <form action="{{ route('superadmin.deepseek.config') }}" method="POST" 
                  class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs space-y-4 text-xs">
                @csrf
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                    <h3 class="font-bold uppercase tracking-wider text-slate-500 text-[11px]">DeepSeek API Configuration</h3>
                    <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-purple-500/10 text-purple-600 font-bold">Secret</span>
                </div>

                <div>
                    <label class="block font-semibold mb-1">DeepSeek API Key</label>
                    <input type="password" name="api_key" value="{{ $config->api_key }}" placeholder="sk-..."
                           class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white font-mono text-xs">
                    <span class="text-[10px] text-slate-400">If left empty, high-fidelity local clinical reasoning engine is used.</span>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Model Selector</label>
                    <select name="selected_model" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white font-mono font-bold">
                        <option value="deepseek-chat" {{ $config->selected_model === 'deepseek-chat' ? 'selected' : '' }}>deepseek-chat (V3 Fast)</option>
                        <option value="deepseek-reasoner" {{ $config->selected_model === 'deepseek-reasoner' ? 'selected' : '' }}>deepseek-reasoner (R1 Clinical)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Creativity Temperature</label>
                    <input type="number" step="0.05" min="0" max="1" name="temperature" value="{{ $config->temperature }}"
                           class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white font-mono">
                </div>

                <button type="submit" class="w-full py-2 rounded-xl font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 transition">
                    Save Agent Settings
                </button>
            </form>
        </div>

        <!-- Generated Narrative & Publishing Workspace -->
        <div class="lg:col-span-2 space-y-5">
            
            <template x-if="publishedSuccess">
                <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <i data-lucide="check-check" class="w-4 h-4 text-emerald-600"></i>
                        <span>Insight successfully pushed into official Executive Dashboard & Boardroom reports!</span>
                    </div>
                    <a href="{{ route('dashboard') }}" class="underline font-bold">View in Dashboard &rarr;</a>
                </div>
            </template>

            <template x-if="loading">
                <div class="p-12 text-center bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-4">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-purple-500/10 text-purple-600 flex items-center justify-center animate-pulse">
                        <i data-lucide="brain" class="w-6 h-6"></i>
                    </div>
                    <h4 class="text-base font-bold text-slate-900 dark:text-white">Synthesizing Hospital Operations...</h4>
                    <p class="text-xs text-slate-500 max-w-md mx-auto">
                        Cross-correlating Pharmacy stockouts against Reception doctor consultations, balancing Inpatient census vectors, and benchmarking mortality indicators...
                    </p>
                </div>
            </template>

            <template x-if="!loading && report">
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-xs space-y-6">
                    
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
                        <div>
                            <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-purple-600 px-2 py-0.5 rounded bg-purple-500/10">
                                Boardroom Ready Output
                            </span>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white mt-1" x-text="report.title"></h3>
                        </div>

                        <!-- 1-Click Publish Button -->
                        <button @click="publishToBoardroom()" :disabled="publishing"
                                class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-md flex items-center space-x-1.5 transition disabled:opacity-50 self-start sm:self-auto">
                            <i data-lucide="upload-cloud" class="w-3.5 h-3.5"></i>
                            <span x-text="publishing ? 'Publishing...' : 'Publish as Official Insight'"></span>
                        </button>
                    </div>

                    <!-- Executive Narrative -->
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Executive Summary Narrative</h4>
                        <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 leading-relaxed bg-slate-50 dark:bg-slate-800/40 p-4 rounded-xl border border-slate-200 dark:border-slate-700/60" x-text="report.executive_summary"></p>
                    </div>

                    <!-- Key Findings -->
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Key Operational Findings</h4>
                        <div class="space-y-2">
                            <template x-for="(finding, idx) in report.key_findings" :key="idx">
                                <div class="flex items-start space-x-2 text-xs text-slate-700 dark:text-slate-300">
                                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i>
                                    <span x-text="finding"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Bottlenecks & Root Causes -->
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-rose-500">Cross-Departmental Bottlenecks &amp; Root Causes</h4>
                        <div class="space-y-2">
                            <template x-for="(b, idx) in report.bottlenecks_and_root_causes" :key="idx">
                                <div class="p-3 rounded-xl bg-rose-50/50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900 text-xs text-rose-800 dark:text-rose-200 flex items-start space-x-2">
                                    <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600 shrink-0 mt-0.5"></i>
                                    <span x-text="b"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Action Plan -->
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-blue-500">Prioritized Action Plan</h4>
                        <div class="grid grid-cols-1 gap-2.5">
                            <template x-for="(act, idx) in report.action_plan" :key="idx">
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between text-xs gap-2">
                                    <div>
                                        <span class="font-bold text-slate-900 dark:text-white" x-text="act.priority + ': '"></span>
                                        <span class="text-slate-700 dark:text-slate-300" x-text="act.action"></span>
                                    </div>
                                    <span class="text-[11px] font-mono text-slate-400 font-semibold shrink-0" x-text="'Owner: ' + act.owner"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Medical Benchmarks -->
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-purple-500">International Clinical Quality Benchmarks (WHO / JCI)</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                            <template x-for="(val, label) in report.medical_benchmarks" :key="label">
                                <div class="p-3 bg-purple-50/40 dark:bg-purple-950/20 rounded-xl border border-purple-200 dark:border-purple-900">
                                    <div class="font-bold text-purple-900 dark:text-purple-300" x-text="label"></div>
                                    <div class="font-mono text-slate-700 dark:text-slate-300 text-[11px]" x-text="val"></div>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>
            </template>

            <template x-if="!loading && !report">
                <div class="p-12 text-center bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-3">
                    <i data-lucide="sparkles" class="w-8 h-8 mx-auto text-purple-500"></i>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">Ready to Synthesize Hospital Data</h4>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">
                        Click the "1-Click Deep Hospital Analysis" button above to synthesize data across all 5 departments for the chosen date.
                    </p>
                </div>
            </template>

        </div>

    </div>

</div>
@endsection
