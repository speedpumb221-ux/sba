<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', __('messages.Speed Bumps App'))</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="تطبيق حواجز السرعة - تطبيق ذكي لتتبع وتقرير حواجز السرعة">
    <meta name="theme-color" content="#1e40af">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    
    <!-- Icons -->
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icon-192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('icon-512.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icon-192.png') }}">
    
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('styles')
    
    <style>
        /* Admin Navigation Styles */
        .admin-nav-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }

        .admin-nav-card {
            display: block;
            background: var(--bg-primary);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 20px;
            text-decoration: none;
            color: var(--text-primary);
            transition: all 0.3s ease;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .admin-nav-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .admin-nav-card.active {
            border-color: var(--primary-color);
            background: linear-gradient(135deg, var(--primary-color-light), var(--primary-color));
            color: white;
        }

        .admin-nav-card.active .admin-nav-desc {
            color: rgba(255,255,255,0.8);
        }

        .admin-nav-icon {
            font-size: 32px;
            margin-bottom: 12px;
            display: block;
        }

        .admin-nav-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
            display: block;
        }

        .admin-nav-desc {
            font-size: 12px;
            color: var(--text-secondary);
            line-height: 1.4;
            display: block;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .admin-nav-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 12px;
            }

            .admin-nav-card {
                padding: 16px;
            }

            .admin-nav-icon {
                font-size: 28px;
                margin-bottom: 8px;
            }

            .admin-nav-title {
                font-size: 14px;
            }

            .admin-nav-desc {
                font-size: 11px;
            }
        }

        @media (max-width: 480px) {
            .admin-nav-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .admin-nav-card {
                padding: 14px;
            }

            .admin-nav-icon {
                font-size: 24px;
            }
        }

        /* Stats cards responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)) !important;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }
    </style>
    
    <!-- Theme Persistence Script -->
    <script>
        (function() {
            try {
                var saved = localStorage.getItem('theme');
                @auth
                var userTheme = '{{ Auth::user()->getSettingsOrCreate()->theme ?? '' }}';
                if (!saved && userTheme) saved = userTheme;
                @endauth
                if (saved) document.documentElement.setAttribute('data-theme', saved);
            } catch (e) {
                console.warn('Theme persistence failed', e);
            }
        })();
    </script>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-content">
            <div class="logo">
                <span class="logo-icon">🚗</span>
                <span>{{ __('messages.Speed Bumps App') }}</span>
            </div>
            
            <div class="header-actions">
                <button id="theme-toggle" class="theme-toggle" title="{{ __('messages.Switch to Dark Mode') }}" aria-label="{{ __('messages.Switch to Dark Mode') }}">
                    🌙
                </button>
                <div style="display:inline-block; margin-left:8px;">
                    <button id="lang-switch" class="theme-toggle" title="{{ app()->getLocale() === 'ar' ? __('messages.Switch to English') : __('messages.Switch to Arabic') }}" aria-label="language switch">{{ app()->getLocale() === 'ar' ? 'EN' : 'ع' }}</button>
                </div>
                @auth
                    <a href="{{ route('profile.show') }}" class="theme-toggle" title="الملف الشخصي" aria-label="الملف الشخصي">
                        👤
                    </a>
                    <form method="POST" action="{{ route('logout') }}" style="display:inline; margin-left:0.5rem;">
                        @csrf
                        <button type="submit" class="theme-toggle" title="تسجيل الخروج" aria-label="تسجيل الخروج" style="background:none; border:none; cursor:pointer; font-size:1.2rem;">🚪</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-sm btn-primary">دخول</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <!-- Alert Messages -->
        @if ($errors->any())
            <div class="alert alert-error">
                <span class="alert-icon">✕</span>
                <div class="alert-content">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                <span class="alert-icon">✓</span>
                <div class="alert-content">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                <span class="alert-icon">✕</span>
                <div class="alert-content">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning">
                <span class="alert-icon">⚠</span>
                <div class="alert-content">
                    {{ session('warning') }}
                </div>
            </div>
        @endif

        <!-- Page Content -->
        @yield('content')
    </main>

    <!-- Floating Action Button -->
    @auth
        <a href="{{ route('bumps.create') }}" class="fab" title="إضافة مطب جديد" aria-label="إضافة مطب جديد">
            ➕
        </a>
    @endauth

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        @auth
            <a href="{{ route('dashboard') }}" class="nav-item">
                <span class="bottom-nav-icon">🏠</span>
                <span class="bottom-nav-label">{{ __('messages.Home') }}</span>
            </a>
            <a href="{{ route('bumps.map') }}" class="nav-item">
                <span class="bottom-nav-icon">🗺️</span>
                <span class="bottom-nav-label">{{ __('messages.Map') }}</span>
            </a>
            <a href="{{ route('bumps.index') }}" class="nav-item">
                <span class="bottom-nav-icon">📍</span>
                <span class="bottom-nav-label">{{ __('messages.Speed Bumps') }}</span>
            </a>
            <a href="{{ route('profile.show') }}" class="nav-item">
                <span class="bottom-nav-icon">👤</span>
                <span class="bottom-nav-label">{{ __('messages.Profile') }}</span>
            </a>
            @if(Auth::user()->is_admin)
                <a href="{{ route('admin.dashboard') }}" class="nav-item">
                    <span class="bottom-nav-icon">⚙️</span>
                    <span class="bottom-nav-label">الإدارة</span>
                </a>
            @endif
        @else
            <a href="{{ route('login') }}" class="nav-item">
                <span class="bottom-nav-icon">🔐</span>
                <span class="bottom-nav-label">دخول</span>
            </a>
            <a href="{{ route('register') }}" class="nav-item">
                <span class="bottom-nav-icon">📝</span>
                <span class="bottom-nav-label">تسجيل</span>
            </a>
        @endauth
    </nav>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        (function(){
            var btn = document.getElementById('lang-switch');
            if(!btn) return;
            btn.addEventListener('click', function(){
                try{
                    var next = '{{ app()->getLocale() === "ar" ? "en" : "ar" }}';
                    window.location.href = '/locale/' + next;
                }catch(e){ console.warn('Language switch failed', e); }
            });
        })();
    </script>
    <script>
    (function(){
        function playAlertSound(type){
            try{
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const o = ctx.createOscillator();
                const g = ctx.createGain();
                const freq = (type === 'danger') ? 880 : (type === 'warning') ? 660 : 440;
                o.type = 'sine'; o.frequency.value = freq;
                g.gain.value = 0.001;
                o.connect(g); g.connect(ctx.destination);
                o.start();
                g.gain.exponentialRampToValueAtTime(0.2, ctx.currentTime + 0.01);
                g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.5);
                setTimeout(()=>{ try{ o.stop(); ctx.close(); } catch(e){} }, 600);
            }catch(e){}
        }

        function showBrowserNotification(title, body){
            try{
                if(!('Notification' in window)) return;
                if(Notification.permission === 'granted'){
                    const n = new Notification(title, { body: body });
                    setTimeout(()=> n.close(), 5000);
                }
            }catch(e){}
        }

        function triggerAlert(title, body, type = 'info'){
            try{
                playAlertSound(type);
                try{ if(navigator.vibrate) navigator.vibrate(type === 'danger' ? [200,100,200] : [100,50]); }catch(e){}
                showBrowserNotification(title, body);
            }catch(e){}
        }

        window.triggerAlert = triggerAlert;

        // Request notification permission once (non-blocking)
        try{ if('Notification' in window && Notification.permission !== 'granted'){ Notification.requestPermission().catch(()=>{}); } }catch(e){}
    })();
    </script>

    <script>
    (function(){
        // Listen for server-side speed bump broadcasts if Echo is configured
        try{
            if(window.Echo && typeof window.Echo.channel === 'function'){
                window.Echo.channel('speed-bumps').listen('.SpeedBumpDetected', function(e){
                    try{
                        var title = 'مطب جديد بالقرب منك';
                        var body = (e && e.latitude && e.longitude) ? ('موقع: ' + e.latitude.toFixed(5) + ',' + e.longitude.toFixed(5)) : 'تم اكتشاف مطب جديد.';
                        window.triggerAlert(title, body, 'warning');
                    }catch(err){ console.warn('speed-bump handler', err); }
                });
            }
        }catch(e){ console.warn('Realtime listener not available', e); }
    })();
    </script>

    {{-- Bust cached service-worker copy when updating alert script by adding version param --}}
    <button id="pwa-install-btn" aria-hidden="true" style="display:none; position:fixed; bottom:88px; left:16px; z-index:200000; padding:10px 14px; border-radius:10px; background:#059669; color:#fff; border:none; box-shadow:0 8px 20px rgba(0,0,0,0.18); font-weight:600;">تثبيت التطبيق</button>

    <script>
    (function(){
        var btn = document.getElementById('pwa-install-btn');
        var deferredPrompt = null;

        window.addEventListener('beforeinstallprompt', function(e){
            try{ e.preventDefault(); }catch(_){ }
            deferredPrompt = e;
            if(btn){ btn.style.display = 'block'; btn.setAttribute('aria-hidden','false'); }
        });

        function hideBtn(){ if(btn){ btn.style.display = 'none'; btn.setAttribute('aria-hidden','true'); } }

        if(btn){
            btn.addEventListener('click', function(){
                if(!deferredPrompt) return;
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(function(choice){
                    if(choice && choice.outcome === 'accepted'){ hideBtn(); }
                    deferredPrompt = null;
                }).catch(function(){ deferredPrompt = null; });
            });
        }

        window.addEventListener('appinstalled', function(){
            deferredPrompt = null; hideBtn();
            try{ if(window.AlertManager) AlertManager.create('تم تثبيت التطبيق','success',2000); }catch(e){}
        });
    })();
    </script>

    <script src="{{ asset('js/nearby-alerts-fixed.js') }}?v=2"></script>
    @yield('scripts')
</body>
</html>
