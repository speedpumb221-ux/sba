@extends('layouts.app')

@section('title', 'إدارة المطبات')

@section('content')
<h2 style="margin-bottom: 24px;">إدارة المطبات</h2>

<!-- Navigation Buttons -->
<div class="admin-nav-grid" style="margin-bottom: 32px;">
    <a href="{{ route('admin.dashboard') }}" class="admin-nav-card">
        <div class="admin-nav-icon">📊</div>
        <div class="admin-nav-title">لوحة التحكم</div>
        <div class="admin-nav-desc">نظرة عامة على النظام</div>
    </a>

    <a href="{{ route('admin.bumps') }}" class="admin-nav-card active">
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
</div>

<div class="card">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border-color);">
                <th style="padding: 12px; text-align: right;">الموقع</th>
                <th style="padding: 12px; text-align: right;">المصدر</th>
                <th style="padding: 12px; text-align: right;">الثقة</th>
                <th style="padding: 12px; text-align: right;">التقارير</th>
                <th style="padding: 12px; text-align: right;">الحالة</th>
                <th style="padding: 12px; text-align: right;">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bumps as $bump)
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 12px;">{{ $bump->latitude }}, {{ $bump->longitude }}</td>
                    <td style="padding: 12px;">{{ $bump->source }}</td>
                    @php
                        $confidencePercent = match($bump->confidence_level ?? 'medium') {
                            'high' => 90,
                            'medium' => 65,
                            'low' => 35,
                            default => 50
                        };
                    @endphp
                    <td style="padding: 12px;">{{ $confidencePercent }}%</td>
                    <td style="padding: 12px;">{{ $bump->report_count }}</td>
                    <td style="padding: 12px;">
                        <span style="background: {{ $bump->is_verified ? '#10b981' : '#f59e0b' }}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                            {{ $bump->is_verified ? 'مؤكد' : 'معلق' }}
                        </span>
                    </td>
                    <td style="padding: 12px;">
                        @if(!$bump->is_verified)
                            <form action="{{ route('admin.bumps.approve', $bump) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-primary" style="font-size: 12px; padding: 6px 12px;">✓</button>
                            </form>
                        @endif
                        <form action="{{ route('admin.bumps.reject', $bump) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-danger" style="font-size: 12px; padding: 6px 12px;">✗</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding: 24px; text-align: center; color: var(--text-secondary);">
                        لا توجد مطبات
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($bumps->hasPages())
    <div style="margin-top: 24px; display: flex; justify-content: center; gap: 8px;">
        {{ $bumps->links() }}
    </div>
@endif

<div style="margin-top: 24px;">
    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">← العودة</a>
</div>
@endsection
