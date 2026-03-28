@extends('layouts.app')

@section('title', 'الملف الشخصي')

@section('content')
<div class="profile-container">
    <!-- Profile Header -->
    <div class="card mb-xl">
        <div class="profile-header">
            <div class="profile-avatar">
                <span class="profile-avatar-icon">👤</span>
            </div>
            <div class="profile-info">
                <h1 class="profile-name">{{ $user->name }}</h1>
                <p class="profile-email">{{ $user->email }}</p>
                @if($user->is_admin)
                    <span class="badge badge-primary">مسؤول النظام</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-2 mb-xl">
        <div class="stat-card">
            <div class="stat-card-icon">📍</div>
            <div class="stat-card-label">المطبات المضافة</div>
            <div class="stat-card-value">{{ $bumpsAdded }}</div>
        </div>
        <div class="stat-card success">
            <div class="stat-card-icon">📊</div>
            <div class="stat-card-label">التقارير المرسلة</div>
            <div class="stat-card-value">{{ $reportsCount }}</div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="card mb-xl">
        <div class="tabs" data-tabs>
            <button class="tab-button active" data-tab="account">
                <span>👤</span>
                <span>معلومات الحساب</span>
            </button>
            <button class="tab-button" data-tab="settings">
                <span>⚙️</span>
                <span>الإعدادات</span>
            </button>
            <button class="tab-button" data-tab="security">
                <span>🔒</span>
                <span>الأمان</span>
            </button>
            <button class="tab-button" data-tab="activity">
                <span>📝</span>
                <span>النشاطات</span>
            </button>
        </div>

        <!-- Account Tab -->
        <div class="tab-content active" data-content="account">
            <form action="{{ route('profile.update') }}" method="POST" class="form-section">
                @csrf
                
                <div class="form-group">
                    <label for="name" class="form-label-required">الاسم</label>
                    <input type="text" id="name" name="name" value="{{ $user->name }}" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label-required">البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" value="{{ $user->email }}" required>
                </div>

                <div class="form-group">
                    <label for="phone">رقم الهاتف</label>
                    <input type="tel" id="phone" name="phone" value="{{ $user->phone ?? '' }}" placeholder="+966...">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg">
                        💾 حفظ التغييرات
                    </button>
                </div>
            </form>
        </div>

        <!-- Settings Tab -->
        <div class="tab-content" data-content="settings">
            <div class="form-section">
                <h3 class="form-section-title">التفضيلات العامة</h3>
                
                <div class="grid grid-2 gap-lg">
                    <div class="form-group">
                        <label for="language">اللغة</label>
                        <select id="language" onchange="updateSetting('language', this.value)">
                            <option value="ar" {{ $settings->language === 'ar' ? 'selected' : '' }}>العربية</option>
                            <option value="en" {{ $settings->language === 'en' ? 'selected' : '' }}>English</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="alert-distance">مسافة التنبيه</label>
                        <select id="alert-distance" onchange="updateSetting('alert_distance', this.value)">
                            <option value="50" {{ $settings->alert_distance === 50 ? 'selected' : '' }}>50 متر</option>
                            <option value="100" {{ $settings->alert_distance === 100 ? 'selected' : '' }}>100 متر</option>
                            <option value="200" {{ $settings->alert_distance === 200 ? 'selected' : '' }}>200 متر</option>
                        </select>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <form id="logout-form" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-block">🚪 تسجيل الخروج</button>
                    </form>
                </div>

                <h3 class="form-section-title mt-xl">الإشعارات والصوت</h3>
                
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="notifications" {{ $settings->notifications_enabled ? 'checked' : '' }} onchange="updateSetting('notifications_enabled', this.checked)">
                        <span class="checkbox-text">
                            <span class="checkbox-title">🔔 تفعيل الإشعارات</span>
                            <span class="checkbox-description">استقبل تنبيهات عند الاقتراب من مطب</span>
                        </span>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" id="sound" {{ $settings->sound_enabled ? 'checked' : '' }} onchange="updateSetting('sound_enabled', this.checked)">
                        <span class="checkbox-text">
                            <span class="checkbox-title">🔊 تفعيل الصوت</span>
                            <span class="checkbox-description">تشغيل صوت التنبيه</span>
                        </span>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" id="gps" {{ $settings->gps_enabled ? 'checked' : '' }} onchange="updateSetting('gps_enabled', this.checked)">
                        <span class="checkbox-text">
                            <span class="checkbox-title">📍 تفعيل GPS</span>
                            <span class="checkbox-description">السماح بالوصول إلى موقعك</span>
                        </span>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" id="motion" {{ $settings->motion_tracking_enabled ? 'checked' : '' }} onchange="updateSetting('motion_tracking_enabled', this.checked)">
                        <span class="checkbox-text">
                            <span class="checkbox-title">📊 تتبع الحركة</span>
                            <span class="checkbox-description">تتبع حركتك لتحسين التنبيهات</span>
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Security Tab -->
        <div class="tab-content" data-content="security">
            <form action="{{ route('profile.password') }}" method="POST" class="form-section">
                @csrf
                
                <h3 class="form-section-title">تغيير كلمة المرور</h3>
                
                <div class="form-group">
                    <label for="current_password" class="form-label-required">كلمة المرور الحالية</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label-required">كلمة المرور الجديدة</label>
                    <input type="password" id="password" name="password" required>
                    <div class="form-help">يجب أن تكون 8 أحرف على الأقل</div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label-required">تأكيد كلمة المرور</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg">
                        🔒 تحديث كلمة المرور
                    </button>
                </div>
            </form>

            <div class="divider"></div>

            <div class="form-section">
                <h3 class="form-section-title">الأمان والخصوصية</h3>
                <div class="alert alert-info">
                    <span class="alert-icon">ℹ</span>
                    <div class="alert-content">
                        <div class="alert-title">نصائح الأمان</div>
                        <ul style="margin: var(--spacing-md) 0; padding-right: var(--spacing-lg);">
                            <li>استخدم كلمة مرور قوية وفريدة</li>
                            <li>لا تشارك بيانات حسابك مع أحد</li>
                            <li>قم بتحديث كلمة المرور بشكل دوري</li>
                            <li>تحقق من الأنشطة المريبة</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Tab -->
        <div class="tab-content" data-content="activity">
            <div class="form-section">
                <h3 class="form-section-title">النشاطات الأخيرة</h3>
                
                @forelse($activities as $activity)
                    <div class="activity-item">
                        <div class="activity-icon">{{ $activity->icon ?? '📝' }}</div>
                        <div class="activity-content">
                            <div class="activity-title">{{ $activity->description }}</div>
                            <div class="activity-time">
                                {{ $activity->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center p-lg">
                        <div style="font-size: 48px; margin-bottom: var(--spacing-md);">📭</div>
                        <p class="text-muted">لا توجد نشاطات حالياً</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
    .profile-container {
        animation: fadeIn 0.3s ease;
    }

    /* Profile Header */
    .profile-header {
        display: flex;
        align-items: center;
        gap: var(--spacing-xl);
        padding: var(--spacing-xl) 0;
        border-bottom: 1px solid var(--border-color);
    }

    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: var(--shadow-md);
    }

    .profile-avatar-icon {
        font-size: 40px;
    }

    .profile-info {
        flex: 1;
    }

    .profile-name {
        margin: 0;
        font-size: var(--font-size-2xl);
    }

    .profile-email {
        color: var(--text-secondary);
        margin: var(--spacing-xs) 0 var(--spacing-md);
    }

    /* Tabs */
    .tabs {
        display: flex;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: var(--spacing-lg);
        gap: 0;
        overflow-x: auto;
    }

    .tab-button {
        background: none;
        border: none;
        padding: var(--spacing-md) var(--spacing-lg);
        cursor: pointer;
        color: var(--text-secondary);
        font-weight: 600;
        font-size: var(--font-size-sm);
        position: relative;
        transition: color 0.3s ease;
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        white-space: nowrap;
    }

    .tab-button:hover {
        color: var(--text-primary);
    }

    .tab-button.active {
        color: var(--primary);
    }

    .tab-button.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        right: 0;
        height: 2px;
        background-color: var(--primary);
    }

    .tab-content {
        display: none;
        animation: fadeIn 0.3s ease;
    }

    .tab-content.active {
        display: block;
    }

    /* Form Section */
    .form-section {
        padding: var(--spacing-lg) 0;
    }

    .form-section:not(:last-child) {
        border-bottom: 1px solid var(--border-color);
    }

    .form-section-title {
        font-size: var(--font-size-lg);
        font-weight: 600;
        margin-bottom: var(--spacing-lg);
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        gap: var(--spacing-md);
        margin-top: var(--spacing-xl);
        padding-top: var(--spacing-lg);
        border-top: 1px solid var(--border-color);
    }

    /* Checkbox Group */
    .checkbox-group {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-lg);
    }

    .checkbox-label {
        display: flex;
        align-items: flex-start;
        gap: var(--spacing-md);
        padding: var(--spacing-md);
        border-radius: var(--radius-md);
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .checkbox-label:hover {
        background-color: var(--bg-secondary);
    }

    .checkbox-label input[type="checkbox"] {
        width: 20px;
        height: 20px;
        margin-top: 2px;
        cursor: pointer;
        flex-shrink: 0;
    }

    .checkbox-text {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-xs);
    }

    .checkbox-title {
        font-weight: 600;
        color: var(--text-primary);
    }

    .checkbox-description {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }

    /* Activity Items */
    .activity-item {
        display: flex;
        gap: var(--spacing-md);
        padding: var(--spacing-md);
        border-radius: var(--radius-md);
        background-color: var(--bg-secondary);
        margin-bottom: var(--spacing-md);
        border-right: 3px solid var(--primary);
    }

    [dir="rtl"] .activity-item {
        border-right: none;
        border-left: 3px solid var(--primary);
    }

    .activity-icon {
        font-size: var(--font-size-2xl);
        flex-shrink: 0;
    }

    .activity-content {
        flex: 1;
    }

    .activity-title {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: var(--spacing-xs);
    }

    .activity-time {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }

    /* Responsive */
    @media (max-width: 640px) {
        .profile-header {
            flex-direction: column;
            text-align: center;
            gap: var(--spacing-lg);
        }

        .profile-avatar {
            width: 64px;
            height: 64px;
        }

        .profile-avatar-icon {
            font-size: 32px;
        }

        .tabs {
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-md);
        }

        .tab-button {
            padding: var(--spacing-sm) var(--spacing-md);
            font-size: var(--font-size-xs);
        }

        .tab-button span:last-child {
            display: none;
        }

        .grid-2 {
            grid-template-columns: 1fr;
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection

@section('scripts')
<script>
    /**
     * Update Setting
     */
    function updateSetting(key, value) {
        // Save locally
        try {
            localStorage.setItem('settings.' + key, typeof value === 'boolean' ? (value ? '1' : '0') : String(value));
        } catch (e) {
            console.warn('Failed to save setting locally', e);
        }

        // Apply immediate effects
        if (key === 'theme') {
            document.documentElement.setAttribute('data-theme', value);
        }

        if (key === 'language') {
            // Store preference and refresh locale + direction
            document.documentElement.setAttribute('lang', value);
            document.documentElement.setAttribute('dir', value === 'ar' ? 'rtl' : 'ltr');

            // Show loading indicator
            const loadingDiv = document.createElement('div');
            loadingDiv.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.9);
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                color: #333;
            `;
            loadingDiv.innerHTML = 'جاري تحديث اللغة...';
            document.body.appendChild(loadingDiv);

            // Force immediate reload to apply new locale and direction
            window.location.reload(true);
        }

        // Send to server
        fetch('/api/settings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                [key]: value
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                AlertManager.create('تم تحديث الإعداد', 'success', 2000);
            }
        })
        .catch(err => {
            console.error('Error updating setting:', err);
            AlertManager.create('حدث خطأ في تحديث الإعداد', 'error', 2000);
        });
    }

    /**
     * Initialize Tabs
     */
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const tabId = button.getAttribute('data-tab');

                // Remove active class from all buttons and contents
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));

                // Add active class to clicked button and corresponding content
                button.classList.add('active');
                document.querySelector(`[data-content="${tabId}"]`).classList.add('active');
            });
        });

        // Restore settings from localStorage
        const keys = ['language', 'alert_distance', 'notifications_enabled', 'sound_enabled', 'gps_enabled', 'motion_tracking_enabled'];
        keys.forEach(function(k) {
            try {
                const v = localStorage.getItem('settings.' + k);
                if (v === null) return;
                const el = document.getElementById(k.replace(/\./g, '_')) || document.getElementById(k);
                if (!el) return;
                if (el.type === 'checkbox') {
                    el.checked = (v === '1' || v === 'true');
                } else {
                    el.value = v;
                }
            } catch (e) {
                // ignore
            }
        });
    });
</script>
@endsection
