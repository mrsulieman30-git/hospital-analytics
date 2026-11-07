@php
    $hospital = $hospital ?? \App\Models\HospitalSetting::current();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? (($hospital->hospital_name ?? 'Hospital') . ' - Operations & Analytics Platform') }}</title>

    <!-- Immediate Theme Injection (Prevents Flash) -->
    <script>
        try {
            if (localStorage.getItem('hospital_theme') === 'dark' || (!localStorage.getItem('hospital_theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        } catch (_) {}
    </script>

    <!-- Dynamic Brand Color Inheritance Engine -->
    <style>
        :root {
            --color-primary: {{ $hospital->primary_color ?? '#124E78' }};
            --color-secondary: {{ $hospital->secondary_color ?? '#1E293B' }};
            --color-accent: {{ $hospital->accent_color ?? '#C81D25' }};
        }
    </style>

    <!-- Google Fonts: Inter + JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data class="min-h-screen flex flex-col antialiased selection:bg-brand-primary selection:text-white"
      style="background: var(--hc-surface-alt); color: var(--hc-text);"
      :class="{ 'focus-mode': $store.app.focusMode }">

    @php
        $user = auth()->user();
        $canSeeAllDepartments = $user && ($user->isAdmin() || $user->isAnalyticsOfficer());
        $currentDept = request()->query('department') ?? (isset($report) ? $report->department : null);
        $currentSection = request()->query('section', 'daily-report');
        $isLabPage = $currentDept === 'laboratory';
        $isPharmacyPage = $currentDept === 'pharmacy';
        $isErPage = $currentDept === 'emergency';
        $isIpdPage = $currentDept === 'inpatient';
        $isRecPage = $currentDept === 'reception';
        $isDeptPage = $currentDept !== null;
    @endphp

    <!-- ═══════════════════════════════════════════════════════
         TOP NAVIGATION BAR
         ═══════════════════════════════════════════════════════ -->
    <nav class="topnav no-print" x-show="!$store.app.focusMode"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0">

        <!-- Left: Logo + Hospital Name -->
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 mr-6 shrink-0 text-decoration-none">
            @if(!empty($hospital->logo_path))
                <img src="{{ asset($hospital->logo_path) }}"
                     alt="{{ $hospital->hospital_name }}"
                     class="h-8 w-auto object-contain">
            @else
                <div class="w-8 h-8 rounded bg-brand-primary text-white flex items-center justify-center font-bold text-sm"
                     style="border-radius: 6px;">
                    {{ substr($hospital->hospital_name, 0, 1) }}
                </div>
            @endif
            <span class="hidden sm:block text-sm font-bold tracking-tight" style="color: var(--hc-text);">
                {{ $hospital->hospital_name }}
            </span>
        </a>

        <!-- Center: Navigation Links (Desktop) -->
        <div class="hidden lg:flex items-center gap-1 flex-1">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}"
               class="topnav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                <span x-text="$store.app.t('executive_dashboard', 'Dashboard')">Dashboard</span>
            </a>

            <!-- Reports -->
            <a href="{{ route('reports.index') }}"
               class="topnav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                <span x-text="$store.app.t('daily_submissions', 'Submissions')">Submissions</span>
            </a>

            <!-- Department Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="topnav-link {{ $isDeptPage ? 'active' : '' }}"
                        :class="{ 'active': {{ $isDeptPage ? 'true' : 'false' }} }">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                    <span x-text="$store.app.t('department_data_entry', 'Departments')">Departments</span>
                    <i data-lucide="chevron-down" class="w-3 h-3"></i>
                </button>
                <div x-show="open" @click.away="open = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="dropdown-menu">
                    @if($canSeeAllDepartments || ($user && $user->department === 'pharmacy'))
                        <a href="{{ route('reports.create', ['department' => 'pharmacy']) }}"
                           class="dropdown-item" @click="open = false">
                            <i data-lucide="pill" class="w-4 h-4" style="color: var(--hc-success);"></i>
                            <span x-text="$store.app.t('pharmacy', 'Pharmacy')">Pharmacy</span>
                        </a>
                    @endif
                    @if($canSeeAllDepartments || ($user && $user->department === 'laboratory'))
                        <a href="{{ route('reports.create', ['department' => 'laboratory']) }}"
                           class="dropdown-item" @click="open = false">
                            <i data-lucide="flask-conical" class="w-4 h-4" style="color: var(--hc-purple);"></i>
                            <span x-text="$store.app.t('laboratory', 'Laboratory')">Laboratory</span>
                        </a>
                    @endif
                    @if($canSeeAllDepartments || ($user && $user->department === 'emergency'))
                        <a href="{{ route('reports.create', ['department' => 'emergency']) }}"
                           class="dropdown-item" @click="open = false">
                            <i data-lucide="siren" class="w-4 h-4" style="color: var(--hc-warning);"></i>
                            <span x-text="$store.app.t('emergency', 'Emergency (E.R)')">Emergency (E.R)</span>
                        </a>
                    @endif
                    @if($canSeeAllDepartments || ($user && $user->department === 'inpatient'))
                        <a href="{{ route('reports.create', ['department' => 'inpatient']) }}"
                           class="dropdown-item" @click="open = false">
                            <i data-lucide="bed" class="w-4 h-4" style="color: var(--hc-info);"></i>
                            <span x-text="$store.app.t('inpatient', 'Inpatient (IPD)')">Inpatient (IPD)</span>
                        </a>
                    @endif
                    @if($canSeeAllDepartments || ($user && $user->department === 'reception'))
                        <a href="{{ route('reports.create', ['department' => 'reception']) }}"
                           class="dropdown-item" @click="open = false">
                            <i data-lucide="users" class="w-4 h-4" style="color: var(--hc-info);"></i>
                            <span x-text="$store.app.t('reception', 'Reception (OPD)')">Reception (OPD)</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Audit Trail (Admin / Analytics Officer) -->
            @if($canSeeAllDepartments)
                <a href="{{ route('audit.index') }}"
                   class="topnav-link {{ request()->routeIs('audit.index') ? 'active' : '' }}">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    <span x-text="$store.app.t('audit_trail', 'Audit Trail')">Audit Trail</span>
                </a>
            @endif

            <!-- SOPs Portal (Hidden if disabled by Super Admin, unless user is Super Admin) -->
            @php
                $sopPortalActive = \App\Models\HospitalSetting::current()->extra_metadata['sop_portal_enabled'] ?? true;
                $isSuperAdmin = $user && $user->email === 'superadmin@reports.com';
            @endphp
            @if($sopPortalActive || $isSuperAdmin)
                <a href="{{ route('sops.index') }}"
                   class="topnav-link {{ request()->routeIs('sops.*') ? 'active' : '' }}">
                    <i data-lucide="book-open" class="w-4 h-4"></i>
                    <span>SOPs</span>
                    @if(!$sopPortalActive && $isSuperAdmin)
                        <span class="ml-1 px-1.5 py-0.2 text-[9px] bg-red-600 text-white rounded font-bold uppercase tracking-wider">OFF</span>
                    @endif
                </a>
            @endif

            <!-- Settings (Admin only) -->
            @if($user && $user->isAdmin())
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                            class="topnav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <i data-lucide="settings" class="w-4 h-4"></i>
                        <span>Settings</span>
                        <i data-lucide="chevron-down" class="w-3 h-3"></i>
                    </button>
                    <div x-show="open" @click.away="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="dropdown-menu">
                        <a href="{{ route('settings.hospital.edit') }}" class="dropdown-item" @click="open = false">
                            <i data-lucide="building-2" class="w-4 h-4"></i>
                            <span x-text="$store.app.t('hospital_branding', 'Hospital & Branding')">Hospital & Branding</span>
                        </a>
                        <a href="{{ route('settings.forms.index') }}" class="dropdown-item" @click="open = false">
                            <i data-lucide="sliders" class="w-4 h-4"></i>
                            <span x-text="$store.app.t('department_forms', 'Department Forms')">Department Forms</span>
                        </a>
                        <a href="{{ route('settings.users.index') }}" class="dropdown-item" @click="open = false">
                            <i data-lucide="user-cog" class="w-4 h-4"></i>
                            <span x-text="$store.app.t('staff_rbac', 'Staff & RBAC')">Staff & RBAC</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right: Utilities -->
        <div class="flex items-center gap-2 ml-auto">

            <!-- Language Toggle (Strictly EN or SO) -->
            <div class="flex items-center rounded-md p-0.5 text-xs font-semibold" style="background: var(--hc-surface-alt); border: 1px solid var(--hc-border);">
                <button type="button" @click="$store.app.setLocale('en')"
                        :style="$store.app.locale === 'en' ? 'background: var(--color-primary); color: white; border-radius: 4px;' : 'color: var(--hc-text-muted); border-radius: 4px;'"
                        class="px-2.5 py-1 transition font-bold" title="English">EN</button>
                <button type="button" @click="$store.app.setLocale('so')"
                        :style="$store.app.locale === 'so' ? 'background: var(--color-primary); color: white; border-radius: 4px;' : 'color: var(--hc-text-muted); border-radius: 4px;'"
                        class="px-2.5 py-1 transition font-bold" title="Af-Soomaali">SO</button>
            </div>

            <!-- Focus Mode -->
            <button @click="$store.app.toggleFocusMode()" class="btn-ghost btn-xs p-2" title="Focus Mode"
                    style="border-radius: 6px;">
                <i data-lucide="maximize-2" class="w-4 h-4"></i>
            </button>

            <!-- Theme Toggle -->
            <button @click="$store.app.toggleTheme()" class="btn-ghost btn-xs p-2" title="Toggle Theme"
                    style="border-radius: 6px;">
                <svg x-show="$store.app.theme === 'dark'" class="w-4 h-4" style="color: var(--hc-warning);" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                <svg x-show="$store.app.theme !== 'dark'" class="w-4 h-4" style="color: var(--hc-text-muted);" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
            </button>

            <!-- Secret Super Admin DeepSeek AI Button -->
            @if($user && $user->email === 'superadmin@reports.com')
                <a href="{{ route('superadmin.deepseek.index') }}"
                   class="btn btn-xs btn-purple" title="DeepSeek AI">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    <span class="hidden lg:inline">AI</span>
                </a>
            @endif

            <!-- Quick Role Switcher -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="btn btn-xs btn-secondary">
                    <i data-lucide="shuffle" class="w-3.5 h-3.5"></i>
                    <span class="hidden md:inline" x-text="$store.app.t('quick_role', 'Role')">Role</span>
                </button>
                <div x-show="open" @click.away="open = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="dropdown-menu" style="right: 0; left: auto; min-width: 260px;">
                    <div class="px-3 py-2 text-xs font-bold uppercase tracking-wider" style="color: var(--hc-text-muted);">
                        Fast Test Role Switch
                    </div>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('auth.quick-switch') }}" method="POST">
                        @csrf
                        @if($user && $user->isStealthSuperAdmin())
                            <button type="submit" name="email" value="superadmin@reports.com" class="dropdown-item">
                                <span class="status-dot" style="background: var(--hc-danger);"></span>
                                <span class="font-bold" style="color: var(--hc-danger);">Super Admin (Secret)</span>
                            </button>
                        @endif
                        <button type="submit" name="email" value="admin@kaafi.com" class="dropdown-item">
                            <span class="status-dot status-dot-brand"></span>
                            <span>Dr. Kaafi (Admin)</span>
                        </button>
                        <button type="submit" name="email" value="analytics@kaafi.com" class="dropdown-item">
                            <span class="status-dot" style="background: var(--hc-info);"></span>
                            <span>Farah (Analytics)</span>
                        </button>
                        <button type="submit" name="email" value="pharmacy@kaafi.com" class="dropdown-item">
                            <span class="status-dot status-dot-success"></span>
                            <span>Ahmed (Pharmacy)</span>
                        </button>
                        <button type="submit" name="email" value="lab@kaafi.com" class="dropdown-item">
                            <span class="status-dot" style="background: var(--hc-purple);"></span>
                            <span>Khadra (Lab)</span>
                        </button>
                        <button type="submit" name="email" value="emergency@kaafi.com" class="dropdown-item">
                            <span class="status-dot status-dot-warning"></span>
                            <span>Dr. Hassan (ER)</span>
                        </button>
                        <button type="submit" name="email" value="inpatient@kaafi.com" class="dropdown-item">
                            <span class="status-dot" style="background: var(--hc-info);"></span>
                            <span>Sister Halima (IPD)</span>
                        </button>
                        <button type="submit" name="email" value="reception@kaafi.com" class="dropdown-item">
                            <span class="status-dot status-dot-brand"></span>
                            <span>Maryan (Reception)</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- User Menu -->
            @auth
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                            class="flex items-center gap-2 px-2 py-1.5 transition"
                            style="border-radius: 6px; color: var(--hc-text);"
                            onmouseover="this.style.background='var(--hc-surface-alt)'"
                            onmouseout="this.style.background='transparent'">
                        <div class="w-7 h-7 flex items-center justify-center font-bold text-xs text-white"
                             style="background: var(--color-primary); border-radius: 6px;">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="hidden sm:block text-left">
                            <div class="text-xs font-semibold leading-tight">{{ auth()->user()->name }}</div>
                            <div class="text-[10px] leading-tight" style="color: var(--hc-text-muted);">
                                {{ auth()->user()->visible_role_badge }}
                                @if(auth()->user()->department)
                                    · {{ ucfirst(auth()->user()->department) }}
                                @endif
                            </div>
                        </div>
                        <i data-lucide="chevron-down" class="w-3 h-3" style="color: var(--hc-text-muted);"></i>
                    </button>
                    <div x-show="open" @click.away="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="dropdown-menu" style="right: 0; left: auto; min-width: 200px;">
                        <div class="px-3 py-2" style="border-bottom: 1px solid var(--hc-border);">
                            <div class="text-xs font-bold">{{ auth()->user()->name }}</div>
                            <div class="text-[11px] font-mono" style="color: var(--hc-text-muted);">{{ auth()->user()->email }}</div>
                        </div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item" style="color: var(--hc-danger);">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                                <span x-text="$store.app.t('sign_out', 'Sign Out')">Sign Out</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endauth

            <!-- Mobile Hamburger -->
            <button @click="$store.app.toggleMobileMenu()" class="lg:hidden btn-ghost btn-xs p-2" style="border-radius: 6px;">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>
        </div>
    </nav>

    <!-- ═══════════════════════════════════════════════════════
         MOBILE NAVIGATION DRAWER
         ═══════════════════════════════════════════════════════ -->
    <div x-show="$store.app.mobileMenuOpen" @click.away="$store.app.mobileMenuOpen = false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="lg:hidden no-print" style="background: var(--hc-surface); border-bottom: 1px solid var(--hc-border); padding: 12px 24px;">
        <div class="flex flex-col gap-1">
            <a href="{{ route('dashboard') }}" class="dropdown-item">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                <span x-text="$store.app.t('executive_dashboard', 'Dashboard')">Dashboard</span>
            </a>
            <a href="{{ route('reports.index') }}" class="dropdown-item">
                <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                <span x-text="$store.app.t('daily_submissions', 'Submissions')">Submissions</span>
            </a>
            <div class="dropdown-divider"></div>
            <div class="px-3 py-1 text-xs font-bold uppercase tracking-wider" style="color: var(--hc-text-muted);">Departments</div>
            @if($canSeeAllDepartments || ($user && $user->department === 'pharmacy'))
                <a href="{{ route('reports.create', ['department' => 'pharmacy']) }}" class="dropdown-item">
                    <i data-lucide="pill" class="w-4 h-4" style="color: var(--hc-success);"></i> Pharmacy
                </a>
            @endif
            @if($canSeeAllDepartments || ($user && $user->department === 'laboratory'))
                <a href="{{ route('reports.create', ['department' => 'laboratory']) }}" class="dropdown-item">
                    <i data-lucide="flask-conical" class="w-4 h-4" style="color: var(--hc-purple);"></i> Laboratory
                </a>
            @endif
            @if($canSeeAllDepartments || ($user && $user->department === 'emergency'))
                <a href="{{ route('reports.create', ['department' => 'emergency']) }}" class="dropdown-item">
                    <i data-lucide="siren" class="w-4 h-4" style="color: var(--hc-warning);"></i> Emergency
                </a>
            @endif
            @if($canSeeAllDepartments || ($user && $user->department === 'inpatient'))
                <a href="{{ route('reports.create', ['department' => 'inpatient']) }}" class="dropdown-item">
                    <i data-lucide="bed" class="w-4 h-4" style="color: var(--hc-info);"></i> Inpatient
                </a>
            @endif
            @if($canSeeAllDepartments || ($user && $user->department === 'reception'))
                <a href="{{ route('reports.create', ['department' => 'reception']) }}" class="dropdown-item">
                    <i data-lucide="users" class="w-4 h-4" style="color: var(--hc-info);"></i> Reception
                </a>
            @endif

            @if($sopPortalActive || $isSuperAdmin)
                <a href="{{ route('sops.index') }}" class="dropdown-item">
                    <i data-lucide="book-open" class="w-4 h-4"></i> Standard Operating Procedures (SOPs)
                </a>
            @endif

            <!-- Mobile Language Switcher (Strictly EN or SO) -->
            <div class="dropdown-divider"></div>
            <div class="flex items-center gap-2 px-3 py-2">
                <span class="text-xs font-bold" style="color: var(--hc-text-muted);">Language:</span>
                <div class="flex items-center rounded-md p-0.5 text-xs font-semibold" style="background: var(--hc-surface-alt); border: 1px solid var(--hc-border);">
                    <button type="button" @click="$store.app.setLocale('en')"
                            :style="$store.app.locale === 'en' ? 'background: var(--color-primary); color: white; border-radius: 4px;' : 'color: var(--hc-text-muted); border-radius: 4px;'"
                            class="px-2.5 py-1 transition font-bold">EN</button>
                    <button type="button" @click="$store.app.setLocale('so')"
                            :style="$store.app.locale === 'so' ? 'background: var(--color-primary); color: white; border-radius: 4px;' : 'color: var(--hc-text-muted); border-radius: 4px;'"
                            class="px-2.5 py-1 transition font-bold">SO</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════
         FLOATING EXIT FOCUS MODE
         ═══════════════════════════════════════════════════════ -->
    <div x-show="$store.app.focusMode" class="no-print fixed top-4 right-4 z-50" x-transition>
        <button @click="$store.app.toggleFocusMode()"
                class="btn btn-sm"
                style="background: var(--hc-text); color: var(--hc-surface); border-color: var(--hc-text);">
            <i data-lucide="minimize-2" class="w-3.5 h-3.5"></i>
            <span x-text="$store.app.t('exit_focus_mode', 'Exit Focus Mode')">Exit Focus Mode</span>
        </button>
    </div>

    <!-- ═══════════════════════════════════════════════════════
         MAIN CONTENT CANVAS
         ═══════════════════════════════════════════════════════ -->
    <main class="flex-1 px-4 lg:px-8 py-4 lg:py-5 overflow-y-auto">

        <!-- Toast Notifications -->
        @if(session('success'))
            <div class="hc-toast hc-toast-success no-print">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="btn-ghost btn-xs p-1" style="color: var(--hc-success);">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        @endif

        @if(session('warning'))
            <div class="hc-toast hc-toast-warning no-print">
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                    <span>{{ session('warning') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="btn-ghost btn-xs p-1" style="color: var(--hc-warning);">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="hc-toast hc-toast-danger no-print">
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="btn-ghost btn-xs p-1" style="color: var(--hc-danger);">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        @endif

        {{ $slot ?? '' }}
        @yield('content')

        <!-- System Copyright Footer -->
        <footer class="mt-12 pt-6 pb-4 border-t text-center text-xs no-print" style="border-color: var(--hc-border); color: var(--hc-text-muted);">
            <div class="flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-4 font-medium">
                <span>This platform is developed by <strong class="text-slate-900 dark:text-white font-bold">SEHTECH Healthcare Solutions</strong></span>
                <span class="hidden sm:inline opacity-50">•</span>
                <span>All copyrights received for <strong class="text-slate-900 dark:text-white font-bold">Mohammad Sulieman</strong></span>
            </div>
        </footer>
    </main>

    <!-- Script Refresh -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.refreshIcons) window.refreshIcons();
        });
    </script>
    @stack('scripts')
</body>
</html>
