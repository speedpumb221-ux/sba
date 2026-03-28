@extends('layouts.app')

@section('title', __('messages.Dashboard'))

@section('content')
<div class="dashboard-container">
    <!-- Welcome Section -->
    <div class="welcome-section mb-xl">
        <h1>مرحباً {{ Auth::user()->name }}</h1>
        <p class="text-muted">تابع نشاطك وإحصائياتك في تطبيق حواجز السرعة</p>
    </div>

    <!-- Statistics Grid -->
    <div class="grid grid-2 mb-xl">
        <!-- Total Speed Bumps -->
        <div class="stat-card">
            <div class="stat-card-icon">📍</div>
            <div class="stat-card-label">{{ __('messages.Total Speed Bumps') }}</div>
            <div class="stat-card-value">{{ \App\Models\SpeedBump::count() }}</div>
        </div>

        <!-- Verified Bumps -->
        <div class="stat-card success">
            <div class="stat-card-icon">✅</div>
            <div class="stat-card-label">{{ __('messages.Verified Bumps') }}</div>
            <div class="stat-card-value">{{ \App\Models\SpeedBump::where('is_verified', true)->count() }}</div>
        </div>

        <!-- Your Reports -->
        <div class="stat-card warning">
            <div class="stat-card-icon">📊</div>
            <div class="stat-card-label">{{ __('messages.Your Reports') }}</div>
            <div class="stat-card-value">{{ Auth::user()->reports()->count() }}</div>
        </div>

        <!-- Predictions -->
        <div class="stat-card info">
            <div class="stat-card-icon">🧠</div>
            <div class="stat-card-label">{{ __('messages.Predictions') }}</div>
            <div class="stat-card-value">{{ \App\Models\Prediction::where('is_converted', false)->count() }}</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mb-xl">
        <div class="card-header">
            <span class="card-header-icon">⚡</span>
            <span>{{ __('messages.Quick Actions') }}</span>
        </div>
        <div class="card-body">
            <div class="grid grid-2 gap-md">
                <a href="{{ route('bumps.map') }}" class="btn btn-primary btn-block">
                    <span>🗺️</span>
                    <span>{{ __('messages.View Map') }}</span>
                </a>
                <a href="{{ route('bumps.index') }}" class="btn btn-primary btn-block">
                    <span>📍</span>
                    <span>{{ __('messages.List of Bumps') }}</span>
                </a>
                <a href="{{ route('profile.show') }}" class="btn btn-secondary btn-block">
                    <span>👤</span>
                    <span>{{ __('messages.Personal Profile') }}</span>
                </a>
                @if(Auth::user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-block">
                        <span>⚙️</span>
                        <span>الإدارة</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    @php
        $recentReports = Auth::user()->reports()->latest()->take(5)->get();
    @endphp
    
    @if($recentReports->count() > 0)
        <div class="card mb-xl">
            <div class="card-header">
                <span class="card-header-icon">📝</span>
                <span>{{ __('messages.Recent Reports') }}</span>
            </div>
            <div class="card-body">
                <ul class="list">
                    @foreach($recentReports as $report)
                        <li class="list-item">
                            <div>
                                <div class="font-semibold">{{ $report->speedBump->location ?? 'موقع غير معروف' }}</div>
                                <div class="text-muted" style="font-size: var(--font-size-xs);">
                                    {{ \Carbon\Carbon::parse($report->created_at)->diffForHumans() }}
                                </div>
                            </div>
                            <span class="badge badge-primary">{{ $report->type }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- How to Use Section -->
    <div class="card">
        <div class="card-header">
            <span class="card-header-icon">❓</span>
            <span>{{ __('messages.How to Use') }}</span>
        </div>
        <div class="card-body">
            <div class="space-y-md">
                <div class="flex gap-md">
                    <div class="text-2xl">1️⃣</div>
                    <div>
                        <div class="font-semibold">{{ __('messages.View speed bumps') }}</div>
                        <p class="text-muted">استخدم الخريطة لعرض جميع حواجز السرعة المسجلة في منطقتك</p>
                    </div>
                </div>

                <div class="flex gap-md">
                    <div class="text-2xl">2️⃣</div>
                    <div>
                        <div class="font-semibold">{{ __('messages.Add a bump') }}</div>
                        <p class="text-muted">اضغط على زر الإضافة أو انقر على الخريطة لتسجيل مطب جديد</p>
                    </div>
                </div>

                <div class="flex gap-md">
                    <div class="text-2xl">3️⃣</div>
                    <div>
                        <div class="font-semibold">{{ __('messages.Alerts') }}</div>
                        <p class="text-muted">سيتم تنبيهك تلقائياً عند الاقتراب من مطب (50-200 متر)</p>
                    </div>
                </div>

                <div class="flex gap-md">
                    <div class="text-2xl">4️⃣</div>
                    <div>
                        <div class="font-semibold">{{ __('messages.Reports') }}</div>
                        <p class="text-muted">أرسل تقارير لتأكيد أو رفض المطبات الموجودة</p>
                    </div>
                </div>

                <div class="flex gap-md">
                    <div class="text-2xl">5️⃣</div>
                    <div>
                        <div class="font-semibold">الإحصائيات</div>
                        <p class="text-muted">تابع نشاطك وإحصائياتك في الملف الشخصي</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
    .dashboard-container {
        animation: fadeIn 0.3s ease;
    }

    .welcome-section {
        padding: var(--spacing-lg) 0;
        border-bottom: 1px solid var(--border-color);
    }

    .welcome-section h1 {
        margin-bottom: var(--spacing-sm);
    }

    .space-y-md > * + * {
        margin-top: var(--spacing-md);
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

    @media (max-width: 640px) {
        .grid-2 {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection
