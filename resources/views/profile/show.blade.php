@extends('layouts.app')

@section('title', 'الملف الشخصي')

@section('content')
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px;">
    <!-- Profile Info -->
    <div class="card">
        <div class="card-header">معلومات الحساب</div>
        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">الاسم</label>
                <input type="text" id="name" name="name" value="{{ $user->name }}" required>
            </div>
            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" value="{{ $user->email }}" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">حفظ التغييرات</button>
        </form>
    </div>

    <!-- Statistics -->
    <div class="card">
        <div class="card-header">إحصائياتي</div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div style="text-align: center; padding: 16px; background: var(--bg-secondary); border-radius: 6px;">
                <div style="font-size: 24px; font-weight: bold; color: var(--primary);">{{ $bumpsAdded }}</div>
                <div style="font-size: 12px; color: var(--text-secondary);">مطبات أضفتها</div>
            </div>
            <div style="text-align: center; padding: 16px; background: var(--bg-secondary); border-radius: 6px;">
                <div style="font-size: 24px; font-weight: bold; color: var(--primary);">{{ $reportsCount }}</div>
                <div style="font-size: 12px; color: var(--text-secondary);">تقارير أرسلتها</div>
            </div>
        </div>
    </div>
</div>

<!-- Settings -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header" data-i18n="stats.statistics">{{ __('messages.Settings') ?? 'الإعدادات' }}</div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
        <div class="form-group">
            <label for="language" data-i18n="nav.profile">{{ __('messages.Language') ?? 'اللغة' }}</label>
            <select id="language" onchange="updateSetting('language', this.value)">
                <option value="ar" {{ $settings->language === 'ar' ? 'selected' : '' }}>العربية</option>
                <option value="en" {{ $settings->language === 'en' ? 'selected' : '' }}>English</option>
            </select>
        </div>
        <div class="form-group">
            <label for="theme">المظهر</label>
            <select id="theme" onchange="updateSetting('theme', this.value)">
                <option value="light" {{ $settings->theme === 'light' ? 'selected' : '' }}>فاتح</option>
                <option value="dark" {{ $settings->theme === 'dark' ? 'selected' : '' }}>غامق</option>
            </select>
        </div>
        <div class="form-group">
            <label for="alert-distance" data-i18n="label.alert_distance">مسافة التنبيه</label>
            <select id="alert-distance" onchange="updateSetting('alert_distance', this.value)">
                <option value="50" {{ $settings->alert_distance === 50 ? 'selected' : '' }}>50 متر</option>
                <option value="100" {{ $settings->alert_distance === 100 ? 'selected' : '' }}>100 متر</option>
                <option value="200" {{ $settings->alert_distance === 200 ? 'selected' : '' }}>200 متر</option>
            </select>
        </div>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-top: 16px;">
        <label style="display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" id="notifications" {{ $settings->notifications_enabled ? 'checked' : '' }} onchange="updateSetting('notifications_enabled', this.checked)">
            <span data-i18n="nav.notifications">تفعيل الإشعارات</span>
        </label>
        <label style="display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" id="sound" {{ $settings->sound_enabled ? 'checked' : '' }} onchange="updateSetting('sound_enabled', this.checked)">
            <span data-i18n="nav.sound">تفعيل الصوت</span>
        </label>
        <label style="display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" id="gps" {{ $settings->gps_enabled ? 'checked' : '' }} onchange="updateSetting('gps_enabled', this.checked)">
            <span data-i18n="nav.gps">تفعيل GPS</span>
        </label>
        <label style="display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" id="motion" {{ $settings->motion_tracking_enabled ? 'checked' : '' }} onchange="updateSetting('motion_tracking_enabled', this.checked)">
            <span data-i18n="nav.motion">تتبع الحركة</span>
        </label>
    </div>
</div>

<!-- Change Password -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header">تغيير كلمة المرور</div>
    <form action="{{ route('profile.password') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="current_password">كلمة المرور الحالية</label>
            <input type="password" id="current_password" name="current_password" required>
        </div>
        <div class="form-group">
            <label for="password">كلمة المرور الجديدة</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="password_confirmation">تأكيد كلمة المرور</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%;">تحديث كلمة المرور</button>
    </form>
</div>

<!-- Recent Activities -->
<div class="card">
    <div class="card-header">النشاطات الأخيرة</div>
    <div style="display: flex; flex-direction: column; gap: 12px;">
        @forelse($activities as $activity)
            <div style="padding: 12px; background: var(--bg-secondary); border-radius: 6px; border-right: 3px solid var(--primary);">
                <div style="font-weight: 500;">{{ $activity->description }}</div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">
                    {{ $activity->created_at->diffForHumans() }}
                </div>
            </div>
        @empty
            <p style="color: var(--text-secondary); text-align: center;">لا توجد نشاطات</p>
        @endforelse
    </div>
</div>

<script>
    function updateSetting(key, value) {
        // persist locally first so UI stays in sync even if server endpoint is missing
        try {
            localStorage.setItem('settings.' + key, typeof value === 'boolean' ? (value ? '1' : '0') : String(value));
        } catch (e) {
            console.warn('Failed to save setting locally', e);
        }

        // Apply some immediate effects for certain keys
        if (key === 'theme') {
            document.documentElement.setAttribute('data-theme', value);
        }
        if (key === 'language') {
            // simple client-side notice; full i18n reload not implemented here
            console.log('Language preference set to', value);
        }

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
        .then(response => {
            if (!response.ok) {
                console.warn('updateSetting: server returned', response.status);
                return null;
            }
            return response.json().catch(err => {
                console.warn('updateSetting: response not JSON', err);
                return null;
            });
        })
        .then(data => {
            if (data && data.success) {
                console.log('تم تحديث الإعداد على الخادم');
            } else {
                console.log('الإعداد محفوظ محلياً (الخادم غير متاح أو لم يرجع JSON)');
            }
        })
        .catch(err => {
            console.error('Error updating setting:', err);
        });
    }

    // Initialize settings UI from localStorage when profile page loads
    document.addEventListener('DOMContentLoaded', function() {
        const keys = ['language','theme','alert_distance','notifications_enabled','sound_enabled','gps_enabled','motion_tracking_enabled'];
        keys.forEach(function(k) {
            try {
                const v = localStorage.getItem('settings.' + k);
                if (v === null) return;
                const el = document.getElementById(k.replace(/\./g,'_')) || document.getElementById(k);
                if (!el) return;
                if (el.type === 'checkbox') {
                    el.checked = (v === '1' || v === 'true');
                } else {
                    el.value = (v === '1' || v === '0') ? (v === '1' ? '1' : '0') : v;
                }
                // apply immediate effects for theme
                if (k === 'theme') {
                    document.documentElement.setAttribute('data-theme', el.value);
                }
            } catch (e) {
                // ignore
            }
        });
    });
</script>
@endsection
