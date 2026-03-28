@extends('layouts.app')

@section('title', 'إدارة التقارير')

@section('content')
<h2 style="margin-bottom: 24px;">إدارة التقارير</h2>

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

    <a href="{{ route('admin.reports') }}" class="admin-nav-card active">
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
                <th style="padding: 12px; text-align: right;">المستخدم</th>
                <th style="padding: 12px; text-align: right;">المطب</th>
                <th style="padding: 12px; text-align: right;">نوع التقرير</th>
                <th style="padding: 12px; text-align: right;">الوصف</th>
                <th style="padding: 12px; text-align: right;">التاريخ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $report)
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 12px;">{{ $report->user->name }}</td>
                    <td style="padding: 12px;">
                        {{ $report->speedBump->latitude }}, {{ $report->speedBump->longitude }}
                    </td>
                    <td style="padding: 12px;">
                        <span style="background: #3b82f6; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                            {{ $report->report_type }}
                        </span>
                    </td>
                    <td style="padding: 12px;">{{ Str::limit($report->description, 50) }}</td>
                    <td style="padding: 12px;">{{ $report->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="padding: 24px; text-align: center; color: var(--text-secondary);">
                        لا توجد تقارير
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($reports->hasPages())
    <div style="margin-top: 24px; display: flex; justify-content: center; gap: 8px;">
        {{ $reports->links() }}
    </div>
@endif

<div style="margin-top: 24px;">
    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">← العودة</a>
</div>
@endsection
