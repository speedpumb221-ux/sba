@extends('layouts.app')

@section('title', 'إدارة التنبؤات')

@section('content')
<h2 style="margin-bottom: 24px;">إدارة التنبؤات الذكية</h2>

<!-- Navigation Buttons -->
<div class="admin-nav-grid" style="margin-bottom: 32px;">
    <a href="{{ route('admin.dashboard') }}" class="admin-nav-card">
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

    <a href="{{ route('admin.predictions') }}" class="admin-nav-card active">
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
                <th style="padding: 12px; text-align: right;">الدرجة</th>
                <th style="padding: 12px; text-align: right;">الاهتزازات</th>
                <th style="padding: 12px; text-align: right;">انخفاض السرعة</th>
                <th style="padding: 12px; text-align: right;">المستخدمون</th>
                <th style="padding: 12px; text-align: right;">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($predictions as $prediction)
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 12px;">{{ $prediction->latitude }}, {{ $prediction->longitude }}</td>
                    <td style="padding: 12px;">
                        <span style="background: {{ $prediction->score >= 15 ? '#10b981' : ($prediction->score >= 8 ? '#f59e0b' : '#ef4444') }}; color: white; padding: 4px 8px; border-radius: 4px;">
                            {{ $prediction->score }}
                        </span>
                    </td>
                    <td style="padding: 12px;">{{ $prediction->vibration_count }}</td>
                    <td style="padding: 12px;">{{ $prediction->speed_drop_count }}</td>
                    <td style="padding: 12px;">{{ $prediction->user_count }}</td>
                    <td style="padding: 12px;">
                        <form action="{{ route('admin.predictions.convert', $prediction) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-primary" style="font-size: 12px; padding: 6px 12px;">تحويل</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding: 24px; text-align: center; color: var(--text-secondary);">
                        لا توجد تنبؤات معلقة
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($predictions->hasPages())
    <div style="margin-top: 24px; display: flex; justify-content: center; gap: 8px;">
        {{ $predictions->links() }}
    </div>
@endif

<div style="margin-top: 24px;">
    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">← العودة</a>
</div>
@endsection
