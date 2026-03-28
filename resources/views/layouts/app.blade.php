<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', 'تطبيق حواجز السرعة')</title>
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
                <span>حواجز السرعة</span>
            </div>
            
            <div class="header-actions">
                <button id="theme-toggle" class="theme-toggle" title="تبديل الوضع الليلي" aria-label="تبديل الوضع الليلي">
                    🌙
                </button>
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
                <span class="bottom-nav-label">الرئيسية</span>
            </a>
            <a href="{{ route('bumps.map') }}" class="nav-item">
                <span class="bottom-nav-icon">🗺️</span>
                <span class="bottom-nav-label">الخريطة</span>
            </a>
            <a href="{{ route('bumps.index') }}" class="nav-item">
                <span class="bottom-nav-icon">📍</span>
                <span class="bottom-nav-label">المطبات</span>
            </a>
            <a href="{{ route('profile.show') }}" class="nav-item">
                <span class="bottom-nav-icon">👤</span>
                <span class="bottom-nav-label">الملف الشخصي</span>
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
    <script src="{{ asset('js/nearby-alerts-fixed.js') }}?v=2"></script>
    @yield('scripts')
</body>
</html>
