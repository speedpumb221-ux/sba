@extends('layouts.app')

@section('title', __('messages.Dashboard'))

@section('content')
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px;">
    <div class="card">
        <div style="font-size: 32px; margin-bottom: 8px;">📍</div>
        <div style="font-size: 12px; color: var(--text-secondary);">{{ __('messages.Total Speed Bumps') }}</div>
        <div style="font-size: 28px; font-weight: bold;">{{ \App\Models\SpeedBump::count() }}</div>
    </div>

    <div class="card">
        <div style="font-size: 32px; margin-bottom: 8px;">✅</div>
        <div style="font-size: 12px; color: var(--text-secondary);">{{ __('messages.Verified Bumps') }}</div>
        <div style="font-size: 28px; font-weight: bold;">{{ \App\Models\SpeedBump::where('is_verified', true)->count() }}</div>
    </div>

    <div class="card">
        <div style="font-size: 32px; margin-bottom: 8px;">📊</div>
        <div style="font-size: 12px; color: var(--text-secondary);">{{ __('messages.Your Reports') }}</div>
        <div style="font-size: 28px; font-weight: bold;">{{ Auth::user()->reports()->count() }}</div>
    </div>

    <div class="card">
        <div style="font-size: 32px; margin-bottom: 8px;">🧠</div>
        <div style="font-size: 12px; color: var(--text-secondary);">{{ __('messages.Predictions') }}</div>
        <div style="font-size: 28px; font-weight: bold;">{{ \App\Models\Prediction::where('is_converted', false)->count() }}</div>
    </div>
</div>

<div class="card">
    <div class="card-header">{{ __('messages.Quick Actions') }}</div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;">
        <a href="{{ route('bumps.map') }}" class="btn btn-primary">🗺️ {{ __('messages.View Map') }}</a>
        <a href="{{ route('bumps.index') }}" class="btn btn-primary">📍 {{ __('messages.List of Bumps') }}</a>
        <a href="{{ route('profile.show') }}" class="btn btn-secondary">👤 {{ __('messages.Personal Profile') }}</a>
        @if(Auth::user()->is_admin)
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">⚙️ الإدارة</a>
        @endif
    </div>
</div>

<div class="card" style="margin-top: 24px;">
    <div class="card-header">{{ __('messages.How to Use') }}</div>
    <div style="line-height: 1.8;">
        <p><strong>1. {{ __('messages.View speed bumps') }}:</strong> {{ __('messages.View speed bumps') }} Google Maps لعرض جميع حواجز السرعة</p>
        <p><strong>2. {{ __('messages.Add a bump') }}:</strong> اضغط على الخريطة لإضافة مطب جديد</p>
        <p><strong>3. {{ __('messages.Alerts') }}:</strong> سيتم تنبيهك عند الاقتراب من مطب (50-200 متر)</p>
        <p><strong>4. {{ __('messages.Reports') }}:</strong> أرسل تقارير لتأكيد أو رفض المطبات</p>
        <p><strong>5. الإحصائيات:</strong> تابع نشاطك في الملف الشخصي</p>
    </div>
</div>
@endsection
