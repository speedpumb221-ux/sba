@extends('layouts.app')

@section('title', 'اختبار النظام')

@section('styles')
<style>
    .testing-container {
        max-width: 1024px;
        margin: 0 auto;
        padding: 20px;
    }

    .testing-header {
        text-align: center;
        margin-bottom: 30px;
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .testing-header h2 {
        font-size: 2.5rem;
        margin-bottom: 10px;
    }

    .testing-controls {
        background: var(--bg-primary);
        border: 2px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .control-group {
        margin-bottom: 20px;
        position: relative;
    }

    .control-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--text-primary);
        font-size: 14px;
    }

    .control-group input, .control-group select {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--bg-secondary);
        color: var(--text-primary);
        font-size: 16px;
        transition: all 0.3s ease;
    }

    .control-group input:focus, .control-group select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .control-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .simulation-area {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        margin-bottom: 30px;
    }

    .map-section, .alerts-section {
        background: var(--bg-primary);
        border: 2px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 25px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .map-section:hover, .alerts-section:hover {
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
    }

    .map-section h3, .alerts-section h3 {
        margin-bottom: 20px;
        color: var(--primary-color);
        font-size: 1.3rem;
        font-weight: 600;
    }

    #test-map {
        width: 100%;
        height: 450px;
        border-radius: var(--radius-md);
        border: 2px solid var(--border-color);
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .alert-item {
        padding: 18px;
        border-radius: var(--radius-md);
        margin-bottom: 12px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        border: 1px solid transparent;
        transition: all 0.3s ease;
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert-danger {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        border-color: #fca5a5;
        color: #991b1b;
        border-left: 4px solid #dc2626;
    }

    .alert-warning {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        border-color: #f59e0b;
        color: #92400e;
        border-left: 4px solid #d97706;
    }

    .alert-info {
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        border-color: #3b82f6;
        color: #1e40af;
        border-left: 4px solid #2563eb;
    }

    .alert-icon {
        font-size: 24px;
        flex-shrink: 0;
    }

    @media (max-width: 768px) {
        .testing-container {
            padding: 15px;
        }

        .testing-header h2 {
            font-size: 2rem;
        }

        .control-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .simulation-area {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .test-actions {
            flex-direction: column;
        }

        .test-actions .btn {
            min-width: auto;
            width: 100%;
        }

        #test-map {
            height: 350px;
        }
    }

    @media (max-width: 480px) {
        .testing-header h2 {
            font-size: 1.8rem;
        }

        .testing-controls {
            padding: 20px;
        }

        .map-section, .alerts-section {
            padding: 20px;
        }

        .alert-item {
            padding: 15px;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .alert-icon {
            align-self: center;
        }
    }

    /* Enhanced scrollbar for alerts */
    #alerts-container {
        max-height: 400px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: var(--border-color) transparent;
    }

    #alerts-container::-webkit-scrollbar {
        width: 6px;
    }

    #alerts-container::-webkit-scrollbar-track {
        background: var(--bg-secondary);
        border-radius: 3px;
    }

    #alerts-container::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 3px;
    }

    #alerts-container::-webkit-scrollbar-thumb:hover {
        background: var(--text-secondary);
    }

    /* Page Header Styles */
    .page-header {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        color: white;
        padding: 30px 0;
        margin-bottom: 40px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }

    .page-header-content {
        max-width: 1024px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .page-title-section {
        flex: 1;
        min-width: 300px;
    }

    .page-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0 0 10px 0;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .page-icon {
        font-size: 3rem;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
    }

    .page-subtitle {
        font-size: 1.1rem;
        margin: 0;
        opacity: 0.9;
        font-weight: 400;
    }

    .page-actions {
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .page-actions .btn {
        background: rgba(255,255,255,0.2);
        border: 2px solid rgba(255,255,255,0.3);
        color: white;
        padding: 12px 24px;
        border-radius: var(--radius-lg);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .page-actions .btn:hover {
        background: rgba(255,255,255,0.3);
        border-color: rgba(255,255,255,0.5);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    /* Page Footer Styles */
    .page-footer {
        background: var(--bg-primary);
        border-top: 2px solid var(--border-color);
        margin-top: 60px;
        padding: 40px 0 20px 0;
    }

    .page-footer-content {
        max-width: 1024px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .footer-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 40px;
        margin-bottom: 30px;
    }

    .footer-section h4 {
        color: var(--primary-color);
        margin: 0 0 15px 0;
        font-size: 1.2rem;
        font-weight: 600;
    }

    .footer-section p {
        color: var(--text-secondary);
        line-height: 1.6;
        margin: 0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 20px;
    }

    .stat-item {
        text-align: center;
        padding: 15px;
        background: var(--bg-secondary);
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
    }

    .stat-number {
        display: block;
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 0.9rem;
        color: var(--text-secondary);
        font-weight: 500;
    }

    .footer-bottom {
        border-top: 1px solid var(--border-color);
        padding-top: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .footer-bottom p {
        color: var(--text-secondary);
        margin: 0;
        font-size: 0.9rem;
    }

    .footer-links {
        display: flex;
        gap: 20px;
    }

    .footer-links a {
        color: var(--text-secondary);
        text-decoration: none;
        font-size: 0.9rem;
        transition: color 0.3s ease;
    }

    .footer-links a:hover {
        color: var(--primary-color);
    }

    /* Responsive Header and Footer */
    @media (max-width: 768px) {
        .page-header {
            padding: 20px 0;
        }

        .page-header-content {
            flex-direction: column;
            text-align: center;
            gap: 15px;
        }

        .page-title {
            font-size: 2rem;
        }

        .page-icon {
            font-size: 2.5rem;
        }

        .page-subtitle {
            font-size: 1rem;
        }

        .page-actions .btn {
            padding: 10px 20px;
            font-size: 14px;
        }

        .footer-info {
            grid-template-columns: 1fr;
            gap: 30px;
        }

        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .stat-item {
            padding: 10px;
        }

        .stat-number {
            font-size: 1.5rem;
        }

        .footer-bottom {
            flex-direction: column;
            text-align: center;
            gap: 15px;
        }

        .footer-links {
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .page-title {
            font-size: 1.8rem;
            flex-direction: column;
            gap: 10px;
        }

        .page-icon {
            font-size: 2rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .footer-links {
            flex-direction: column;
            gap: 10px;
        }
    }
</style>
@endsection

@section('content')
<!-- Page Header -->
<header class="page-header">
    <div class="page-header-content">
        <div class="page-title-section">
            <h1 class="page-title">
                <span class="page-icon">🧪</span>
                اختبار النظام
            </h1>
            <p class="page-subtitle">محاكاة اختبار المستخدم على المطبات وصدور التنبيهات</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                <span class="btn-icon">←</span>
                العودة للوحة التحكم
            </a>
        </div>
    </div>
</header>

<div class="testing-container">
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

        <a href="{{ route('admin.predictions') }}" class="admin-nav-card">
            <div class="admin-nav-icon">🧠</div>
            <div class="admin-nav-title">التوقعات</div>
            <div class="admin-nav-desc">إدارة التنبؤات الذكية</div>
        </a>

        <a href="{{ route('admin.testing') }}" class="admin-nav-card active">
            <div class="admin-nav-icon">🧪</div>
            <div class="admin-nav-title">اختبار النظام</div>
            <div class="admin-nav-desc">محاكاة التنبيهات والإشعارات</div>
        </a>
    </div>

    <!-- Testing Controls -->
    <div class="testing-controls">
        <h3 style="margin-bottom: 25px;">⚙️ إعدادات الاختبار</h3>

        <div class="control-grid">
            <div class="control-group">
                <label for="user-lat">خط العرض الحالي</label>
                <input type="number" id="user-lat" step="0.000001" value="24.7136" placeholder="24.7136">
            </div>

            <div class="control-group">
                <label for="user-lng">خط الطول الحالي</label>
                <input type="number" id="user-lng" step="0.000001" value="46.6753" placeholder="46.6753">
            </div>

            <div class="control-group">
                <label for="user-speed">السرعة الحالية (كم/ساعة)</label>
                <input type="number" id="user-speed" min="0" max="200" value="60" placeholder="60">
            </div>

            <div class="control-group">
                <label for="detection-radius">نطاق الكشف (متر)</label>
                <input type="number" id="detection-radius" min="10" max="1000" value="100" placeholder="100">
            </div>
        </div>

        <div class="control-group" style="margin-top: 25px;">
            <label for="selected-bump">اختيار مطب محدد (اختياري)</label>
            <select id="selected-bump">
                <option value="">جميع المطبات</option>
                @foreach($bumps as $bump)
                    <option value="{{ $bump->id }}" data-lat="{{ $bump->latitude }}" data-lng="{{ $bump->longitude }}">
                        مطب #{{ $bump->id }} - {{ $bump->description ?? 'بدون وصف' }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Simulation Area -->
    <div class="simulation-area">
        <!-- Interactive Map Partial -->
        @include('partials.interactive-map')

        <!-- Alerts Section -->
        <div class="alerts-section">
            <h3>🚨 التنبيهات والإشعارات</h3>
            <div id="alerts-container">
                <div class="alert-item alert-info">
                    <span class="alert-icon">ℹ️</span>
                    <div>
                        <strong>مرحباً بك في نظام الاختبار!</strong>
                        <br>
                        <small>اضبط إعداداتك وقم ببدء المحاكاة لرؤية التنبيهات في الوقت الفعلي</small>
                    </div>
                </div>
            </div>
            <div style="margin-top: 15px; text-align: center;">
                <small style="color: var(--text-secondary);">
                    <span id="alerts-count">0</span> تنبيه نشط
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Page Footer -->
<footer class="page-footer">
    <div class="page-footer-content">
        <div class="footer-info">
            <div class="footer-section">
                <h4>🧪 نظام الاختبار</h4>
                <p>أداة متقدمة لمحاكاة واختبار نظام كشف المطبات والتنبيهات</p>
            </div>
            <div class="footer-section">
                <h4>📊 إحصائيات الاختبار</h4>
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number">{{ $bumps->count() }}</span>
                        <span class="stat-label">مطب متاح</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="total-alerts">0</span>
                        <span class="stat-label">تنبيه تم إنشاؤه</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="simulation-time">0</span>
                        <span class="stat-label">ثانية محاكاة</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} تطبيق حواجز السرعة - جميع الحقوق محفوظة</p>
            <div class="footer-links">
                <a href="{{ route('admin.dashboard') }}">لوحة التحكم</a>
                <a href="{{ route('admin.bumps') }}">إدارة المطبات</a>
                <a href="{{ route('profile.show') }}">الملف الشخصي</a>
            </div>
        </div>
    </div>
</footer>
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
    let testMap = null;
    let userMarker = null;
    let bumpMarkers = [];
    let simulationInterval = null;
    let isSimulating = false;
    let totalAlertsGenerated = 0;
    let simulationStartTime = null;
    let simulationTotalTime = 0;

    const bumps = @json($bumps);

    function initTestMap() {
        if (testMap) return;

        testMap = L.map('test-map', {
            center: [24.7136, 46.6753],
            zoom: 15,
            minZoom: 5,
            maxZoom: 20,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(testMap);

        bumps.forEach(bump => {
            const marker = L.divIcon({ className: 'bump-marker', html: '📍', iconSize: [30,30], iconAnchor: [15,30] });
            const mapMarker = L.marker([bump.latitude, bump.longitude], { icon: marker }).addTo(testMap);
            mapMarker.bindPopup(`<strong>مطب #${bump.id}</strong><br>${bump.description || 'بدون وصف'}<br>النوع: ${bump.type}<br>الثقة: ${getConfidenceText(bump.confidence_level)}`);
            bumpMarkers.push(mapMarker);
        });

        updateUserMarker();

        setTimeout(() => { try { testMap.invalidateSize(); } catch (e) {} }, 200);
    }

    function updateUserMarker() {
        const lat = parseFloat(document.getElementById('user-lat').value);
        const lng = parseFloat(document.getElementById('user-lng').value);
        if (userMarker) testMap.removeLayer(userMarker);
        userMarker = L.marker([lat, lng], { icon: L.divIcon({ className: 'user-marker', html: '👤', iconSize: [30,30], iconAnchor: [15,15] }), draggable: true }).addTo(testMap);
        userMarker.on('dragend', () => { const pos = userMarker.getLatLng(); document.getElementById('user-lat').value = pos.lat.toFixed(6); document.getElementById('user-lng').value = pos.lng.toFixed(6); checkNearbyBumps(); });
        testMap.setView([lat, lng], testMap.getZoom());
    }

    function getConfidenceText(level) {
        const levels = { 'low': 'منخفضة (35%)', 'medium': 'متوسطة (65%)', 'high': 'عالية (90%)' };
        return levels[level] || level;
    }

    function calculateDistance(lat1, lng1, lat2, lng2) {
        const R = 6371; const dLat = (lat2 - lat1) * Math.PI / 180; const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng/2) * Math.sin(dLng/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); return R * c * 1000;
    }

    // Small helpers: play beep, show browser notification, vibrate (if available)
    function playAlertSound(type = 'info') {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const o = ctx.createOscillator();
            const g = ctx.createGain();
            const freq = (type === 'danger') ? 880 : (type === 'warning') ? 660 : 440;
            o.type = 'sine'; o.frequency.value = freq;
            g.gain.value = 0.001;
            o.connect(g); g.connect(ctx.destination);
            o.start();
            g.gain.exponentialRampToValueAtTime(0.2, ctx.currentTime + 0.01);
            g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.5);
            setTimeout(() => { try { o.stop(); ctx.close(); } catch (e) {} }, 600);
        } catch (e) {}
    }

    function showBrowserNotification(title, body) {
        try {
            if (!('Notification' in window)) return;
            if (Notification.permission === 'granted') {
                const n = new Notification(title, { body: body });
                setTimeout(() => n.close(), 5000);
            }
        } catch (e) {}
    }

    function checkNearbyBumps() {
        const userLat = parseFloat(document.getElementById('user-lat').value);
        const userLng = parseFloat(document.getElementById('user-lng').value);
        const radius = parseInt(document.getElementById('detection-radius').value);
        const selectedBumpId = document.getElementById('selected-bump').value;
        let nearbyBumps = [];
        if (selectedBumpId) {
            const selectedBump = bumps.find(b => b.id == selectedBumpId);
            if (selectedBump) { const distance = calculateDistance(userLat, userLng, selectedBump.latitude, selectedBump.longitude); if (distance <= radius) nearbyBumps.push({ ...selectedBump, distance: Math.round(distance) }); }
        } else {
            bumps.forEach(bump => { const distance = calculateDistance(userLat, userLng, bump.latitude, bump.longitude); if (distance <= radius) nearbyBumps.push({ ...bump, distance: Math.round(distance) }); });
        }
        nearbyBumps.sort((a,b) => a.distance - b.distance);
        generateAlerts(nearbyBumps);
    }
    function generateAlerts(nearbyBumps) {
        const alertsContainer = document.getElementById('alerts-container');
        const alertsCount = document.getElementById('alerts-count');
        const userSpeed = parseInt(document.getElementById('user-speed').value);

        // remove old alerts (except welcome)
        const oldAlerts = alertsContainer.querySelectorAll('.alert-item:not(:first-child)');
        oldAlerts.forEach(a => a.remove());

        alertsCount.textContent = nearbyBumps.length;
        totalAlertsGenerated += nearbyBumps.length;
        document.getElementById('total-alerts').textContent = totalAlertsGenerated;

        if (nearbyBumps.length === 0) {
            addAlert('لا توجد مطبات قريبة في النطاق المحدد', 'info');
            return;
        }

        nearbyBumps.forEach((bump, index) => {
            let alertType = 'warning';
            let alertIcon = '⚠️';
            let alertMessage = '';
            let alertDetails = '';

            if (bump.distance <= 50) {
                alertType = 'danger';
                alertIcon = '🚨';
                alertMessage = `مطب خطير على بعد ${bump.distance} متر!`;
                alertDetails = `توقف فوراً! ${bump.description || 'مطب سرعة'}`;
            } else if (bump.distance <= 100) {
                alertType = 'warning';
                alertIcon = '⚠️';
                alertMessage = `تحذير: مطب على بعد ${bump.distance} متر`;
                alertDetails = `خفض سرعتك. ${bump.description || 'مطب سرعة'}`;
            } else {
                alertType = 'info';
                alertIcon = 'ℹ️';
                alertMessage = `مطب على بعد ${bump.distance} متر`;
                alertDetails = `كن حذراً. ${bump.description || 'مطب سرعة'}`;
            }

            if (userSpeed > 60 && bump.distance <= 200) {
                alertDetails += ` | سرعتك عالية (${userSpeed} كم/ساعة)`;
            }

            setTimeout(() => {
                addAlert(`${alertMessage}<br><small>${alertDetails}</small>`, alertType, alertIcon);

                // Play sound, vibrate and show browser notification
                try { playAlertSound(alertType); } catch (e) {}
                try { if (navigator.vibrate) navigator.vibrate(alertType === 'danger' ? [200,100,200] : [100,50]); } catch(e) {}
                try { showBrowserNotification(alertMessage, alertDetails); } catch (e) {}

            }, index * 100);
        });
    }

    function addAlert(message,type,icon='ℹ️'){ const alertsContainer = document.getElementById('alerts-container'); const alertDiv = document.createElement('div'); alertDiv.className = `alert-item alert-${type}`; alertDiv.innerHTML = `<span class="alert-icon">${icon}</span><div>${message}</div>`; const welcomeMessage = alertsContainer.querySelector('.alert-item'); if (welcomeMessage) welcomeMessage.insertAdjacentElement('afterend', alertDiv); else alertsContainer.appendChild(alertDiv); alertsContainer.scrollTop = alertsContainer.scrollHeight; }

    function startSimulation(){ if (isSimulating) return; isSimulating=true; document.getElementById('start-simulation').disabled=true; document.getElementById('stop-simulation').disabled=false; simulationStartTime = Date.now(); document.querySelector('.simulation-area').classList.add('simulating'); addAlert('بدء المحاكاة...', 'info'); simulationInterval = setInterval(()=>{ const lat = parseFloat(document.getElementById('user-lat').value); const lng = parseFloat(document.getElementById('user-lng').value); const newLat = lat + (Math.random()-0.5)*0.001; const newLng = lng + (Math.random()-0.5)*0.001; document.getElementById('user-lat').value = newLat.toFixed(6); document.getElementById('user-lng').value = newLng.toFixed(6); updateUserMarker(); checkNearbyBumps(); }, 2000); }

    function stopSimulation(){ if (!isSimulating) return; isSimulating=false; document.getElementById('start-simulation').disabled=false; document.getElementById('stop-simulation').disabled=true; if (simulationStartTime){ const currentTime=Date.now(); const sessionTime=Math.floor((currentTime - simulationStartTime)/1000); simulationTotalTime += sessionTime; document.getElementById('simulation-time').textContent = simulationTotalTime; simulationStartTime=null; } document.querySelector('.simulation-area').classList.remove('simulating'); if (simulationInterval){ clearInterval(simulationInterval); simulationInterval=null; } addAlert('تم إيقاف المحاكاة', 'info'); }

    function resetSimulation(){ stopSimulation(); document.getElementById('user-lat').value='24.713600'; document.getElementById('user-lng').value='46.675300'; document.getElementById('user-speed').value='60'; document.getElementById('detection-radius').value='100'; document.getElementById('selected-bump').value=''; totalAlertsGenerated=0; simulationTotalTime=0; document.getElementById('total-alerts').textContent='0'; document.getElementById('simulation-time').textContent='0'; updateUserMarker(); const alertsContainer=document.getElementById('alerts-container'); const oldAlerts=alertsContainer.querySelectorAll('.alert-item:not(:first-child)'); oldAlerts.forEach(a=>a.remove()); addAlert('تم إعادة تعيين المحاكاة والإحصائيات', 'info'); }

    document.addEventListener('DOMContentLoaded', ()=>{
        // Request notification permission (non-blocking)
        try { if ('Notification' in window && Notification.permission !== 'granted') Notification.requestPermission().catch(()=>{}); } catch(e) {}
        initTestMap();
        document.getElementById('user-lat').addEventListener('input', updateUserMarker);
        document.getElementById('user-lng').addEventListener('input', updateUserMarker);
        document.getElementById('user-speed').addEventListener('input', checkNearbyBumps);
        document.getElementById('detection-radius').addEventListener('input', checkNearbyBumps);
        document.getElementById('selected-bump').addEventListener('change', checkNearbyBumps);
        document.getElementById('start-simulation').addEventListener('click', startSimulation);
        document.getElementById('stop-simulation').addEventListener('click', stopSimulation);
        document.getElementById('reset-simulation').addEventListener('click', resetSimulation);
        checkNearbyBumps();
    });
    </script>

@endsection
