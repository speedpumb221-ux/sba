@extends('layouts.app')

@section('title', 'لوحة الإدارة')

@section('content')
<h2 style="margin-bottom: 24px;">لوحة الإدارة</h2>

<!-- Navigation Buttons -->
<div class="admin-nav-grid" style="margin-bottom: 32px;">
    <a href="{{ route('admin.dashboard') }}" class="admin-nav-card active">
        <div class="admin-nav-icon">📊</div>
        <div class="admin-nav-title">لوحة التحكم</div>
        <div class="admin-nav-desc">نظرة عامة على النظام</div>
    </a>

    <a href="{{ route('admin.bumps') }}" class="admin-nav-card">
        <div class="admin-nav-icon">📍</div>
        <div class="admin-nav-title">إدارة المطبات</div>
        <div class="admin-nav-desc">مراجعة وإدارة المطبات</div>
    </a>

    <a href="{{ route('admin.users') }}" class="admin-nav-card">
        <div class="admin-nav-icon">👥</div>
        <div class="admin-nav-title">إدارة المستخدمين</div>
        <div class="admin-nav-desc">إدارة حسابات المستخدمين</div>
    </a>

    <a href="{{ route('admin.reports') }}" class="admin-nav-card">
        <div class="admin-nav-icon">📋</div>
        <div class="admin-nav-title">التقارير</div>
        <div class="admin-nav-desc">عرض وإدارة التقارير</div>
    </a>

    <a href="{{ route('admin.predictions') }}" class="admin-nav-card">
        <div class="admin-nav-icon">🧠</div>
        <div class="admin-nav-title">التوقعات</div>
        <div class="admin-nav-desc">إدارة التنبؤات الذكية</div>
    </a>

    <a href="{{ route('admin.testing') }}" class="admin-nav-card">
        <div class="admin-nav-icon">🧪</div>
        <div class="admin-nav-title">اختبار النظام</div>
        <div class="admin-nav-desc">محاكاة التنبيهات والإشعارات</div>
    </a>
</div>

<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px;">
    <div class="card">
        <div style="font-size: 32px; margin-bottom: 8px;">👥</div>
        <div style="font-size: 12px; color: var(--text-secondary);">إجمالي المستخدمين</div>
        <div style="font-size: 28px; font-weight: bold;">{{ $stats['total_users'] }}</div>
    </div>

    <div class="card">
        <div style="font-size: 32px; margin-bottom: 8px;">📍</div>
        <div style="font-size: 12px; color: var(--text-secondary);">إجمالي المطبات</div>
        <div style="font-size: 28px; font-weight: bold;">{{ $stats['total_bumps'] }}</div>
    </div>

    <div class="card">
        <div style="font-size: 32px; margin-bottom: 8px;">✅</div>
        <div style="font-size: 12px; color: var(--text-secondary);">المطبات المؤكدة</div>
        <div style="font-size: 28px; font-weight: bold;">{{ $stats['verified_bumps'] }}</div>
    </div>

    <div class="card">
        <div style="font-size: 32px; margin-bottom: 8px;">⏳</div>
        <div style="font-size: 12px; color: var(--text-secondary);">المطبات قيد الانتظار</div>
        <div style="font-size: 28px; font-weight: bold;">{{ $stats['pending_bumps'] }}</div>
    </div>

    <div class="card">
        <div style="font-size: 32px; margin-bottom: 8px;">📊</div>
        <div style="font-size: 12px; color: var(--text-secondary);">إجمالي التقارير</div>
        <div style="font-size: 28px; font-weight: bold;">{{ $stats['total_reports'] }}</div>
    </div>

    <div class="card">
        <div style="font-size: 32px; margin-bottom: 8px;">🧠</div>
        <div style="font-size: 12px; color: var(--text-secondary);">التنبؤات المعلقة</div>
        <div style="font-size: 28px; font-weight: bold;">{{ $stats['predicted_bumps'] }}</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
    <!-- Recent Bumps -->
    <div class="card">
        <div class="card-header">أحدث المطبات</div>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            @forelse($recentBumps as $bump)
                <div style="padding: 12px; background: var(--bg-secondary); border-radius: 6px;">
                    <div style="font-weight: 500;">{{ $bump->description ?? 'مطب' }}</div>
                    <div style="font-size: 12px; color: var(--text-secondary);">
                        @php
                            $confidencePercent = match($bump->confidence_level ?? 'medium') {
                                'high' => 90,
                                'medium' => 65,
                                'low' => 35,
                                default => 50
                            };
                        @endphp
                        المصدر: {{ $bump->source }} | الثقة: {{ $confidencePercent }}%
                    </div>
                    <div style="margin-top: 8px; display: flex; gap: 8px;">
                        @if(!$bump->is_verified)
                            <form action="{{ route('admin.bumps.approve', $bump) }}" method="POST" style="flex:1;">
                                @csrf
                                <button type="submit" class="btn btn-primary" style="font-size: 12px; width:100%;">✓</button>
                            </form>
                        @endif
                        <form action="{{ route('admin.bumps.reject', $bump) }}" method="POST" style="flex:1;">
                            @csrf
                            <button type="submit" class="btn btn-danger" style="font-size: 12px; width:100%;">✗</button>
                        </form>
                    </div>
                </div>
            @empty
                <p style="color: var(--text-secondary); text-align: center;">لا توجد مطبات</p>
            @endforelse
        </div>
    </div>

    <!-- Recent Users -->
    <div class="card">
        <div class="card-header">أحدث المستخدمين</div>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            @forelse($users as $user)
                <div style="padding: 12px; background: var(--bg-secondary); border-radius: 6px;">
                    <div style="font-weight: 500;">{{ $user->name }}</div>
                    <div style="font-size: 12px; color: var(--text-secondary);">{{ $user->email }}</div>
                    <div style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">
                        {{ $user->created_at->format('Y-m-d') }}
                    </div>
                </div>
            @empty
                <p style="color: var(--text-secondary); text-align: center;">لا توجد مستخدمين</p>
            @endforelse
        </div>
    </div>
</div>

<div style="margin-top: 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;">
    <a href="{{ route('admin.users') }}" class="btn btn-primary">👥 إدارة المستخدمين</a>
    <a href="{{ route('admin.bumps') }}" class="btn btn-primary">📍 إدارة المطبات</a>
    <a href="{{ route('admin.reports') }}" class="btn btn-primary">📊 إدارة التقارير</a>
    <a href="{{ route('admin.predictions') }}" class="btn btn-primary">🧠 إدارة التنبؤات</a>
</div>
@endsection
