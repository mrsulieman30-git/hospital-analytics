<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kaafi Hospitals - Analytics & Operations Portal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4 selection:bg-[#124E78] selection:text-white">
    <div class="w-full max-w-5xl grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        
        <!-- Left Side: Hospital Brand & Welcome -->
        <div class="lg:col-span-5 space-y-6 text-center lg:text-left">
            <div class="inline-flex items-center space-x-3 bg-slate-800/80 border border-slate-700/80 rounded-2xl px-4 py-3 shadow-lg">
                <img src="{{ asset('images/kaafi_logo.png') }}" alt="Kaafi Hospitals" class="h-12 w-auto object-contain rounded drop-shadow">
                <div class="text-left">
                    <h1 class="text-xl font-bold tracking-tight text-white leading-tight">Kaafi Hospitals</h1>
                    <p class="text-xs text-slate-400 font-medium">Analytics & Executive Performance</p>
                </div>
            </div>

            <div class="space-y-3">
                <h2 class="text-3xl font-extrabold text-white tracking-tight">
                    Multi-Tenant Clinical Governance & Daily Data Collection
                </h2>
                <p class="text-sm text-slate-300 leading-relaxed">
                    Synchronized dual-mode daily collection across Pharmacy, Laboratory, Emergency, Inpatient, and Reception departments with automated cross-department audit verification.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 text-left">
                <div class="p-3 rounded-xl bg-slate-800/50 border border-slate-700/50">
                    <div class="text-xs text-slate-400 font-medium">Bilingual System</div>
                    <div class="text-sm font-bold text-white">English & Soomaali</div>
                </div>
                <div class="p-3 rounded-xl bg-slate-800/50 border border-slate-700/50">
                    <div class="text-xs text-slate-400 font-medium">Dual-Mode Forms</div>
                    <div class="text-sm font-bold text-white">Paper Replica + Guided</div>
                </div>
                <div class="p-3 rounded-xl bg-slate-800/50 border border-slate-700/50">
                    <div class="text-xs text-slate-400 font-medium">Daily Cutoff</div>
                    <div class="text-sm font-bold text-white">08:30 AM Strict Audit</div>
                </div>
                <div class="p-3 rounded-xl bg-slate-800/50 border border-slate-700/50">
                    <div class="text-xs text-slate-400 font-medium">AI Intelligence</div>
                    <div class="text-sm font-bold text-white">DeepSeek Analyst</div>
                </div>
            </div>
        </div>

        <!-- Right Side: Login & 1-Click Fast Roles -->
        <div class="lg:col-span-7 bg-slate-800/90 border border-slate-700/80 rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-xl space-y-6">
            
            <div class="flex items-center justify-between border-b border-slate-700/60 pb-4">
                <div>
                    <h3 class="text-lg font-bold text-white">Sign In to Workspace</h3>
                    <p class="text-xs text-slate-400">Enter credentials or select a pre-configured role below</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    System Online
                </span>
            </div>

            @if($errors->any())
                <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs font-medium">
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- Standard Credentials Form -->
            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Staff Work Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:outline-hidden focus:border-[#124E78] transition"
                           placeholder="staff@kaafihospital.so">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-medium text-slate-300">Password</label>
                    </div>
                    <input type="password" name="password" required
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:outline-hidden focus:border-[#124E78] transition"
                           placeholder="••••••••">
                </div>

                <button type="submit" 
                        class="w-full py-2.5 rounded-xl bg-[#124E78] hover:bg-[#0e3f61] text-white text-sm font-bold shadow-md transition">
                    Authenticate & Access Platform
                </button>
            </form>

            <!-- 1-Click Fast Role Switcher -->
            <div class="space-y-3 pt-2">
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Fast 1-Click Role Testing</span>
                    <div class="flex-1 border-t border-slate-700/60"></div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    @foreach($demoUsers as $dUser)
                        <form action="{{ route('auth.quick-switch') }}" method="POST">
                            @csrf
                            <input type="hidden" name="email" value="{{ $dUser['email'] }}">
                            <button type="submit" 
                                    class="w-full text-left p-2.5 rounded-xl bg-slate-900/70 hover:bg-slate-900 border border-slate-700/60 hover:border-slate-600 transition flex items-center justify-between group">
                                <div class="truncate mr-2">
                                    <div class="text-xs font-bold text-white group-hover:text-blue-400 transition truncate">
                                        {{ $dUser['name'] }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 truncate">
                                        {{ $dUser['role_label'] }}
                                    </div>
                                </div>
                                <span class="text-[10px] font-mono px-2 py-0.5 rounded-md font-semibold shrink-0 {{ $dUser['email'] === 'superadmin@reports.com' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'bg-slate-800 text-slate-300 border border-slate-700' }}">
                                    {{ $dUser['badge'] }}
                                </span>
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>

        </div>

    </div>

    <!-- System Copyright Footer -->
    <div class="fixed bottom-3 inset-x-0 text-center text-xs text-slate-400 font-medium px-4">
        <span>This platform is developed by <strong class="text-white font-bold">SEHTECH Healthcare Solutions</strong></span>
        <span class="mx-2 opacity-50">•</span>
        <span>All copyrights received for <strong class="text-white font-bold">Mohammad Sulieman</strong></span>
    </div>
</body>
</html>
