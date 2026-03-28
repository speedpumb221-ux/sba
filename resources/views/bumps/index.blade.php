@extends('layouts.app')

@section('title', 'قائمة المطبات')

@section('content')
<div class="bumps-container">
    <!-- Success Messages -->
    @if(session('success'))
        <div class="alert alert-success mb-lg" style="padding: 15px; border-radius: 8px; background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0;">
            <strong>✅ {{ session('success') }}</strong>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error mb-lg" style="padding: 15px; border-radius: 8px; background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;">
            <strong>❌ {{ session('error') }}</strong>
        </div>
    @endif

    <!-- Page Header -->
    <div class="page-header mb-xl">
        <h1>قائمة حواجز السرعة</h1>
        <p class="text-muted">تصفح وأدر جميع المطبات المسجلة</p>
    </div>

    <!-- Filter and Search -->
    <div class="card mb-xl">
        <div class="grid grid-2 gap-md">
            <div class="form-group">
                <label for="search">البحث</label>
                <input type="text" id="search" placeholder="ابحث عن مطب..." onkeyup="filterBumps()">
            </div>
            <div class="form-group">
                <label for="filter-status">الحالة</label>
                <select id="filter-status" onchange="filterBumps()">
                    <option value="">الكل</option>
                    <option value="verified">مؤكد</option>
                    <option value="unverified">غير مؤكد</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Bumps List -->
    @forelse($bumps as $bump)
        <div class="bump-card" data-bump-id="{{ $bump->id }}" data-status="{{ $bump->is_verified ? 'verified' : 'unverified' }}">
            <div class="bump-card-header">
                <div class="bump-card-title">
                    <span class="bump-status-icon">{{ $bump->is_verified ? '✅' : '❓' }}</span>
                    <div>
                        <h3>{{ $bump->description ?? 'مطب سرعة' }}</h3>
                        <p class="text-muted">{{ $bump->location ?? 'موقع غير محدد' }}</p>
                    </div>
                </div>
                <div class="bump-card-badge">
                    @if($bump->is_verified)
                        <span class="badge badge-success">مؤكد</span>
                    @else
                        <span class="badge badge-warning">غير مؤكد</span>
                    @endif
                </div>
            </div>

            <div class="bump-card-body">
                <div class="bump-info-grid">
                    <div class="bump-info-item">
                        <span class="bump-info-label">📍 الموقع</span>
                        <span class="bump-info-value">
                            {{ number_format($bump->latitude, 4) }}, {{ number_format($bump->longitude, 4) }}
                        </span>
                    </div>

                    <div class="bump-info-item">
                        <span class="bump-info-label">📊 التقارير</span>
                        <span class="bump-info-value">{{ $bump->reports_count ?? 0 }}</span>
                    </div>

                    <div class="bump-info-item">
                        <span class="bump-info-label">🎯 مستوى الثقة</span>
                        <div class="confidence-bar">
                            @php
                                $confidencePercent = match($bump->confidence_level ?? 'medium') {
                                    'high' => 90,
                                    'medium' => 65,
                                    'low' => 35,
                                    default => 50
                                };
                            @endphp
                            <div class="confidence-fill" style="width: {{ $confidencePercent }}%"></div>
                            <span class="confidence-text">{{ $confidencePercent }}%</span>
                        </div>
                    </div>

                    <div class="bump-info-item">
                        <span class="bump-info-label">📌 المصدر</span>
                        <span class="bump-info-value">{{ $bump->source ?? 'مستخدم' }}</span>
                    </div>

                    @if($bump->type)
                        <div class="bump-info-item">
                            <span class="bump-info-label">🏷️ النوع</span>
                            <span class="bump-info-value">{{ $bump->type }}</span>
                        </div>
                    @endif

                    <div class="bump-info-item">
                        <span class="bump-info-label">⏰ تاريخ الإضافة</span>
                        <span class="bump-info-value">{{ $bump->created_at->diffForHumans() }}</span>
                    </div>
                </div>

                @if($bump->description)
                    <div class="bump-description">
                        <p>{{ $bump->description }}</p>
                    </div>
                @endif
            </div>

            <div class="bump-card-footer">
                <button class="btn btn-success" onclick="confirmBump({{ $bump->id }})">
                    ✓ تأكيد
                </button>
                <button class="btn btn-danger" onclick="reportFalse({{ $bump->id }})">
                    ✕ خطأ
                </button>
                <a href="{{ route('bumps.map') }}?lat={{ $bump->latitude }}&lng={{ $bump->longitude }}" class="btn btn-secondary">
                    🗺️ عرض على الخريطة
                </a>
            </div>
        </div>
    @empty
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <h2>لا توجد مطبات</h2>
            <p>ابدأ بإضافة مطب جديد من الخريطة</p>
            <a href="{{ route('bumps.map') }}" class="btn btn-primary mt-lg">
                🗺️ الذهاب إلى الخريطة
            </a>
        </div>
    @endforelse
