<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'تطبيق حواجز السرعة')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#1e40af">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icon-192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('icon-512.png') }}">
    <!-- Apple Touch Icon -->
    <link rel="apple-touch-icon" href="{{ asset('icon-192.png') }}">
    <!-- Splash screen for iOS (مثال، يمكن توليد المزيد حسب المقاسات المطلوبة) -->
    <link rel="apple-touch-startup-image" href="{{ asset('splash-640x1136.png') }}" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)">
    <!-- يمكنك توليد splash images أخرى لمقاسات مختلفة حسب الحاجة -->
    <script>
        // Ensure theme persists immediately on page load to avoid flash
        (function(){
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
    <style>
        :root {
            --primary: #1e40af;
            --primary-light: #3b82f6;
            --primary-dark: #1e3a8a;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --bg-primary: #ffffff;
            --bg-secondary: #f9fafb;
            --border-color: #e5e7eb;
        }

        [data-theme="dark"] {
            --text-primary: #f3f4f6;
            --text-secondary: #d1d5db;
            --bg-primary: #1f2937;
            --bg-secondary: #111827;
            --border-color: #374151;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            line-height: 1.6;
            transition: background-color 0.3s, color 0.3s;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 16px;
        }

        /* Header */
        header {
            background-color: var(--bg-primary);
            border-bottom: 1px solid var(--border-color);
            padding: 12px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 16px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary);
        }

        .nav-links {
            display: flex;
            gap: 24px;
            align-items: center;
            list-style: none;
        }

        .nav-links a {
            color: var(--text-primary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .theme-toggle, .lang-toggle {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            padding: 8px;
            border-radius: 4px;
            transition: background-color 0.3s;
            color: var(--text-primary);
        }

        .theme-toggle:hover, .lang-toggle:hover {
            background-color: var(--bg-secondary);
        }

        /* Main Content */
        main {
            min-height: calc(100vh - 200px);
            padding: 24px 0;
        }

        /* Footer */
        footer {
            background-color: var(--bg-primary);
            border-top: 1px solid var(--border-color);
            padding: 24px 0;
            text-align: center;
            color: var(--text-secondary);
            font-size: 14px;
            margin-top: 48px;
        }

        /* Alert Messages */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #7f1d1d;
            border: 1px solid #fecaca;
        }

        .alert-warning {
            background-color: #fef3c7;
            color: #78350f;
            border: 1px solid #fcd34d;
        }

        /* Buttons */
        .btn {
            padding: 10px 16px;
            border-radius: 6px;
            border: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-secondary {
            background-color: var(--gray-200);
            color: var(--text-primary);
        }

        .btn-secondary:hover {
            background-color: var(--gray-300);
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        /* Forms */
        .form-group {
            margin-bottom: 16px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 14px;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            transition: border-color 0.3s;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }

        /* Cards */
        .card {
            background-color: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .card-header {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-color);
        }

        /* Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: var(--bg-primary);
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-around;
            padding: 8px 0;
            z-index: 50;
        }

        .bottom-nav a {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            text-decoration: none;
            color: var(--text-secondary);
            font-size: 12px;
            padding: 8px;
            transition: color 0.3s;
        }

        .bottom-nav a:hover,
        .bottom-nav a.active {
            color: var(--primary);
        }

        main {
            padding-bottom: 100px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .header-content {
                justify-content: center;
            }

            .theme-toggle, .lang-toggle {
                display: none; /* Hide on mobile, can be added to bottom nav if needed */
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- PWA Install Banner -->
    <div id="pwa-install-banner" style="display:none;position:fixed;bottom:24px;left:0;right:0;z-index:9999;text-align:center;">
        <div style="display:inline-block;background:#1e40af;color:#fff;padding:16px 32px;border-radius:12px;box-shadow:0 2px 12px rgba(30,64,175,0.15);">
            <span style="font-size:20px;vertical-align:middle;">🚗</span>
            <span style="margin:0 12px;vertical-align:middle;">أضف التطبيق إلى الشاشة الرئيسية</span>
            <button id="pwa-install-btn" style="background:#fff;color:#1e40af;border:none;padding:8px 18px;border-radius:8px;font-weight:bold;cursor:pointer;margin-right:8px;">تثبيت</button>
            <button id="pwa-install-close" style="background:none;color:#fff;border:none;font-size:20px;cursor:pointer;vertical-align:middle;">×</button>
        </div>
    </div>
    <header>
        <div class="header-content">
            <div class="logo">🚗 <span data-i18n="app.name">{{ __('messages.Speed Bumps App') }}</span></div>
            <nav>
                <ul class="nav-links">
                    @auth
                        <li><a href="{{ route('dashboard') }}"><span data-i18n="nav.dashboard">{{ __('messages.Dashboard') }}</span></a></li>
                        <li><a href="{{ route('bumps.map') }}"><span data-i18n="nav.map">{{ __('messages.Map') }}</span></a></li>
                        <li><a href="{{ route('profile.show') }}"><span data-i18n="nav.profile">{{ __('messages.Profile') }}</span></a></li>
                        @if(Auth::user()->is_admin)
                            <li><a href="{{ route('admin.dashboard') }}"><span data-i18n="nav.admin">{{ __('messages.Administration') }}</span></a></li>
                        @endif
                        <li><button class="theme-toggle" onclick="toggleTheme()" title="تبديل الوضع">🌙</button></li>
                        <li><button class="lang-toggle" onclick="toggleLanguage()" title="تبديل اللغة">🇺🇸</button></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-secondary"><span data-i18n="action.logout">{{ __('messages.Logout') }}</span></button>
                            </form>
                        </li>
                    @else
                        <li><a href="{{ route('login') }}"><span data-i18n="auth.login">{{ __('messages.Login') }}</span></a></li>
                        <li><a href="{{ route('register') }}"><span data-i18n="auth.register">{{ __('messages.Register') }}</span></a></li>
                        <li><button class="theme-toggle" onclick="toggleTheme()" title="تبديل الوضع">🌙</button></li>
                        <li><button class="lang-toggle" onclick="toggleLanguage()" title="تبديل اللغة">🇺🇸</button></li>
                    @endauth
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        @if($errors->any())
            @foreach($errors->all() as $error)
                <div class="alert alert-error">
                    <span>⚠️</span>
                    <span>{{ $error }}</span>
                </div>
            @endforeach
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                <span>✓</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <nav class="bottom-nav">
        <a href="{{ route('dashboard') }}" class="@if(request()->routeIs('dashboard')) active @endif">
            <span>📊</span>
            <span data-i18n="nav.home">{{ __('messages.Home') }}</span>
        </a>
        <a href="{{ route('bumps.map') }}" class="@if(request()->routeIs('bumps.map')) active @endif">
            <span>🗺️</span>
            <span data-i18n="nav.map">{{ __('messages.Map') }}</span>
        </a>
        <a href="{{ route('bumps.index') }}" class="@if(request()->routeIs('bumps.index')) active @endif">
            <span>📍</span>
            <span data-i18n="stats.speed_bumps">{{ __('messages.Speed Bumps') }}</span>
        </a>
        <a href="{{ route('profile.show') }}" class="@if(request()->routeIs('profile.show')) active @endif">
            <span>👤</span>
            <span data-i18n="nav.profile">{{ __('messages.Profile') }}</span>
        </a>
    </nav>

    <footer>
        <p>&copy; 2024 {{ __('messages.Speed Bumps App') }}. {{ __('messages.All rights reserved') }}.</p>
    </footer>

    <script>
    // PWA Install Banner Logic
    let deferredPrompt;
    const banner = document.getElementById('pwa-install-banner');
    const installBtn = document.getElementById('pwa-install-btn');
    const closeBtn = document.getElementById('pwa-install-close');
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        banner.style.display = 'block';
    });
    if (installBtn) {
        installBtn.onclick = function() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        banner.style.display = 'none';
                    }
                });
            }
        };
    }
    if (closeBtn) {
        closeBtn.onclick = function() {
            banner.style.display = 'none';
        };
    }
        // Theme Toggle
        @auth
        const userTheme = '{{ Auth::user()->getSettingsOrCreate()->theme ?? 'light' }}';
        @else
        const userTheme = null;
        @endauth
        const theme = localStorage.getItem('theme') || userTheme || 'light';
        document.documentElement.setAttribute('data-theme', theme);
        updateThemeIcon();

        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-theme');
            const newTheme = current === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon();

            // Update user settings if logged in
            @auth
            updateUserSetting('theme', newTheme);
            @endauth
            // map theme switching disabled — map stays in light mode
        }

        function updateThemeIcon() {
            const themeBtn = document.querySelector('.theme-toggle');
            const currentTheme = document.documentElement.getAttribute('data-theme');
            themeBtn.textContent = currentTheme === 'light' ? '🌙' : '☀️';
            themeBtn.title = currentTheme === 'light' ? 'الوضع المظلم' : 'الوضع الفاتح';
        }


        // Simple client-side translations for immediate UI updates without reload
        const translations = {
            ar: {
                'app.name': 'تطبيق حواجز السرعة',
                'nav.dashboard': 'لوحة التحكم',
                'nav.map': 'الخريطة',
                'nav.profile': 'الملف الشخصي',
                'nav.admin': 'الادارة',
                'auth.login': 'تسجيل الدخول',
                'auth.register': 'تسجيل',
                'action.logout': 'تسجيل الخروج',
                'nav.home': 'الرئيسية',
                'action.start_tracking': '▶️ بدء التتبع',
                'action.stop': '⏹️ إيقاف',
                'label.alert_distance': 'مسافة التنبيه',
                'label.meters': 'متر',
                'stats.statistics': 'الإحصائيات',
                'stats.speed_bumps': 'مطبات',
                'stats.verified': 'مؤكد',
                'stats.alerts': 'تنبيهات',
                'warning.title': 'تحذير',
                'warning.speed_bump_ahead': 'مطب أمامك',
                'map.add_bump_confirm': 'هل تريد إضافة مطب هنا؟'
            },
            en: {
                'app.name': 'Speed Bumps App',
                'nav.dashboard': 'Dashboard',
                'nav.map': 'Map',
                'nav.profile': 'Profile',
                'nav.admin': 'Administration',
                'auth.login': 'Login',
                'auth.register': 'Register',
                'action.logout': 'Logout',
                'nav.home': 'Home',
                'action.start_tracking': '▶️ Start Tracking',
                'action.stop': '⏹️ Stop',
                'label.alert_distance': 'Alert Distance',
                'label.meters': 'meters',
                'stats.statistics': 'Statistics',
                'stats.speed_bumps': 'Speed Bumps',
                'stats.verified': 'Verified',
                'stats.alerts': 'Alerts',
                'warning.title': 'Warning',
                'warning.speed_bump_ahead': 'Speed bump ahead',
                'map.add_bump_confirm': 'Add a bump here?'
            }
        };

        // Language Toggle
        @auth
        const userLanguage = '{{ Auth::user()->getSettingsOrCreate()->language ?? 'ar' }}';
        @else
        const userLanguage = null;
        @endauth
        const language = localStorage.getItem('language') || userLanguage || 'ar';
        updateLanguageIcon();
        // Apply language attributes and client-side translations on load
        document.documentElement.lang = language;
        document.documentElement.dir = language === 'ar' ? 'rtl' : 'ltr';
        if (typeof applyTranslations === 'function') applyTranslations(language);

        function toggleLanguage() {
            const currentLang = localStorage.getItem('language') || 'ar';
            const newLang = currentLang === 'ar' ? 'en' : 'ar';

            // Update stored language
            localStorage.setItem('language', newLang);
            updateLanguageIcon();

            // Update user settings if logged in
            @auth
            updateUserSetting('language', newLang);
            @endauth

            // Apply immediately without reloading: update attributes and translate in-place
            document.documentElement.lang = newLang;
            document.documentElement.dir = newLang === 'ar' ? 'rtl' : 'ltr';
            applyTranslations(newLang);
        }

        function updateLanguageIcon() {
            const langBtn = document.querySelector('.lang-toggle');
            const currentLang = localStorage.getItem('language') || 'ar';
            langBtn.textContent = currentLang === 'ar' ? '🇺🇸' : '🇸🇦';
            langBtn.title = currentLang === 'ar' ? 'Switch to English' : 'التبديل إلى العربية';
        }

        function applyTranslations(lang) {
            try {
                console.debug('applyTranslations:', lang);
                const dict = translations[lang] || translations['ar'];
                document.querySelectorAll('[data-i18n]').forEach(el => {
                    const key = el.getAttribute('data-i18n');
                    if (dict[key]) el.textContent = dict[key];
                });
                document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
                    const key = el.getAttribute('data-i18n-placeholder');
                    if (dict[key]) el.placeholder = dict[key];
                });
            } catch (e) { console.warn('applyTranslations error', e); }
        }

        function updateUserSetting(setting, value) {
            return fetch('/api/settings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    [setting]: value
                })
            })
            .then(response => {
                if (!response.ok) {
                    console.warn('updateUserSetting: non-OK response', response.status);
                    return null;
                }
                return response.json().catch(err => {
                    console.warn('updateUserSetting: response not JSON', err);
                    return null;
                });
            })
            .then(data => {
                if (data && data.success) {
                    console.log('Setting updated:', data);
                }
            })
            .catch(error => console.error('Error updating setting:', error));
        }

        // Register Service Worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('{{ asset("service-worker.js") }}')
                .then(registration => console.log('Service Worker registered'))
                .catch(error => console.log('Service Worker registration failed:', error));
        }

        // CSRF Token for AJAX
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    </script>

    @yield('scripts')
</body>
</html>
