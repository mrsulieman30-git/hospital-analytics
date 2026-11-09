<!DOCTYPE html>
<html lang="en" class="h-screen max-h-screen overflow-hidden bg-[#030d22]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="description" content="Kaafi Hospitals Specialized Clinical Analytics & Operations Intelligence Portal">
    <title>Kaafi Hospitals — Clinical Analytics &amp; Operations Portal</title>
    <link rel="icon" type="image/png" href="{{ asset('images/kaafi_logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html, body {
            height: 100vh;
            max-height: 100vh;
            overflow: hidden;
            margin: 0;
            padding: 0;
            touch-action: pan-x pan-y;
        }

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
            background: rgba(18, 28, 54, 0.45);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.65), 0 0 40px rgba(255, 220, 130, 0.12);
        }

        .glass-login-card.card-dimmed {
            background: rgba(8, 14, 28, 0.65);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.85);
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
<body class="h-screen max-h-screen w-screen overflow-hidden fixed inset-0 bg-[#030d22] text-slate-100 antialiased selection:bg-amber-400 selection:text-slate-950 flex flex-col justify-between"
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
        <div class="w-[560px] h-[480px] rounded-full bg-radial from-amber-200/25 via-amber-400/10 to-transparent blur-3xl -translate-y-8"></div>
    </div>

    <!-- TOP BAR: Clean Minimalist Header (Top-left logo removed as requested) -->
    <header class="relative z-20 w-full px-5 py-2 sm:px-8 flex items-center justify-end shrink-0">
        <!-- Light Status Badge (Clickable alternative to the rope switch) -->
        <button type="button" 
                @click="triggerPull()"
                class="px-2.5 py-1 rounded-full text-xs font-mono backdrop-blur-md border transition-all duration-300 flex items-center gap-1.5 cursor-pointer"
                :class="lightOn ? 'bg-amber-400/15 border-amber-400/40 text-amber-200 hover:bg-amber-400/25' : 'bg-slate-800/60 border-slate-700 text-slate-400 hover:bg-slate-700/60'">
            <span class="w-2 h-2 rounded-full transition-colors duration-300"
                  :class="lightOn ? 'bg-amber-400 shadow-[0_0_8px_#fbbf24]' : 'bg-slate-500'"></span>
            <span x-text="lightOn ? 'Light ON' : 'Light OFF'"></span>
        </button>
    </header>

    <!-- MAIN SCENE: Auto-Adjustable & Non-Scrollable Centered View -->
    <main class="relative z-10 flex-1 flex flex-col items-center justify-center px-4 py-1 sm:py-2 min-h-0">
        
        <!-- ======================================================== -->
        <!-- LAMP & INTERACTIVE PULL-CORD ASSEMBLY                    -->
        <!-- ======================================================== -->
        <div class="relative w-full max-w-[420px] flex flex-col items-center mb-1 sm:mb-2 z-30 shrink-0">
            
            <!-- Wall Lamp Fixture (Trapezoidal Sconce matching reference) -->
            <div class="relative z-20 flex flex-col items-center">
                <!-- Mounting Bracket Plate -->
                <div class="w-14 h-1.5 bg-slate-900 rounded-t-sm shadow-md border-t border-slate-700/60"></div>
                
                <!-- Main Trapezoid Metal Lamp Fixture -->
                <div class="w-24 sm:w-28 h-10 sm:h-11 bg-gradient-to-b from-[#182a4d] via-[#0d1a33] to-[#081224] lamp-trapezoid shadow-2xl relative overflow-hidden flex flex-col justify-end border-t border-cyan-400/20">
                    <!-- Subtle metallic top rim highlight -->
                    <div class="absolute inset-x-2 top-0.5 h-[1px] bg-gradient-to-r from-transparent via-cyan-300/40 to-transparent"></div>

                    <!-- Underside Glowing Glass Diffuser Bar -->
                    <div class="w-full h-2.5 sm:h-3 transition-all duration-500 ease-out flex items-center justify-center"
                         :class="lightOn ? 'bg-gradient-to-r from-amber-200 via-white to-amber-200 shadow-[0_0_30px_10px_rgba(255,235,160,0.95),0_0_60px_20px_rgba(245,158,11,0.7)]' : 'bg-[#0a1424] border-t border-slate-800 shadow-none'">
                        <!-- Core light filament line when lit -->
                        <div class="w-3/4 h-[1.5px] transition-opacity duration-300"
                             :class="lightOn ? 'bg-white opacity-100' : 'opacity-0'"></div>
                    </div>
                </div>

                <!-- Hotspot Bloom underneath the lamp -->
                <div class="absolute -bottom-6 pointer-events-none transition-opacity duration-500 ease-out"
                     :class="lightOn ? 'opacity-100 scale-100' : 'opacity-0 scale-90'">
                    <div class="w-40 h-16 bg-radial from-amber-100/90 via-amber-300/40 to-transparent blur-md"></div>
                </div>
            </div>

            <!-- INTERACTIVE PULL ROPE / CORD WITH NATURAL SPRING PHYSICS -->
            <div class="absolute top-[38px] sm:top-[42px] left-[calc(50%+40px)] sm:left-[calc(50%+46px)] w-[140px] h-[190px] pointer-events-none z-40">
                <svg id="rope-svg" 
                     class="w-full h-full overflow-visible pointer-events-none" 
                     viewBox="0 0 140 190">
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
                          d="M 20 0 Q 20 50 20 100" 
                          stroke="url(#brassGradient)" 
                          stroke-width="2.2" 
                          stroke-linecap="round"
                          fill="none" />

                    <!-- The Pull Knob / Handle Group (Draggable & Clickable) -->
                    <g id="rope-handle-group" 
                       transform="translate(20, 100)" 
                       class="cord-grabber pointer-events-auto"
                       @pointerdown="onCordPointerDown($event)">
                        
                        <!-- Invisible larger touch target for effortless grabbing -->
                        <circle cx="0" cy="10" r="24" fill="transparent" />

                        <!-- Top attachment bead ring -->
                        <circle cx="0" cy="0" r="2.8" fill="url(#brassGradient)" stroke="#78350f" stroke-width="0.5" />
                        
                        <!-- Elegant Teardrop Brass Pull Weight -->
                        <path d="M -3 3 L 3 3 L 5.5 15 C 5.5 19 3 22 0 22 C -3 22 -5.5 19 -5.5 15 Z" 
                              fill="url(#knobGradient)" 
                              stroke="#78350f" 
                              stroke-width="0.8" 
                              filter="url(#knobGlow)" />
                        
                        <!-- Bottom small metallic ring bead -->
                        <circle cx="0" cy="23" r="2.2" fill="url(#brassGradient)" />

                        <!-- Specular highlight on pull handle -->
                        <path d="M -1.2 5 L 1.2 5 L 1.8 14 C 1.8 16 0.5 18 0 18" 
                              stroke="rgba(255,255,255,0.7)" 
                              stroke-width="0.8" 
                              stroke-linecap="round" 
                              fill="none" />
                    </g>
                </svg>

                <!-- Floating Pull Hint Tooltip (disappears once pulled) -->
                <div x-show="showHint" 
                     x-transition.opacity.duration.400ms
                     class="absolute left-[34px] top-[90px] bg-slate-900/90 border border-amber-400/40 text-amber-200 text-[10px] font-semibold px-2 py-0.5 rounded-full whitespace-nowrap shadow-xl pointer-events-none cord-hint-anim flex items-center gap-1 backdrop-blur-md">
                    <span>💡 Pull to toggle</span>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- FLOATING GLASSMORPHISM LOGIN CARD                        -->
        <!-- Auto-adjustable to screen size, fits within 100vh        -->
        <!-- ======================================================== -->
        <div class="w-full max-w-[360px] sm:max-w-[385px] transition-all duration-500 ease-out shrink-0">
            <div class="glass-login-card rounded-2xl sm:rounded-3xl p-5 sm:p-7 text-white transition-all duration-500"
                 :class="{ 'card-dimmed': !lightOn }">

                <!-- Header: Clean Crest & Bold "Login" title -->
                <div class="text-center mb-4 sm:mb-5">
                    <div class="inline-flex items-center justify-center mb-1.5">
                        <img src="{{ asset('images/kaafi_logo.png') }}" 
                             alt="Kaafi Hospitals" 
                             class="h-8 sm:h-9 w-auto object-contain drop-shadow-[0_2px_10px_rgba(255,255,255,0.2)]">
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                        Login
                    </h2>
                    <p class="text-[11px] text-slate-300/80 mt-0.5 font-medium">
                        Kaafi Hospitals Operations &amp; Analytics Portal
                    </p>
                </div>

                <!-- Error Messages Box -->
                @if($errors->any())
                    <div class="mb-4 p-2.5 rounded-xl bg-rose-500/20 border border-rose-500/40 text-rose-200 text-xs font-semibold flex items-center gap-2 backdrop-blur-md">
                        <svg class="w-4 h-4 shrink-0 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-2.5 rounded-xl bg-rose-500/20 border border-rose-500/40 text-rose-200 text-xs font-semibold flex items-center gap-2 backdrop-blur-md">
                        <svg class="w-4 h-4 shrink-0 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Credentials Form (Registration removed completely) -->
                <form action="{{ route('login.post') }}" method="POST" class="space-y-3 sm:space-y-3.5">
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
                               class="pill-input w-full py-2.5 sm:py-3 pl-5 pr-11 rounded-full text-xs sm:text-sm font-medium">
                        
                        <!-- User Silhouette Icon on Right -->
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-300/80">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                               class="pill-input w-full py-2.5 sm:py-3 pl-5 pr-11 rounded-full text-xs sm:text-sm font-medium">
                        
                        <!-- Lock Icon on Right -->
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-300/80">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Options Row: Remember Me (Left) & Forgot Password / Admin Assist (Right) -->
                    <div class="flex items-center justify-between px-1.5 pt-0.5">
                        <label class="flex items-center gap-1.5 cursor-pointer select-none text-[11px] sm:text-xs text-slate-200">
                            <input type="checkbox" 
                                   name="remember" 
                                   value="1" 
                                   class="w-3.5 h-3.5 rounded border-white/30 bg-white/10 text-amber-500 focus:ring-0 focus:ring-offset-0 cursor-pointer">
                            <span>Remember me</span>
                        </label>
                        <a href="javascript:void(0)" 
                           @click="fillAdmin()"
                           class="text-[11px] sm:text-xs text-slate-300 hover:text-white transition font-medium">
                            Forgot password?
                        </a>
                    </div>

                    <!-- Pure White Solid Pill Submit Button -->
                    <div class="pt-1">
                        <button type="submit" 
                                class="w-full py-2.5 sm:py-3 px-6 rounded-full bg-white text-slate-950 font-bold text-xs sm:text-sm tracking-wide hover:bg-slate-100 active:scale-[0.99] transition shadow-[0_10px_25px_rgba(0,0,0,0.35)] cursor-pointer flex items-center justify-center">
                            Login
                        </button>
                    </div>
                </form>
            </div>

            <!-- Author & Hospital Credit Caption -->
            <p class="text-center text-[10px] sm:text-[11px] text-slate-400/75 mt-2.5 font-medium tracking-wide">
                Kaafi Hospitals &bull; Designed &amp; Developed by SEHTECH Healthcare Solutions (Mohammad Sulieman)
            </p>
        </div>
    </main>

    <!-- FOOTER: Security Status (Compact, non-scrollable) -->
    <footer class="relative z-20 w-full px-4 py-2 text-center text-[10px] text-slate-400/50 font-mono shrink-0">
        &copy; {{ date('Y') }} Kaafi Hospitals. Protected Healthcare System. Authorized Personnel Only.
    </footer>

    <!-- ======================================================== -->
    <!-- INTERACTIVE PULL-ROPE NATURAL DAMPED SPRING PHYSICS ENGINE -->
    <!-- ======================================================== -->
    <script>
        function lampScene() {
            return {
                lightOn: true,
                showHint: true,

                // Rope anchor and coordinates relative to rope SVG (viewBox 0 0 140 190)
                anchorX: 20,
                anchorY: 0,
                restX: 20,
                restY: 100,
                
                // Current handle position
                hx: 20,
                hy: 100,
                
                // Physics velocities
                vx: 0,
                vy: 0,
                
                // Interaction tracking
                isDragging: false,
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
                        const k = 0.09;
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

                    const scaleX = 140 / this.svgRect.width;
                    const scaleY = 190 / this.svgRect.height;

                    const mouseSvgX = (e.clientX - this.svgRect.left) * scaleX;
                    const mouseSvgY = (e.clientY - this.svgRect.top) * scaleY;

                    // Clamped physics bounds
                    this.hx = Math.max(-10, Math.min(60, mouseSvgX));
                    
                    const rawY = mouseSvgY;
                    if (rawY < this.restY - 15) {
                        this.hy = this.restY - 15;
                    } else if (rawY > this.restY) {
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

                    // If pulled down past 20px OR quickly clicked:
                    if (pullDistance > 20 || duration < 250) {
                        this.toggleLight();
                        this.vy = -Math.max(14, pullDistance * 0.7);
                    } else {
                        this.vy = -pullDistance * 0.5;
                    }
                },

                // Programmatic impulse (for button or quick click)
                triggerPull() {
                    this.showHint = false;
                    this.hy = this.restY + 40;
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