</div>

@endsection

@section('styles')
<style>
    .bumps-container {
        animation: fadeIn 0.3s ease;
    }

    /* Page Header */
    .page-header {
        padding: var(--spacing-lg) 0;
        border-bottom: 1px solid var(--border-color);
    }

    .page-header h1 {
        margin-bottom: var(--spacing-sm);
    }

    /* Bump Card */
    .bump-card {
        background-color: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        margin-bottom: var(--spacing-lg);
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: var(--shadow-sm);
    }

    .bump-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .bump-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: var(--spacing-lg);
        border-bottom: 1px solid var(--border-color);
        background-color: var(--bg-secondary);
    }

    .bump-card-title {
        display: flex;
        align-items: flex-start;
        gap: var(--spacing-md);
        flex: 1;
    }

    .bump-status-icon {
        font-size: var(--font-size-2xl);
        flex-shrink: 0;
    }

    .bump-card-title h3 {
        margin: 0 0 var(--spacing-xs);
        font-size: var(--font-size-lg);
    }

    .bump-card-title p {
        margin: 0;
        font-size: var(--font-size-sm);
    }

    .bump-card-badge {
        flex-shrink: 0;
    }

    .bump-card-body {
        padding: var(--spacing-lg);
    }

    /* Bump Info Grid */
    .bump-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-lg);
        margin-bottom: var(--spacing-lg);
    }

    .bump-info-item {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-xs);
    }

    .bump-info-label {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        font-weight: 600;
    }

    .bump-info-value {
        font-size: var(--font-size-base);
        color: var(--text-primary);
        font-weight: 500;
    }

    /* Confidence Bar */
    .confidence-bar {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        height: 24px;
    }

    .confidence-fill {
        height: 6px;
        background: linear-gradient(90deg, #ef4444, #f59e0b, #10b981);
        border-radius: var(--radius-full);
        transition: width 0.3s ease;
        flex: 1;
    }

    .confidence-text {
        font-size: var(--font-size-sm);
        font-weight: 600;
        color: var(--text-primary);
        min-width: 40px;
    }

    /* Bump Description */
    .bump-description {
        padding: var(--spacing-md);
        background-color: var(--bg-secondary);
        border-radius: var(--radius-md);
        margin-bottom: var(--spacing-lg);
    }

    .bump-description p {
        margin: 0;
        color: var(--text-secondary);
        line-height: 1.6;
    }

    /* Bump Card Footer */
    .bump-card-footer {
        padding: var(--spacing-lg);
        border-top: 1px solid var(--border-color);
        display: flex;
        gap: var(--spacing-md);
        flex-wrap: wrap;
    }

    .bump-card-footer .btn {
        flex: 1;
        min-width: 100px;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: var(--spacing-2xl);
        background-color: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        border-style: dashed;
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: var(--spacing-lg);
    }

    .empty-state h2 {
        margin-bottom: var(--spacing-sm);
    }

    .empty-state p {
        margin-bottom: var(--spacing-lg);
    }

    /* Responsive */
    @media (max-width: 640px) {
        .bump-card-header {
            flex-direction: column;
            gap: var(--spacing-md);
        }

        .bump-info-grid {
            grid-template-columns: 1fr;
            gap: var(--spacing-md);
        }

        .bump-card-footer {
            flex-direction: column;
        }

        .bump-card-footer .btn {
            width: 100%;
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
     * Confirm Bump
     */
    function confirmBump(bumpId) {
        if (!confirm('هل تريد تأكيد هذا المطب؟')) return;

        fetch(`/api/bumps/${bumpId}/report`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                report_type: 'confirm'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                AlertManager.create('شكراً على التأكيد!', 'success', 2000);
                setTimeout(() => location.reload(), 2000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            AlertManager.create('حدث خطأ', 'error', 2000);
        });
    }

    /**
     * Report False Positive
     */
    function reportFalse(bumpId) {
        if (!confirm('هل تريد تقرير هذا المطب كخطأ؟')) return;

        fetch(`/api/bumps/${bumpId}/report`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                report_type: 'false_positive'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                AlertManager.create('تم تسجيل التقرير', 'success', 2000);
                setTimeout(() => location.reload(), 2000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            AlertManager.create('حدث خطأ', 'error', 2000);
        });
    }

    /**
     * Filter Bumps
     */
    function filterBumps() {
        const searchTerm = document.getElementById('search').value.toLowerCase();
        const statusFilter = document.getElementById('filter-status').value;
        const bumpCards = document.querySelectorAll('.bump-card');

        bumpCards.forEach(card => {
            const text = card.textContent.toLowerCase();
            const status = card.getAttribute('data-status');
            
            const matchesSearch = text.includes(searchTerm);
            const matchesStatus = !statusFilter || status === statusFilter;

            card.style.display = (matchesSearch && matchesStatus) ? 'block' : 'none';
        });
    }
</script>
@endsection
