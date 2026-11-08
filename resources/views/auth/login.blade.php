<!DOCTYPE html>
<html lang="en" class="h-full bg-black">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Kaafi Hospitals Specialized Clinical Analytics & Operations Intelligence Portal">
    <title>Kaafi Hospitals — Clinical Analytics &amp; Operations Portal</title>
    <link rel="icon" type="image/png" href="{{ asset('images/kaafi_logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Atmospheric Door & Light Beam Styles */
        .door-perspective-container {
            perspective: 1200px;
            perspective-origin: 50% 45%;
        }

        .door-leaf {
            transform-style: preserve-3d;
            transform-origin: left center;
            transform: rotateY(-42deg);
            transition: transform 1.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Volumetric Light Beam spilling onto the floor */
        .volumetric-light-beam {
            background: linear-gradient(135deg, rgba(255, 245, 210, 0.45) 0%, rgba(255, 230, 160, 0.22) 35%, rgba(255, 220, 130, 0.08) 65%, transparent 100%);
            clip-path: polygon(48% 0%, 55% 0%, 100% 100%, 12% 100%);
            filter: blur(14px);
        }

        /* Floor reflection glow */
        .floor-light-pool {
            background: radial-gradient(ellipse at 50% 20%, rgba(255, 240, 190, 0.32) 0%, rgba(255, 225, 140, 0.16) 40%, rgba(255, 210, 100, 0.04) 70%, transparent 85%);
            filter: blur(28px);
        }

        /* Radiant Door Core Aperture */
        .door-light-core {
            background: linear-gradient(to right, rgba(255, 255, 255, 1) 0%, rgba(255, 248, 220, 0.95) 40%, rgba(255, 230, 150, 0.7) 80%, transparent 100%);
            box-shadow: 0 0 70px 25px rgba(255, 240, 180, 0.75), 0 0 140px 60px rgba(255, 220, 120, 0.4);
        }
    </style>
</head>
<body class="h-full bg-black text-slate-100 antialiased overflow-x-hidden selection:bg-[#124E78] selection:text-white flex flex-col justify-between">

    <!-- Atmospheric Architectural Doorway & Radiant Light Beam (Behind Form) -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0 flex items-center justify-center">
        <!-- Deep Ambient Darkness with subtle vignette -->
        <div class="absolute inset-0 bg-radial from-transparent via-black/80 to-black"></div>

        <!-- Door Frame & Light Assembly -->
        <div class="relative w-[340px] sm:w-[420px] md:w-[480px] h-[580px] sm:h-[640px] door-perspective-container -translate-y-8 sm:-translate-y-12">
            
            <!-- Intense Radiant Light pouring from inside the opening room -->
            <div class="absolute top-[8%] left-[38%] w-[26%] h-[84%] door-light-core rounded-sm z-0"></div>

            <!-- Expanding Volumetric Light Beam spilling forward across room floor -->
            <div class="absolute top-[35%] left-[-80%] w-[260%] h-[120%] volumetric-light-beam z-0 opacity-85"></div>

            <!-- Floor Light Pool Glow -->
            <div class="absolute top-[75%] left-[-40%] w-[180%] h-[70%] floor-light-pool z-0 opacity-90"></div>

            <!-- Architectural Door Jamb / Outer Frame -->
            <div class="absolute inset-0 border-[10px] sm:border-[14px] border-slate-900/90 rounded-t-xl shadow-2xl bg-slate-950/40 z-10">
                <!-- Inner Door Aperture / Bright Gateway -->
                <div class="relative w-full h-full overflow-hidden bg-gradient-to-r from-amber-100 via-amber-50 to-amber-200 shadow-inner">

                    <!-- Soft glowing atmospheric particles / room interior -->
                    <div class="absolute inset-0 bg-gradient-to-t from-amber-200/40 via-white/80 to-amber-100/30"></div>

                    <!-- The Partially Opening Door Leaf (Rotated in 3D Perspective) -->
                    <div class="door-leaf absolute inset-y-0 left-0 w-[92%] bg-gradient-to-r from-slate-950 via-slate-900 to-slate-800 border-r-2 border-slate-700/80 shadow-[10px_0_40px_rgba(0,0,0,0.9)] flex items-center justify-end pr-5">
                        <!-- Architectural Panels on Door -->
                        <div class="w-full h-full p-4 sm:p-5 flex flex-col justify-between opacity-80 pointer-events-none">
                            <div class="w-full h-[40%] rounded-md border border-slate-700/40 bg-slate-900/40 shadow-inner"></div>
                            <div class="w-full h-[45%] rounded-md border border-slate-700/40 bg-slate-900/40 shadow-inner"></div>
                        </div>

                        <!-- Modern Door Handle with Metallic Specular Highlight -->
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 flex items-center">
                            <div class="w-1.5 h-14 bg-gradient-to-b from-slate-400 via-amber-100 to-slate-500 rounded-sm shadow-md"></div>
                            <div class="w-6 h-2 bg-gradient-to-r from-slate-300 to-amber-200 rounded-r-xs -ml-1 shadow-lg"></div>
                        </div>
                    </div>

                    <!-- Ambient Glow Spill around the opening crack -->
                    <div class="absolute inset-y-0 right-0 w-24 bg-gradient-to-l from-white/90 via-amber-100/60 to-transparent blur-md pointer-events-none"></div>
                </div>
            </div>

            <!-- Soft Luminous Vignette Bloom -->
            <div class="absolute -inset-10 bg-radial from-amber-100/10 via-transparent to-transparent pointer-events-none blur-2xl"></div>
        </div>

        <!-- Ground Horizon Line & Gentle Ground Mist -->
        <div class="absolute bottom-0 inset-x-0 h-48 bg-gradient-to-t from-black via-black/90 to-transparent"></div>
    </div>

    <!-- Top Minimalist Brand Header -->
    <header class="relative z-10 w-full px-6 py-6 sm:px-12 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/kaafi_logo.png') }}" 
                 alt="Kaafi Hospitals Logo" 
                 class="h-10 sm:h-11 w-auto object-contain rounded-md drop-shadow-[0_2px_12px_rgba(255,255,255,0.15)]">
            <div>
                <span class="text-sm sm:text-base font-extrabold tracking-tight text-white block leading-tight">Kaafi Hospitals</span>
                <span class="text-[10px] sm:text-xs text-slate-400 uppercase tracking-wider font-medium">Operations &amp; Analytics Portal</span>
            </div>
        </div>
        <div class="hidden sm:flex items-center gap-2 text-xs font-mono text-emerald-400/90 bg-emerald-950/40 border border-emerald-500/30 px-3 py-1.5 rounded-full backdrop-blur-md">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>256-Bit SSL Encrypted</span>
        </div>
    </header>

    <!-- Main Container: Light Login Card floating in front of glowing light beam -->
    <main class="relative z-10 flex-1 flex items-center justify-center px-4 py-8 sm:py-12">
        <div class="w-full max-w-[430px]">
            
            <!-- Crisp Light Login Card -->
            <div class="bg-white rounded-3xl p-7 sm:p-9 shadow-[0_25px_70px_rgba(0,0,0,0.8),0_0_50px_rgba(255,245,210,0.22)] border border-white/60 relative overflow-hidden text-slate-900 transition-all duration-300">
                
                <!-- Subtle Top Brand Accent Bar -->
                <div class="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-[#124E78] via-indigo-600 to-[#C81D25]"></div>

                <!-- Hospital Brand Emblem & Header -->
                <div class="text-center mb-7 pt-1">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-slate-50 border border-slate-200/80 shadow-xs mb-3.5 p-2">
                        <img src="{{ asset('images/kaafi_logo.png') }}" alt="Kaafi Hospitals" class="w-full h-full object-contain">
                    </div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                        Sign In
                    </h2>
                    <p class="text-xs text-slate-500 mt-1 font-medium">
                        Access Hospital Clinical Analytics &amp; Operations Center
                    </p>
                </div>

                <!-- Error Alert Box -->
                @if($errors->any())
                    <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center gap-2.5 animate-shake">
                        <svg class="w-4 h-4 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center gap-2.5">
                        <svg class="w-4 h-4 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Credentials Form -->
                <form action="{{ route('login.post') }}" method="POST" class="space-y-4" x-data="{ showPassword: false }">
                    @csrf

                    <!-- Work Email Field -->
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Work Email Address
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/>
                                </svg>
                            </div>
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autofocus
                                   placeholder="staff@kaafihospitals.org"
                                   class="w-full pl-10 pr-3.5 py-3 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 placeholder-slate-400 text-sm font-medium focus:outline-hidden focus:border-[#124E78] focus:bg-white focus:ring-3 focus:ring-[#124E78]/15 transition">
                        </div>
                    </div>

                    <!-- Password Field with Show/Hide Toggle -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Password
                            </label>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input :type="showPassword ? 'text' : 'password'" 
                                   name="password" 
                                   id="password" 
                                   required 
                                   placeholder="••••••••"
                                   class="w-full pl-10 pr-11 py-3 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 placeholder-slate-400 text-sm font-medium focus:outline-hidden focus:border-[#124E78] focus:bg-white focus:ring-3 focus:ring-[#124E78]/15 transition">
                            <button type="button" 
                                    @click="showPassword = !showPassword" 
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition cursor-pointer"
                                    tabindex="-1">
                                <template x-if="!showPassword">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </template>
                                <template x-if="showPassword">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                    </svg>
                                </template>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me Option -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" 
                                   name="remember" 
                                   value="1" 
                                   class="w-4 h-4 rounded text-[#124E78] border-slate-300 focus:ring-[#124E78] cursor-pointer">
                            <span class="text-xs text-slate-600 font-medium">Remember workstation</span>
                        </label>
                        <span class="text-[11px] text-slate-400 font-mono">Kaafi Single Sign-On</span>
                    </div>

                    <!-- Sign In Submit Button -->
                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-[#124E78] to-[#0d3b5b] hover:from-[#0f4469] hover:to-[#092b43] text-white text-sm font-bold shadow-lg shadow-[#124E78]/25 hover:shadow-xl hover:shadow-[#124E78]/35 active:scale-[0.99] transition-all duration-150 cursor-pointer flex items-center justify-center gap-2">
                            <span>Sign In to Operations Portal</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </div>
                </form>

                <!-- Security Assurance Pill -->
                <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-center gap-2 text-[11px] text-slate-400 font-medium text-center">
                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>Authorized Hospital Personnel &amp; Audit Officers Only</span>
                </div>
            </div>

            <!-- Subtle Under-Card Caption -->
            <p class="text-center text-xs text-slate-500 mt-5 font-medium">
                Kaafi Hospitals &bull; Specialized Healthcare Analytics &bull; Designed &amp; Developed by SEHTECH Healthcare Solutions (Mohammad Sulieman)
            </p>
        </div>
    </main>

    <!-- Bottom Simple Legal / System Footer -->
    <footer class="relative z-10 w-full px-6 py-4 text-center text-[11px] text-slate-500 font-mono">
        &copy; {{ date('Y') }} Kaafi Hospitals. All rights reserved.
    </footer>

</body>
</html>
