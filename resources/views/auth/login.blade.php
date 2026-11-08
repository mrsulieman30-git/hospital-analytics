<!DOCTYPE html>
<html lang="en" class="h-full bg-[#030d22]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Kaafi Hospitals Specialized Clinical Analytics & Operations Intelligence Portal">
    <title>Kaafi Hospitals — Clinical Analytics &amp; Operations Portal</title>
    <link rel="icon" type="image/png" href="{{ asset('images/kaafi_logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Ambient Brick Wall Base & Lighting */
        .brick-wall-dark {
            background-image: url('{{ asset('images/dark_brick_wall_hd.jpg') }}');
            background-size: cover;
            background-position: center top;
            background-repeat: no-repeat;
        }

        .brick-wall-lit {
            background-image: url('{{ asset('images/lit_brick_wall_hd.jpg') }}');
            background-size: cover;
            background-position: center top;
            background-repeat: no-repeat;
        }

        /* Glassmorphic Frosted Login Card */
        .glass-login-card {
            background: rgba(18, 28, 54, 0.42);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.65), 0 0 40px rgba(255, 220, 130, 0.12);
        }

        .glass-login-card.card-dimmed {
            background: rgba(8, 14, 28, 0.60);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.85);
        }

        /* Pill Input Fields matching reference image */
        .pill-input {
            background: rgba(255, 255, 255, 0.09);
            border: 1px solid rgba(255, 255, 255, 0.22);
            color: #ffffff;
            transition: all 0.2s ease;
        }

        .pill-input:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.60);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.15);
            outline: none;
        }

        .pill-input::placeholder {
            color: rgba(255, 255, 255, 0.55);
        }

        /* Lamp Trapezoid Geometry */
        .lamp-trapezoid {
            clip-path: polygon(10% 0%, 90% 0%, 100% 100%, 0% 100%);
        }

        /* Pull Cord Handle Cursor */
        .cord-grabber {
            cursor: grab;
            touch-action: none;
            user-select: none;
        }

        .cord-grabber:active, .cord-grabber.is-dragging {
            cursor: grabbing;
        }

        /* Pulse glow animation for cord hint */
        @keyframes cordHintPulse {
            0%, 100% { opacity: 0.85; transform: translateY(0); }
            50% { opacity: 1; transform: translateY(3px); }
        }

        .cord-hint-anim {
            animation: cordHintPulse 2s infinite ease-in-out;
        }
    </style>
