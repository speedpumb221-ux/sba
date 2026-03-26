@extends('layouts.app')

@section('title', 'قائمة المطبات')

@section('content')
<div style="margin-bottom: 24px;">
    <h2 style="margin-bottom: 16px;">قائمة حواجز السرعة</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;">
        @forelse($bumps as $bump)
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <div style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">
                            {{ $bump->description ?? 'مطب سرعة' }}
                        </div>
                        <p style="font-size: 12px; color: var(--text-secondary); margin-bottom: 8px;">
                            📍 {{ $bump->latitude }}, {{ $bump->longitude }}
                        </p>
                        <p style="font-size: 12px; margin-bottom: 4px;">
                            <strong>المصدر:</strong> {{ $bump->source }}
                        </p>
                        <p style="font-size: 12px; margin-bottom: 4px;">
                            <strong>الثقة:</strong> 
                            <span style="background: linear-gradient(90deg, #ef4444, #f59e0b, #10b981); background-size: 100%; -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                                {{ $bump->confidence_level }}%
                            </span>
                        </p>
                        <p style="font-size: 12px;">
                            <strong>التقارير:</strong> {{ $bump->report_count }}
                        </p>
                    </div>
                    <div style="font-size: 32px;">
                        {{ $bump->is_verified ? '✅' : '❓' }}
                    </div>
                </div>
                <div style="margin-top: 12px; display: flex; gap: 8px;">
                    <button class="btn btn-primary" style="flex: 1; font-size: 12px;" onclick="confirmBump({{ $bump->id }})">
                        ✓ تأكيد
                    </button>
                    <button class="btn btn-secondary" style="flex: 1; font-size: 12px;" onclick="reportFalse({{ $bump->id }})">
                        ✗ خطأ
                    </button>
                </div>
            </div>
        @empty
            <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                <p style="font-size: 18px; color: var(--text-secondary);">لا توجد مطبات حالياً</p>
                <p style="font-size: 14px; color: var(--text-secondary); margin-top: 8px;">ابدأ بإضافة مطب من الخريطة</p>
            </div>
        @endforelse
    </div>
</div>

<script>
    function confirmBump(bumpId) {
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
                alert('شكراً على التأكيد!');
                location.reload();
            }
        });
    }

    function reportFalse(bumpId) {
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
                alert('تم تسجيل التقرير');
                location.reload();
            }
        });
    }
</script>
@endsection
