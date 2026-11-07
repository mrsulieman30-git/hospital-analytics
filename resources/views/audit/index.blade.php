@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Header & Context -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="hc-badge hc-badge-brand">Governance &amp; Accountability</span>
                <span class="text-xs text-[var(--hc-text-muted)] font-mono">ISO 15189 / 27001 Clinical Audit Trail</span>
            </div>
            <h1 class="text-2xl font-black text-[var(--hc-text)] tracking-tight mt-1">
                Clinical &amp; Operational Audit Trail
            </h1>
            <p class="text-xs text-[var(--hc-text-muted)] mt-0.5">
                Transparent human-readable activity log of all submissions, departmental approvals, and data revisions.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('audit.index') }}" class="btn btn-secondary btn-sm">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                <span>Refresh Log</span>
            </a>
        </div>
    </div>

    <!-- Summary KPI Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="hc-card p-4 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-[var(--hc-text-muted)] block">Today's Total Events</span>
                <span class="text-2xl font-black text-[var(--hc-text)] mt-1 block">{{ number_format($stats['total_today']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-md bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] flex items-center justify-center text-[var(--hc-text)]">
                <i data-lucide="activity" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="hc-card p-4 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-[var(--hc-text-muted)] block">Submissions &amp; Approvals</span>
                <span class="text-2xl font-black text-[var(--hc-success)] mt-1 block">{{ number_format($stats['submissions_today']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-md bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] flex items-center justify-center text-[var(--hc-success)]">
                <i data-lucide="check-check" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="hc-card p-4 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-[var(--hc-text-muted)] block">Data Edits &amp; Revisions</span>
                <span class="text-2xl font-black text-[var(--hc-warning)] mt-1 block">{{ number_format($stats['updates_today']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-md bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] flex items-center justify-center text-[var(--hc-warning)]">
                <i data-lucide="edit-3" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="hc-card p-4 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-[var(--hc-text-muted)] block">Active Staff Today</span>
                <span class="text-2xl font-black text-[var(--hc-purple)] mt-1 block">{{ number_format($stats['active_users_today']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-md bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] flex items-center justify-center text-[var(--hc-purple)]">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Human Filters Toolbar -->
    <div class="hc-card space-y-4">
        <form action="{{ route('audit.index') }}" method="GET" class="space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <!-- Search Input -->
                <div class="md:col-span-4 relative">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-[var(--hc-text-muted)]"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by staff name, action, or keyword..."
                           class="hc-input pl-9 text-xs w-full">
                </div>

                <!-- Action Dropdown -->
                <div class="md:col-span-3">
                    <select name="action" class="hc-input hc-select text-xs w-full">
                        <option value="">All Action Types</option>
                        @foreach($actions as $code => $label)
                            <option value="{{ $code }}" {{ request('action') === $code ? 'selected' : '' }}>
                                {{ $label }} ({{ $code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Department Dropdown -->
                <div class="md:col-span-3">
                    <select name="department" class="hc-input hc-select text-xs w-full">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->slug }}" {{ request('department') === $dept->slug ? 'selected' : '' }}>
                                {{ $dept->name_en }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- User Dropdown -->
                <div class="md:col-span-2">
                    <select name="user_id" class="hc-input hc-select text-xs w-full">
                        <option value="">All Staff</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Date Preset Pills & Custom Date -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-[var(--hc-border)]">
                <div class="flex flex-wrap items-center gap-1.5 text-xs">
                    <span class="text-[var(--hc-text-muted)] font-semibold mr-1">Period:</span>
                    <button type="submit" name="preset" value=""
                            class="px-2.5 py-1 rounded text-xs font-semibold transition {{ !request('preset') && !request('date') ? 'bg-[var(--color-primary)] text-white' : 'bg-[var(--hc-surface-alt)] text-[var(--hc-text-muted)] hover:text-[var(--hc-text)]' }}">
                        All Time
                    </button>
                    <button type="submit" name="preset" value="today"
                            class="px-2.5 py-1 rounded text-xs font-semibold transition {{ request('preset') === 'today' ? 'bg-[var(--color-primary)] text-white' : 'bg-[var(--hc-surface-alt)] text-[var(--hc-text-muted)] hover:text-[var(--hc-text)]' }}">
                        Today
                    </button>
                    <button type="submit" name="preset" value="yesterday"
                            class="px-2.5 py-1 rounded text-xs font-semibold transition {{ request('preset') === 'yesterday' ? 'bg-[var(--color-primary)] text-white' : 'bg-[var(--hc-surface-alt)] text-[var(--hc-text-muted)] hover:text-[var(--hc-text)]' }}">
                        Yesterday
                    </button>
                    <button type="submit" name="preset" value="7_days"
                            class="px-2.5 py-1 rounded text-xs font-semibold transition {{ request('preset') === '7_days' ? 'bg-[var(--color-primary)] text-white' : 'bg-[var(--hc-surface-alt)] text-[var(--hc-text-muted)] hover:text-[var(--hc-text)]' }}">
                        Past 7 Days
                    </button>
                    <button type="submit" name="preset" value="month"
                            class="px-2.5 py-1 rounded text-xs font-semibold transition {{ request('preset') === 'month' ? 'bg-[var(--color-primary)] text-white' : 'bg-[var(--hc-surface-alt)] text-[var(--hc-text-muted)] hover:text-[var(--hc-text)]' }}">
                        This Month
                    </button>

                    <div class="flex items-center gap-1.5 ml-2">
                        <span class="text-[var(--hc-text-muted)] text-[11px]">Specific Date:</span>
                        <input type="date" name="date" value="{{ request('date') }}"
                               class="hc-input text-xs py-0.5 px-2 font-mono" onchange="this.form.submit()">
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Apply Filters</span>
                    </button>
                    <a href="{{ route('audit.index') }}" class="btn btn-secondary btn-sm">
                        <span>Reset</span>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Human-Readable Audit Trail Entries -->
    <div class="hc-card p-0 overflow-hidden">
        <div class="px-5 py-3.5 bg-[var(--hc-surface-alt)] border-b border-[var(--hc-border)] flex items-center justify-between text-xs">
            <span class="font-bold text-[var(--hc-text)] uppercase tracking-wider text-[11px]">
                Showing {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} Audit Events
            </span>
            <span class="text-[var(--hc-text-muted)] font-mono text-[11px]">Read-only Integrity Ledger</span>
        </div>

        <div class="divide-y divide-[var(--hc-border)]">
            @forelse($logs as $log)
                @php
                    $actionBg = match($log->action) {
                        'SUBMIT' => 'hc-badge-brand',
                        'APPROVE' => 'hc-badge-success',
                        'UPDATE' => 'hc-badge-warning',
                        'RETURN' => 'hc-badge-danger',
                        'LOGIN' => 'hc-badge-purple',
                        default => 'hc-badge-neutral',
                    };
                    $actionIcon = match($log->action) {
                        'SUBMIT' => 'send',
                        'APPROVE' => 'shield-check',
                        'UPDATE' => 'edit-3',
                        'RETURN' => 'rotate-ccw',
                        'LOGIN' => 'key-round',
                        'DELETE' => 'trash-2',
                        default => 'clock',
                    };
                    $humanChanges = $log->human_changes;
                @endphp

                <div x-data="{ showChanges: false }" class="p-4 sm:p-5 hover:bg-[var(--hc-surface-alt)] transition">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                        
                        <!-- Left: Action Icon + Human Narrative Sentence -->
                        <div class="flex items-start gap-3.5">
                            <div class="w-9 h-9 rounded-md bg-[var(--hc-surface)] border border-[var(--hc-border)] flex items-center justify-center shrink-0 mt-0.5 text-[var(--hc-text)]">
                                <i data-lucide="{{ $actionIcon }}" class="w-4 h-4"></i>
                            </div>

                            <div class="space-y-1">
                                <!-- Human-Readable Sentence -->
                                <div class="text-sm font-semibold text-[var(--hc-text)] leading-relaxed">
                                    <span class="font-bold text-[var(--color-primary)]">
                                        {{ $log->user->name ?? 'System' }}
                                    </span>
                                    <span>
                                        @if($log->action === 'SUBMIT')
                                            submitted the official Daily Report in
                                        @elseif($log->action === 'APPROVE')
                                            approved the Daily Report in
                                        @elseif($log->action === 'RETURN')
                                            returned the Daily Report for revision in
                                        @elseif($log->action === 'CREATE')
                                            started a new report draft in
                                        @elseif($log->action === 'UPDATE')
                                            updated data entries in
                                        @elseif($log->action === 'DELETE')
                                            removed records in
                                        @elseif($log->action === 'LOGIN')
                                            {{ str_contains($log->description, 'Quick-switched') ? 'switched session to' : 'logged in securely' }}
                                        @else
                                            performed {{ strtolower($log->action) }} in
                                        @endif
                                    </span>

                                    @if($log->department)
                                        <span class="hc-badge {{ $actionBg }} uppercase text-[10px] ml-1">
                                            {{ ucfirst($log->department) }}
                                        </span>
                                    @endif

                                    @if($log->description && !str_starts_with($log->description, 'User logged') && !str_contains($log->description, 'Quick-switched'))
                                        <span class="text-xs text-[var(--hc-text-muted)] font-normal block sm:inline sm:ml-1">
                                            &bull; {{ $log->description }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Metadata context line -->
                                <div class="flex flex-wrap items-center gap-2 text-[11px] text-[var(--hc-text-muted)] font-mono pt-0.5">
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        <span>{{ $log->created_at->format('M d, Y &bull; H:i:s') }}</span>
                                    </span>
                                    <span>&bull;</span>
                                    <span>{{ $log->created_at->diffForHumans() }}</span>
                                    <span>&bull;</span>
                                    <span>IP: {{ $log->ip_address ?? '127.0.0.1' }}</span>
                                    @if($log->entity_type)
                                        <span>&bull;</span>
                                        <span>Ref: {{ $log->entity_type }} #{{ $log->entity_id }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Right: Action Badge & Change Inspector Button -->
                        <div class="flex items-center gap-2 self-start sm:self-auto shrink-0 pt-1 sm:pt-0">
                            <span class="hc-badge {{ $actionBg }} text-[10px] font-mono">
                                {{ $log->action }}
                            </span>

                            @if(!empty($humanChanges))
                                <button type="button" @click="showChanges = true"
                                        class="btn btn-secondary btn-xs flex items-center gap-1 font-bold">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    <span>View Changes ({{ count($humanChanges) }})</span>
                                </button>

                                <!-- Human Change Inspector Modal (No Raw Code!) -->
                                <div x-show="showChanges" x-cloak
                                     class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4 z-50 text-left"
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100">
                                    <div @click.away="showChanges = false"
                                         class="bg-[var(--hc-surface)] rounded-xl max-w-2xl w-full border border-[var(--hc-border)] shadow-2xl flex flex-col max-h-[85vh]">
                                        
                                        <!-- Modal Header -->
                                        <div class="p-5 border-b border-[var(--hc-border)] flex items-center justify-between">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded bg-[var(--hc-surface-alt)] flex items-center justify-center text-[var(--color-primary)]">
                                                    <i data-lucide="file-check-2" class="w-4 h-4"></i>
                                                </div>
                                                <div>
                                                    <h3 class="text-sm font-bold text-[var(--hc-text)]">
                                                        Detailed Audit Event Record
                                                    </h3>
                                                    <p class="text-xs text-[var(--hc-text-muted)]">
                                                        Recorded by {{ $log->user->name ?? 'System' }} on {{ $log->created_at->format('M d, Y at H:i:s') }}
                                                    </p>
                                                </div>
                                            </div>
                                            <button type="button" @click="showChanges = false" class="btn btn-ghost btn-sm">
                                                <i data-lucide="x" class="w-4 h-4"></i>
                                            </button>
                                        </div>

                                        <!-- Modal Content: Humanized Table of Modifications -->
                                        <div class="p-5 overflow-y-auto space-y-4 text-xs">
                                            <div class="p-3 rounded-md bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] flex items-center justify-between">
                                                <div>
                                                    <span class="text-[11px] font-bold text-[var(--hc-text-muted)] block uppercase">EVENT NARRATIVE</span>
                                                    <span class="font-semibold text-xs text-[var(--hc-text)]">{{ $log->human_narrative }}</span>
                                                </div>
                                                <span class="hc-badge {{ $actionBg }}">{{ $log->action }}</span>
                                            </div>

                                            <div>
                                                <h4 class="text-xs font-bold text-[var(--hc-text)] uppercase tracking-wider mb-2">
                                                    Specific Changes &amp; Entries Recorded:
                                                </h4>

                                                <div class="space-y-2">
                                                    @foreach($humanChanges as $change)
                                                        @if($change['type'] === 'modified')
                                                            <div class="p-2.5 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                                                <span class="font-bold text-[var(--hc-text)]">{{ $change['field'] }}</span>
                                                                <div class="flex items-center gap-2 font-mono text-xs">
                                                                    <span class="px-2 py-0.5 rounded bg-[var(--hc-surface-alt)] text-[var(--hc-danger)] line-through">
                                                                        {{ $change['old'] !== '' ? $change['old'] : '(empty)' }}
                                                                    </span>
                                                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-[var(--hc-text-muted)]"></i>
                                                                    <span class="px-2 py-0.5 rounded bg-[var(--hc-surface-alt)] text-[var(--hc-success)] font-bold">
                                                                        {{ $change['new'] !== '' ? $change['new'] : '(empty)' }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <div class="p-2.5 rounded border border-[var(--hc-border)] bg-[var(--hc-surface)] flex items-center justify-between">
                                                                <span class="font-semibold text-[var(--hc-text)]">{{ $change['field'] }}</span>
                                                                <span class="font-bold text-[var(--color-primary)] font-mono">{{ $change['new'] }}</span>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Footer -->
                                        <div class="p-4 bg-[var(--hc-surface-alt)] border-t border-[var(--hc-border)] flex items-center justify-between text-xs">
                                            <span class="text-[var(--hc-text-muted)] font-mono">Audit ID: #{{ $log->id }}</span>
                                            <button type="button" @click="showChanges = false" class="btn btn-secondary btn-sm">
                                                <span>Close Inspector</span>
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            @empty
                <div class="p-12 text-center space-y-3">
                    <div class="w-12 h-12 rounded-full bg-[var(--hc-surface-alt)] border border-[var(--hc-border)] mx-auto flex items-center justify-center text-[var(--hc-text-muted)]">
                        <i data-lucide="clipboard-x" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-sm font-bold text-[var(--hc-text)]">No Audit Logs Found</h3>
                    <p class="text-xs text-[var(--hc-text-muted)] max-w-sm mx-auto">
                        No activity records matched your active filters. Try broadening your date range or clearing search criteria.
                    </p>
                    <a href="{{ route('audit.index') }}" class="btn btn-secondary btn-sm inline-flex">
                        <span>Clear All Filters</span>
                    </a>
                </div>
            @endforelse
        </div>

        @if($logs->hasPages())
            <div class="px-5 py-3 border-t border-[var(--hc-border)] bg-[var(--hc-surface-alt)]">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