</head>
<body class="h-full bg-[#030d22] text-slate-100 antialiased overflow-x-hidden selection:bg-amber-400 selection:text-slate-950 flex flex-col justify-between"
      x-data="lampScene()"
      x-init="initScene()">

    <!-- LAYER 1: Dark Navy Brick Wall Base (Always underneath) -->
    <div class="fixed inset-0 brick-wall-dark z-0 pointer-events-none"></div>

    <!-- LAYER 2: Lit Brick Wall with Warm Downward Spotlight Cone (Fades smoothly on light toggle) -->
    <div class="fixed inset-0 brick-wall-lit z-0 pointer-events-none transition-opacity duration-600 ease-out"
         :style="'opacity: ' + (lightOn ? '1' : '0')"></div>

    <!-- LAYER 3: Volumetric Radiant Ambient Bloom (Behind form) -->
    <div class="fixed inset-0 z-0 pointer-events-none flex items-center justify-center overflow-hidden transition-opacity duration-700 ease-out"
         :style="'opacity: ' + (lightOn ? '0.85' : '0')">
        <div class="w-[620px] h-[520px] rounded-full bg-radial from-amber-200/25 via-amber-400/10 to-transparent blur-3xl -translate-y-8"></div>
    </div>

    <!-- TOP HEADER: Minimalist Hospital Identity -->
    <header class="relative z-20 w-full px-6 py-4 sm:px-10 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/kaafi_logo.png') }}" 
                 alt="Kaafi Hospitals Logo" 
                 class="h-9 sm:h-10 w-auto object-contain drop-shadow-[0_2px_10px_rgba(0,0,0,0.5)]">
            <div>
                <span class="text-sm sm:text-base font-extrabold tracking-tight text-white block leading-tight">Kaafi Hospitals</span>
                <span class="text-[10px] sm:text-xs text-slate-300/80 uppercase tracking-wider font-medium">Operations &amp; Analytics Portal</span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <!-- Light Status Badge (Clickable alternative) -->
            <button type="button" 
                    @click="triggerPull()"
                    class="px-3 py-1.5 rounded-full text-xs font-mono backdrop-blur-md border transition-all duration-300 flex items-center gap-2 cursor-pointer"
                    :class="lightOn ? 'bg-amber-400/15 border-amber-400/40 text-amber-200 hover:bg-amber-400/25' : 'bg-slate-800/60 border-slate-700 text-slate-400 hover:bg-slate-700/60'">
                <span class="w-2 h-2 rounded-full transition-colors duration-300"
                      :class="lightOn ? 'bg-amber-400 shadow-[0_0_8px_#fbbf24]' : 'bg-slate-500'"></span>
                <span x-text="lightOn ? 'Light ON' : 'Light OFF'"></span>
            </button>
        </div>
    </header>

    <!-- MAIN SCENE: Lamp Assembly + Interactive Pull Rope + Floating Glass Login Card -->
    <main class="relative z-10 flex-1 flex flex-col items-center justify-center px-4 py-4 sm:py-6">
        
        <!-- ======================================================== -->
        <!-- LAMP & INTERACTIVE PULL-CORD ASSEMBLY                    -->
        <!-- ======================================================== -->
        <div class="relative w-full max-w-[440px] flex flex-col items-center mb-2 z-30">
            
            <!-- Wall Lamp Fixture (Trapezoidal Sconce matching reference) -->
            <div class="relative z-20 flex flex-col items-center">
                <!-- Mounting Bracket Plate -->
                <div class="w-16 h-2 bg-slate-900 rounded-t-sm shadow-md border-t border-slate-700/60"></div>
                
                <!-- Main Trapezoid Metal Lamp Fixture -->
                <div class="w-28 sm:w-32 h-13 sm:h-14 bg-gradient-to-b from-[#182a4d] via-[#0d1a33] to-[#081224] lamp-trapezoid shadow-2xl relative overflow-hidden flex flex-col justify-end border-t border-cyan-400/20">
                    <!-- Subtle metallic top rim highlight -->
                    <div class="absolute inset-x-2 top-0.5 h-[1px] bg-gradient-to-r from-transparent via-cyan-300/40 to-transparent"></div>

                    <!-- Underside Glowing Glass Diffuser Bar -->
                    <div class="w-full h-3 sm:h-3.5 transition-all duration-500 ease-out flex items-center justify-center"
                         :class="lightOn ? 'bg-gradient-to-r from-amber-200 via-white to-amber-200 shadow-[0_0_35px_12px_rgba(255,235,160,0.95),0_0_70px_25px_rgba(245,158,11,0.7)]' : 'bg-[#0a1424] border-t border-slate-800 shadow-none'">
                        <!-- Core light filament line when lit -->
                        <div class="w-3/4 h-[2px] transition-opacity duration-300"
                             :class="lightOn ? 'bg-white opacity-100' : 'opacity-0'"></div>
                    </div>
                </div>

                <!-- Hotspot Bloom underneath the lamp -->
                <div class="absolute -bottom-8 pointer-events-none transition-opacity duration-500 ease-out"
                     :class="lightOn ? 'opacity-100 scale-100' : 'opacity-0 scale-90'">
                    <div class="w-48 h-20 bg-radial from-amber-100/90 via-amber-300/40 to-transparent blur-lg"></div>
                </div>
            </div>

            <!-- INTERACTIVE PULL ROPE / CORD WITH NATURAL SPRING PHYSICS -->
            <!-- Positioned hanging from the right underside of the lamp fixture -->
            <div class="absolute top-[48px] sm:top-[52px] left-[calc(50%+46px)] sm:left-[calc(50%+52px)] w-[140px] h-[220px] pointer-events-none z-40">
                <svg id="rope-svg" 
                     class="w-full h-full overflow-visible pointer-events-none" 
                     viewBox="0 0 140 220">
                    <defs>
                        <!-- Brass bead / cord gradient -->
                        <linearGradient id="brassGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#fff0be" />
                            <stop offset="35%" stop-color="#e5b449" />
                            <stop offset="70%" stop-color="#ab771d" />
                            <stop offset="100%" stop-color="#5a3d07" />
                        </linearGradient>

                        <!-- Pull Knob Radial Gradient -->
                        <radialGradient id="knobGradient" cx="35%" cy="30%" r="70%">
                            <stop offset="0%" stop-color="#ffffff" />
                            <stop offset="25%" stop-color="#fde68a" />
                            <stop offset="65%" stop-color="#d97706" />
                            <stop offset="100%" stop-color="#78350f" />
                        </radialGradient>

                        <!-- Glow filter for pull handle -->
                        <filter id="knobGlow" x="-50%" y="-50%" width="200%" height="200%">
                            <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="#f59e0b" flood-opacity="0.6"/>
                        </filter>
                    </defs>

                    <!-- The Flexible Rope Path (dynamically updated by physics engine) -->
                    <path id="rope-path" 
                          d="M 20 0 Q 20 60 20 120" 
                          stroke="url(#brassGradient)" 
                          stroke-width="2.5" 
                          stroke-linecap="round"
                          fill="none" 
                          stroke-dasharray="1 0" />

                    <!-- The Pull Knob / Handle Group (Draggable & Clickable) -->
                    <g id="rope-handle-group" 
                       transform="translate(20, 120)" 
                       class="cord-grabber pointer-events-auto"
                       @pointerdown="onCordPointerDown($event)">
                        
                        <!-- Invisible larger touch/hit target for effortless mobile/mouse grabbing -->
                        <circle cx="0" cy="10" r="24" fill="transparent" />

                        <!-- Top attachment bead ring -->
                        <circle cx="0" cy="0" r="3" fill="url(#brassGradient)" stroke="#78350f" stroke-width="0.5" />
                        
                        <!-- Elegant Teardrop / Bell Brass Pull Weight -->
                        <path d="M -3 3 L 3 3 L 6 16 C 6 20 3 23 0 23 C -3 23 -6 20 -6 16 Z" 
                              fill="url(#knobGradient)" 
                              stroke="#78350f" 
                              stroke-width="0.8" 
                              filter="url(#knobGlow)" />
                        
                        <!-- Bottom small metallic ring bead -->
                        <circle cx="0" cy="24" r="2.5" fill="url(#brassGradient)" />

                        <!-- Specular highlight on pull handle -->
                        <path d="M -1.5 6 L 1.5 6 L 2 15 C 2 17 0.5 19 0 19" 
                              stroke="rgba(255,255,255,0.7)" 
                              stroke-width="0.8" 
                              stroke-linecap="round" 
                              fill="none" />
                    </g>
                </svg>

                <!-- Floating Pull Hint Tooltip (disappears once pulled) -->
                <div x-show="showHint" 
                     x-transition.opacity.duration.400ms
                     class="absolute left-[36px] top-[108px] bg-slate-900/90 border border-amber-400/40 text-amber-200 text-[10px] font-semibold px-2.5 py-1 rounded-full whitespace-nowrap shadow-xl pointer-events-none cord-hint-anim flex items-center gap-1.5 backdrop-blur-md">
                    <span>💡 Pull to toggle</span>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- FLOATING GLASSMORPHISM LOGIN CARD                        -->
        <!-- Matches media_1794110983270_10fa23f5.webp exactly        -->
        <!-- ======================================================== -->
        <div class="w-full max-w-[390px] sm:max-w-[420px] transition-all duration-500 ease-out">
            <div class="glass-login-card rounded-3xl p-7 sm:p-9 text-white transition-all duration-500"
                 :class="{ 'card-dimmed': !lightOn }">

                <!-- Header: Hospital Crest & Bold "Login" title matching reference -->
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center mb-2.5">
                        <img src="{{ asset('images/kaafi_logo.png') }}" 
                             alt="Kaafi Hospitals" 
                             class="h-10 w-auto object-contain drop-shadow-[0_2px_12px_rgba(255,255,255,0.25)]">
                    </div>
                    <h2 class="text-3xl font-extrabold tracking-tight text-white">
                        Login
                    </h2>
                    <p class="text-xs text-slate-300/80 mt-1 font-medium">
                        Kaafi Hospitals Clinical Analytics Portal
                    </p>
                </div>

                <!-- Error Messages Box -->
                @if($errors->any())
                    <div class="mb-5 p-3.5 rounded-2xl bg-rose-500/20 border border-rose-500/40 text-rose-200 text-xs font-semibold flex items-center gap-2.5 backdrop-blur-md">
                        <svg class="w-4 h-4 shrink-0 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-5 p-3.5 rounded-2xl bg-rose-500/20 border border-rose-500/40 text-rose-200 text-xs font-semibold flex items-center gap-2.5 backdrop-blur-md">
                        <svg class="w-4 h-4 shrink-0 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Credentials Form -->
                <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Username / Email Input Pill with User Icon on Right -->
                    <div class="relative">
                        <input type="text" 
                               name="email" 
                               id="email" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus
                               placeholder="Username"
                               class="pill-input w-full py-3.5 pl-6 pr-12 rounded-full text-sm font-medium">
                        
                        <!-- User Silhouette Icon on the Right (exactly like mockup) -->
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-300/80">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Password Input Pill with Lock Icon on Right -->
                    <div class="relative">
                        <input type="password" 
                               name="password" 
                               id="password" 
                               required 
                               placeholder="Password"
                               class="pill-input w-full py-3.5 pl-6 pr-12 rounded-full text-sm font-medium">
                        
                        <!-- Lock Icon on the Right (exactly like mockup) -->
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-300/80">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Options Row: Remember Me (Left) & Forgot Password? (Right) -->
                    <div class="flex items-center justify-between px-2 pt-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none text-xs text-slate-200">
                            <input type="checkbox" 
                                   name="remember" 
                                   value="1" 
                                   class="w-4 h-4 rounded border-white/30 bg-white/10 text-amber-500 focus:ring-0 focus:ring-offset-0 cursor-pointer">
                            <span>Remember me</span>
                        </label>
                        <a href="javascript:void(0)" 
                           @click="fillAdmin()"
                           class="text-xs text-slate-300 hover:text-white transition font-medium">
                            Forgot password?
                        </a>
                    </div>

                    <!-- Pure White Solid Pill Submit Button (exactly like mockup) -->
                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full py-3.5 px-6 rounded-full bg-white text-slate-950 font-bold text-sm sm:text-base tracking-wide hover:bg-slate-100 active:scale-[0.99] transition shadow-[0_10px_25px_rgba(0,0,0,0.35)] cursor-pointer flex items-center justify-center">
                            Login
                        </button>
                    </div>

                    <!-- Bottom Text: Don't have an account? Register / Authorized Access -->
                    <div class="text-center text-xs text-slate-300/80 pt-3">
                        <span>Don't have an account?</span> 
                        <a href="javascript:void(0)" 
                           @click="showStaffPills = !showStaffPills"
                           class="font-semibold text-white underline decoration-white/40 hover:decoration-white transition ml-1 cursor-pointer">
                            Staff Access
                        </a>
                    </div>

                    <!-- Discreet Quick-Fill Access Drawer for Staff -->
                    <div x-show="showStaffPills" 
                         x-transition.opacity.duration.200ms
                         class="pt-3 border-t border-white/15 flex flex-wrap items-center justify-center gap-2 text-[11px]">
                        <button type="button" 
                                @click="fillAdmin()"
                                class="px-3 py-1 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 text-slate-200 transition">
                            Admin (Muse)
                        </button>
                        <button type="button" 
                                @click="fillSuperadmin()"
                                class="px-3 py-1 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 text-slate-200 transition">
                            Superadmin
                        </button>
                    </div>
                </form>
            </div>

            <!-- Author & Hospital Credit Caption -->
            <p class="text-center text-[11px] text-slate-400/80 mt-4 font-medium tracking-wide">
                Kaafi Hospitals &bull; Designed &amp; Developed by SEHTECH Healthcare Solutions (Mohammad Sulieman)
            </p>
        </div>
    </main>

    <!-- FOOTER: System & Security Status -->
    <footer class="relative z-20 w-full px-6 py-3 text-center text-[11px] text-slate-400/60 font-mono">
        &copy; {{ date('Y') }} Kaafi Hospitals. Protected Healthcare Operations System.
    </footer>

    <!-- ======================================================== -->
    <!-- INTERACTIVE PULL-ROPE NATURAL DAMPED SPRING PHYSICS ENGINE -->
    <!-- ======================================================== -->
    <script>
        function lampScene() {
            return {
                lightOn: true,
                showHint: true,
                showStaffPills: false,

                // Rope anchor and coordinates relative to rope SVG (viewBox 0 0 140 220)
                anchorX: 20,
                anchorY: 0,
                restX: 20,
                restY: 120,
                
                // Current handle position
                hx: 20,
                hy: 120,
                
                // Physics velocities
                vx: 0,
                vy: 0,
                
                // Interaction tracking
                isDragging: false,
                dragStartY: 0,
                dragStartHy: 0,
                dragStartTime: 0,
                svgRect: null,

                initScene() {
                    // Start physics animation loop
                    this.hx = this.restX;
                    this.hy = this.restY;
                    this.updateRopeGraphics();
                    requestAnimationFrame(() => this.physicsLoop());

                    // Global pointer events for smooth dragging outside element bounds
                    window.addEventListener('pointermove', (e) => this.onCordPointerMove(e));
                    window.addEventListener('pointerup', (e) => this.onCordPointerUp(e));
                    window.addEventListener('pointercancel', (e) => this.onCordPointerUp(e));
                },

                // Damped Harmonic Oscillator loop (Runs at 60/120 FPS smoothly)
                physicsLoop() {
                    if (!this.isDragging) {
                        // Spring stiffness and damping friction
                        const k = 0.085;
                        const damping = 0.86;

                        const ax = (this.restX - this.hx) * k;
                        const ay = (this.restY - this.hy) * k;

                        this.vx = (this.vx + ax) * damping;
                        this.vy = (this.vy + ay) * damping;

                        this.hx += this.vx;
                        this.hy += this.vy;

                        // Clamp tiny oscillations to rest
                        if (Math.abs(this.hx - this.restX) < 0.02 && Math.abs(this.hy - this.restY) < 0.02 &&
                            Math.abs(this.vx) < 0.02 && Math.abs(this.vy) < 0.02) {
                            this.hx = this.restX;
                            this.hy = this.restY;
                            this.vx = 0;
                            this.vy = 0;
                        }
                    }

                    this.updateRopeGraphics();
                    requestAnimationFrame(() => this.physicsLoop());
                },

                // Update the SVG rope curve and handle position
                updateRopeGraphics() {
                    const path = document.getElementById('rope-path');
                    const handle = document.getElementById('rope-handle-group');
                    if (!path || !handle) return;

                    // Calculate natural Catmull/Bézier sag and flex based on displacement & tension
                    const dx = this.hx - this.anchorX;
                    const dy = this.hy - this.anchorY;

                    // Mid control points for organic rope curvature
                    const cp1x = this.anchorX + dx * 0.25;
                    const cp1y = this.anchorY + dy * 0.45;
                    const cp2x = this.hx - dx * 0.20;
                    const cp2y = this.hy - dy * 0.20;

                    // Draw smooth cubic Bézier curve
                    path.setAttribute('d', `M ${this.anchorX} ${this.anchorY} C ${cp1x} ${cp1y}, ${cp2x} ${cp2y}, ${this.hx} ${this.hy}`);
                    
                    // Position the brass pull weight
                    handle.setAttribute('transform', `translate(${this.hx}, ${this.hy})`);
                },

                // Pointer Down: Grab rope
                onCordPointerDown(e) {
                    e.preventDefault();
                    this.isDragging = true;
                    this.dragStartTime = Date.now();
                    this.showHint = false;

                    const svg = document.getElementById('rope-svg');
                    if (svg) {
                        this.svgRect = svg.getBoundingClientRect();
                    }

                    const handle = document.getElementById('rope-handle-group');
                    if (handle) {
                        handle.classList.add('is-dragging');
                    }
                },

                // Pointer Move: Drag rope with natural flex
                onCordPointerMove(e) {
                    if (!this.isDragging || !this.svgRect) return;

                    // Convert client mouse/touch to SVG coordinates
                    const scaleX = 140 / this.svgRect.width;
                    const scaleY = 220 / this.svgRect.height;

                    const mouseSvgX = (e.clientX - this.svgRect.left) * scaleX;
                    const mouseSvgY = (e.clientY - this.svgRect.top) * scaleY;

                    // Clamped physics bounds
                    // Can swing horizontally between -10 and 60
                    this.hx = Math.max(-10, Math.min(65, mouseSvgX));
                    
                    // Can pull down from rest (120) up to 200 with soft elastic tension
                    const rawY = mouseSvgY;
                    if (rawY < this.restY - 20) {
                        this.hy = this.restY - 20; // Cannot push rope above lamp
                    } else if (rawY > this.restY) {
                        // Elastic resistance when stretched past rest
                        const stretch = rawY - this.restY;
                        this.hy = this.restY + stretch * 0.85;
                    } else {
                        this.hy = rawY;
                    }

                    this.vx = 0;
                    this.vy = 0;
                },

                // Pointer Up: Release cord, trigger switch and spring rebound
                onCordPointerUp(e) {
                    if (!this.isDragging) return;
                    this.isDragging = false;

                    const handle = document.getElementById('rope-handle-group');
                    if (handle) {
                        handle.classList.remove('is-dragging');
                    }

                    const pullDistance = this.hy - this.restY;
                    const duration = Date.now() - this.dragStartTime;

                    // If pulled down past 24px OR quickly clicked:
                    if (pullDistance > 24 || duration < 250) {
                        this.toggleLight();
                        // Give upward elastic spring kick
                        this.vy = -Math.max(14, pullDistance * 0.7);
                    } else {
                        // Gentle release rebound
                        this.vy = -pullDistance * 0.5;
                    }
                },

                // Programmatic impulse (for button or quick click)
                triggerPull() {
                    this.showHint = false;
                    // Apply downward impulse then snap
                    this.hy = this.restY + 45;
                    this.vy = -18;
                    this.toggleLight();
                },

                // Toggle light state with audio click feedback
                toggleLight() {
                    this.lightOn = !this.lightOn;
                    this.playSwitchSound(this.lightOn);
                },

                // Realistic mechanical switch click sound (Web Audio API)
                playSwitchSound(isTurnOn) {
                    try {
                        const AudioCtx = window.AudioContext || window.webkitAudioContext;
                        if (!AudioCtx) return;
                        const ctx = new AudioCtx();
                        if (ctx.state === 'suspended') ctx.resume();

                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();

                        osc.type = isTurnOn ? 'triangle' : 'sine';
                        osc.frequency.setValueAtTime(isTurnOn ? 180 : 130, ctx.currentTime);
                        osc.frequency.exponentialRampToValueAtTime(32, ctx.currentTime + 0.045);

                        gain.gain.setValueAtTime(0.25, ctx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.045);

                        osc.connect(gain);
                        gain.connect(ctx.destination);

                        osc.start();
                        osc.stop(ctx.currentTime + 0.05);
                    } catch(e) {}
                },

                // Quick staff credential fillers
                fillAdmin() {
                    document.getElementById('email').value = 'muse@kaafihospitals.org';
                    document.getElementById('password').value = '11111111';
                },

                fillSuperadmin() {
                    document.getElementById('email').value = 'superadmin@kaafi.org';
                    document.getElementById('password').value = '0905243072';
                }
            };
        }
    </script>
</body>
</html>
