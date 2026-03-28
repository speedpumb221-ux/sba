@extends('layouts.app')

@section('title', 'إدارة المستخدمين')

@section('content')
<h2 style="margin-bottom: 24px;">إدارة المستخدمين</h2>

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

    <a href="{{ route('admin.users') }}" class="admin-nav-card active">
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
                <th style="padding: 12px; text-align: right;">الاسم</th>
                <th style="padding: 12px; text-align: right;">البريد الإلكتروني</th>
                <th style="padding: 12px; text-align: right;">النوع</th>
                <th style="padding: 12px; text-align: right;">تاريخ التسجيل</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 12px;">{{ $user->name }}</td>
                    <td style="padding: 12px;">{{ $user->email }}</td>
                    <td style="padding: 12px;">
                        <span style="background: {{ $user->is_admin ? '#10b981' : '#3b82f6' }}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                            {{ $user->is_admin ? 'مسؤول' : 'مستخدم' }}
                        </span>
                    </td>
                    <td style="padding: 12px;">{{ $user->created_at->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="padding: 24px; text-align: center; color: var(--text-secondary);">
                        لا توجد مستخدمين
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($users->hasPages())
    <div style="margin-top: 24px; display: flex; justify-content: center; gap: 8px;">
        {{ $users->links() }}
    </div>
@endif

<div style="margin-top: 24px;">
    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">← العودة</a>
</div>
@endsection
