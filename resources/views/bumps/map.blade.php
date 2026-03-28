@extends('layouts.app')

@section('title', __('messages.Map'))

@section('styles')
<link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/leaflet-markercluster/MarkerCluster.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/leaflet-markercluster/MarkerCluster.Default.css') }}" />
<link rel="stylesheet" href="{{ asset('css/map-3d.css') }}">
<style>
    /* Advanced Leaflet Customizations */
    .leaflet-control-zoom {
        border-radius: var(--radius-lg) !important;
        overflow: hidden;
    }

    .leaflet-control-zoom-in {
        border-bottom: 1px solid rgba(0, 0, 0, 0.1) !important;
    }

    /* Marker Cluster Customization */
    .marker-cluster {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.8), rgba(30, 64, 175, 0.8)) !important;
        border: 2px solid rgba(255, 255, 255, 0.9) !important;
        border-radius: 50% !important;
        box-shadow: 0 8px 20px rgba(30, 64, 175, 0.3) !important;
    }

    .marker-cluster span {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.9), rgba(30, 64, 175, 0.9)) !important;
        border-radius: 50% !important;
        color: white !important;
        font-weight: 700 !important;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2) !important;
    }

    .marker-cluster.marker-cluster-small {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.7), rgba(30, 64, 175, 0.7)) !important;
    }

    .marker-cluster.marker-cluster-medium {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.8), rgba(30, 64, 175, 0.8)) !important;
    }

    .marker-cluster.marker-cluster-large {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.9), rgba(30, 64, 175, 0.9)) !important;
    }

    /* Loading Animation */
    .map-loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 999;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: var(--spacing-lg);
    }

    .map-loading-spinner {
        width: 48px;
        height: 48px;
        border: 4px solid rgba(30, 64, 175, 0.2);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* Responsive layout: map framed, controls below on mobile */
    .map-container {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-md);
        width: 100%;
        padding: 0 12px;
        box-sizing: border-box;
    }

    .map-frame {
        width: 100%;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.06);
        box-shadow: 0 10px 30px rgba(2,6,23,0.06);
        background: var(--bg-primary);
        position: relative;
    }

    #map {
        width: 100%;
        height: 60vh;
        min-height: 320px;
        display: block;
    }

    .map-cards {
        display: flex;
        flex-direction: column;
        gap: 10px;
        width: 100%;
        box-sizing: border-box;
        padding-bottom: 8px;
    }

    .control-card {
        background: var(--bg-primary);
        border: 1px solid var(--border-color);
        padding: 12px;
        border-radius: 10px;
        box-shadow: 0 6px 18px rgba(2,6,23,0.04);
    }

    .stats-box {
        width: 100%;
        box-sizing: border-box;
    }

    /* Desktop / large screens: overlay controls to the right */
    @media (min-width: 992px) {
        .map-container { padding: 0; }
        .map-container { position: relative; }
        .map-frame { height: calc(100vh - 160px); min-height: 480px; }
        #map { height: 100%; }
        .map-cards {
            position: absolute;
            right: 18px;
            top: 80px;
            width: 320px;
            max-height: calc(100vh - 160px);
            overflow: auto;
            display: flex;
            flex-direction: column;
            gap: 12px;
            z-index: 1200;
        }
        .stats-box {
            position: absolute;
            left: 18px;
            bottom: 18px;
            width: 260px;
            z-index: 1200;
        }
        #alert-box { position: absolute; left: 50%; transform: translateX(-50%); top: 16px; z-index: 1300; width: auto; }
    }

    /* Strong mobile overrides to avoid floating/overlay controls covering the map
       map-3d.css sets .map-container fixed and .map-controls absolute; override those
       on smaller screens so controls flow below the map and are touch-friendly. */
    @media (max-width: 991px) {
        .map-container {
            position: static !important;
            height: auto !important;
            max-height: none !important;
            width: 100% !important;
            padding: 12px !important;
            perspective: none !important;
            overflow: visible !important;
        }

        .map-frame {
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            box-shadow: none !important;
        }

        #map {
            height: 50vh !important;
            min-height: 320px !important;
        }

        /* Make cards flow below the map and be full-width */
        .map-cards {
            position: static !important;
            right: auto !important;
            top: auto !important;
            width: 100% !important;
            max-width: 100% !important;
            margin-top: 12px !important;
            padding: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 12px !important;
            z-index: 2 !important;
        }

        .map-controls {
            position: static !important;
            top: auto !important;
            right: auto !important;
            left: auto !important;
            z-index: 2 !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 10px !important;
            pointer-events: auto !important;
        }

        .control-card {
            min-width: auto !important;
            max-width: none !important;
            width: 100% !important;
            box-shadow: 0 6px 18px rgba(2,6,23,0.06) !important;
            background: var(--bg-primary) !important;
            border: 1px solid var(--border-color) !important;
        }

        .stats-box {
            position: static !important;
            width: 100% !important;
            margin-top: 6px !important;
            left: auto !important;
            right: auto !important;
            bottom: auto !important;
            z-index: 2 !important;
        }

        #alert-box { position: static !important; transform: none !important; top: auto !important; left: auto !important; width: 100% !important; z-index: 1400 !important; }
    }
</style>
@endsection

@section('content')
<!-- Map Container -->
<div class="map-container">
    <div class="map-frame">
        <div id="map"></div>

        <!-- Loading Indicator -->
        <div id="map-loading" class="map-loading" style="display: none;">
            <div class="map-loading-spinner"></div>
            <p style="color: var(--text-secondary); font-weight: 500;">جاري تحميل الخريطة...</p>
        </div>

        <!-- Alert Box (kept inside frame) -->
        <div id="alert-box" class="alert-box" style="display: none;"></div>
    </div>

    <!-- Cards Panel (controls + stats) -->
    <div class="map-cards">
        <!-- Map Controls -->
        <div class="map-controls">
        <!-- Alert Distance Control -->
        <div class="control-card">
            <div class="control-card-header">
                <span class="control-card-icon">🔔</span>
                <span>{{ __('messages.Alert Distance') }}</span>
            </div>
            <div class="form-group">
                <select id="alert-distance" class="form-control">
                    <option value="50">50 متر</option>
                    <option value="100" selected>100 متر</option>
                    <option value="200">200 متر</option>
                </select>
            </div>
            <div class="flex gap-md">
                <button id="start-tracking" class="btn btn-primary btn-sm" style="flex: 1;">
                    ▶️ تتبع
                </button>
                <button id="stop-tracking" class="btn btn-secondary btn-sm" style="flex: 1;">
                    ⏹️ إيقاف
                </button>
            </div>
        </div>

        <!-- Map Navigation Controls -->
        <div class="control-card">
            <div class="control-card-header">
                <span class="control-card-icon">🎯</span>
                <span>تحكم الخريطة</span>
            </div>
            <div class="flex gap-sm" style="flex-direction: column;">
                <button id="zoom-to-location" class="btn btn-info btn-sm" title="انتقل إلى موقعك الحالي (L)">
                    📍 انتقل إلى موقعي
                </button>
                <button id="reset-zoom" class="btn btn-warning btn-sm" title="إعادة تعيين التقريب (R)">
                    🔄 إعادة تعيين التقريب
                </button>
                <button id="street-zoom" class="btn btn-success btn-sm" title="تقريب إلى مستوى الشارع (S)">
                    🏘️ تقريب شارع
                </button>
            </div>
            <small style="color: #666; font-size: 11px; margin-top: 8px; display: block;">
                اختصارات: L (موقعي), R (إعادة), S (شارع), +/- (تكبير/تصغير)
            </small>
        </div>

        <!-- Map Layer Control -->
        <div class="control-card">
            <div class="control-card-header">
                <span class="control-card-icon">🗺️</span>
                <span>طبقات الخريطة</span>
            </div>
            <div class="form-group">
                <select id="map-layer" class="form-control" onchange="changeMapLayer(this.value)">
                    <option value="osm">OpenStreetMap</option>
                    <option value="satellite">صور فضائية</option>
                    <option value="terrain">تضاريس</option>
                    <option value="dark">داكن</option>
                </select>
            </div>
        </div>

        <!-- Current Location Control -->
        <div class="control-card">
            <button id="center-map" class="btn btn-primary btn-block">
                📍 موقعي الحالي
            </button>
        </div>
        </div>

        <!-- Stats Box -->
        <div class="stats-box control-card">
        <div class="control-card-header">
            <span class="control-card-icon">📊</span>
            <span>الإحصائيات</span>
        </div>
        <div class="stats-item">
            <span>📍</span>
            <span>المطبات:</span>
            <span class="stats-value" id="total-bumps">0</span>
        </div>
        <div class="stats-item">
            <span>✅</span>
            <span>مؤكدة:</span>
            <span class="stats-value" id="verified-bumps">0</span>
        </div>
        <div class="stats-item">
            <span>❓</span>
            <span>غير مؤكدة:</span>
            <span class="stats-value" id="unverified-bumps">0</span>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
<script src="{{ asset('vendor/leaflet-markercluster/leaflet.markercluster.js') }}"></script>

<script>
    // ============================================
    // Map Variables
    // ============================================
    let map;
    let userMarker;
    let bumpMarkers = [];
    let markerClusterGroup;
    let watchId;
    let isTracking = false;
    let alertDistance = 100;
    let userLocation = [24.7136, 46.6753]; // Riyadh default
    let lastAlertTime = 0;
    const ALERT_COOLDOWN = 3000;
    
    // Map layers
    const mapLayers = {
        osm: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19,
        }),
        satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles © Esri',
            maxZoom: 19,
        }),
        terrain: L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            attribution: 'Map data: © OpenStreetMap contributors, SRTM | Map style: © OpenTopoMap',
            maxZoom: 17,
        }),
        dark: L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '© OpenStreetMap contributors © CARTO',
            maxZoom: 19,
        })
    };

    let currentLayer = 'osm';

    /**
     * Initialize Map
     */
    function initMap() {
        // Show loading indicator
        document.getElementById('map-loading').style.display = 'flex';

        // Create map
        map = L.map('map', {
            zoomControl: true,
            attributionControl: true,
            preferCanvas: true,
            maxBounds: [[-85.051129, -180], [85.051129, 180]],
        }).setView(userLocation, 13);

        // Add initial tile layer
        mapLayers.osm.addTo(map);

        // Initialize marker cluster group
        markerClusterGroup = L.markerClusterGroup({
            maxClusterRadius: 80,
            disableClusteringAtZoom: 17,
            iconCreateFunction: createClusterIcon
        });
        map.addLayer(markerClusterGroup);

        // Load bumps
        loadBumps();

        // Start GPS tracking
        startGPSTracking();

        // Setup event listeners
        setupEventListeners();

        // Add click event to add new bumps
        map.on('click', function(e) {
            if (confirm('هل تريد إضافة مطب هنا؟')) {
                addNewBump(e.latlng.lat, e.latlng.lng);
            }
        });

        // Hide loading indicator
        setTimeout(() => {
            document.getElementById('map-loading').style.display = 'none';
        }, 500);
    }

    /**
     * Create Cluster Icon
     */
    function createClusterIcon(cluster) {
        const childCount = cluster.getChildCount();
        let c = ' marker-cluster-';
        if (childCount < 10) {
            c += 'small';
        } else if (childCount < 100) {
            c += 'medium';
        } else {
            c += 'large';
        }

        return new L.DivIcon({
            html: `<div style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.9), rgba(30, 64, 175, 0.9)); border: 2px solid rgba(255, 255, 255, 0.9); border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; box-shadow: 0 8px 20px rgba(30, 64, 175, 0.3);">${childCount}</div>`,
            className: 'marker-cluster' + c,
            iconSize: new L.Point(40, 40),
            iconAnchor: [20, 20]
        });
    }

    /**
     * Change Map Layer
     */
    function changeMapLayer(layer) {
        if (currentLayer !== layer) {
            map.removeLayer(mapLayers[currentLayer]);
            mapLayers[layer].addTo(map);
            currentLayer = layer;
        }
    }

    /**
     * Setup Event Listeners
     */
    function setupEventListeners() {
        document.getElementById('alert-distance').addEventListener('change', (e) => {
            alertDistance = parseInt(e.target.value);
        });

        document.getElementById('start-tracking').addEventListener('click', (e) => {
            isTracking = true;
            // Visual feedback
            try{ e.target.classList.add('btn-pressed'); setTimeout(()=>e.target.classList.remove('btn-pressed'), 300); }catch(err){}
            try{ AlertManager.create('تم بدء التتبع', 'success', 2000); }catch(err){ console.warn('AlertManager missing', err); }
        });

        document.getElementById('stop-tracking').addEventListener('click', (e) => {
            isTracking = false;
            try{ e.target.classList.add('btn-pressed'); setTimeout(()=>e.target.classList.remove('btn-pressed'), 300); }catch(err){}
            try{ AlertManager.create('تم إيقاف التتبع', 'info', 2000); }catch(err){ console.warn('AlertManager missing', err); }
        });

        document.getElementById('center-map').addEventListener('click', () => {
            if (userMarker) {
                map.setView(userLocation, 16);
            }
        });

        // New map control buttons
        document.getElementById('zoom-to-location')?.addEventListener('click', () => {
            if (userLocation) {
                map.setView(userLocation, 18);
            } else {
                AlertManager.create('الموقع غير متاح حاليًا', 'warning', 3000);
            }
        });

        document.getElementById('reset-zoom')?.addEventListener('click', () => {
            map.setView(userLocation, 13);
        });

        document.getElementById('street-zoom')?.addEventListener('click', () => {
            if (userLocation) {
                map.setView(userLocation, 20);
            } else {
                AlertManager.create('الموقع غير متاح حاليًا', 'warning', 3000);
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

            switch(e.key.toLowerCase()) {
                case 'l':
                    if (userLocation) {
                        map.setView(userLocation, 18);
                    }
                    break;
                case 'r':
                    map.setView(userLocation, 13);
                    break;
                case 's':
                    if (userLocation) {
                        map.setView(userLocation, 20);
                    }
                    break;
                case '+':
                case '=':
                    map.zoomIn();
                    break;
                case '-':
                    map.zoomOut();
                    break;
            }
        });
        // Diagnostics: warn if start/stop controls not found
        try{
            var startBtn = document.getElementById('start-tracking');
            var stopBtn = document.getElementById('stop-tracking');
            if(!startBtn) console.warn('start-tracking button not found');
            if(!stopBtn) console.warn('stop-tracking button not found');
        }catch(e){ console.warn('setupEventListeners diagnostics failed', e); }
    }

    // Delegated click handler as a fallback if direct listeners don't attach
    document.addEventListener('click', function(e){
        try{
            var btn = e.target.closest && e.target.closest('#start-tracking, #stop-tracking');
            if(!btn) return;
            if(btn.id === 'start-tracking'){
                isTracking = true;
                try{ AlertManager.create('تم بدء التتبع', 'success', 2000); }catch(e){ console.warn('AlertManager missing', e); }
            } else if(btn.id === 'stop-tracking'){
                isTracking = false;
                try{ AlertManager.create('تم إيقاف التتبع', 'info', 2000); }catch(e){ console.warn('AlertManager missing', e); }
            }
        }catch(e){ /* ignore */ }
    });

    /**
     * Load Bumps from API
     */
    function loadBumps() {
        fetch('/api/bumps', {
            headers: {
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                markerClusterGroup.clearLayers();
                bumpMarkers = [];

                data.bumps.forEach(bump => {
                    addBumpMarker(bump);
                });

                updateStats();
            }
        })
        .catch(error => console.error('Error loading bumps:', error));
    }

    /**
     * Add Bump Marker to Map
     */
    function addBumpMarker(bump) {
        const iconHtml = bump.is_verified ? '📍' : '❓';
        const markerColor = bump.is_verified ? '#10b981' : '#ef4444';

        const icon = L.divIcon({
            html: `<div style="background: linear-gradient(135deg, ${markerColor}, ${markerColor}); border: 2px solid white; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 20px; box-shadow: 0 8px 16px rgba(0,0,0,0.3), inset 0 -2px 4px rgba(0,0,0,0.2), inset 0 2px 4px rgba(255,255,255,0.3); filter: drop-shadow(0 4px 8px rgba(0,0,0,0.25));">${iconHtml}</div>`,
            className: 'bump-marker' + (bump.is_verified ? ' verified' : ''),
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });

        const marker = L.marker([parseFloat(bump.latitude), parseFloat(bump.longitude)], { icon })
            .bindPopup(createBumpPopup(bump), {
                maxWidth: 300,
                className: 'bump-popup-container'
            });

        markerClusterGroup.addLayer(marker);
        bumpMarkers.push(marker);
    }

    /**
     * Create Bump Popup HTML
     */
    function createBumpPopup(bump) {
        const statusBadge = bump.is_verified 
            ? '<span class="badge badge-success">مؤكد</span>' 
            : '<span class="badge badge-warning">غير مؤكد</span>';

        return `
            <div class="bump-popup">
                <div class="bump-popup-header">
                    <span class="bump-popup-icon">${bump.is_verified ? '📍' : '❓'}</span>
                    <span>مطب سرعة</span>
                </div>
                
                <div class="bump-popup-info">
                    <div class="bump-popup-row">
                        <span class="bump-popup-label">الحالة:</span>
                        <span>${statusBadge}</span>
                    </div>
                    <div class="bump-popup-row">
                        <span class="bump-popup-label">التقارير:</span>
                        <span class="bump-popup-value">${bump.reports_count || 0}</span>
                    </div>
                    ${bump.description ? `
                        <div class="bump-popup-row">
                            <span class="bump-popup-label">الوصف:</span>
                            <span class="bump-popup-value">${bump.description}</span>
                        </div>
                    ` : ''}
                    <div class="bump-popup-row">
                        <span class="bump-popup-label">الموقع:</span>
                        <span class="bump-popup-value">${parseFloat(bump.latitude).toFixed(4)}, ${parseFloat(bump.longitude).toFixed(4)}</span>
                    </div>
                </div>
                
                <div class="bump-popup-actions">
                    <button onclick="reportBump(${bump.id}, 'confirm')" class="btn btn-success btn-sm">✓ تأكيد</button>
                    <button onclick="reportBump(${bump.id}, 'false_positive')" class="btn btn-danger btn-sm">✕ خطأ</button>
                </div>
            </div>
        `;
    }

    /**
     * Add New Bump
     */
    function addNewBump(lat, lng) {
        fetch('/api/bumps', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                latitude: lat,
                longitude: lng,
                description: 'مطب جديد'
            })
        })
        .then(function(response){
            if(!response.ok){
                // common cases: 401/302 = not authenticated, 419 = CSRF
                if(response.status === 419 || response.status === 401 || response.status === 302){
                    throw new Error('Authentication or CSRF error (status ' + response.status + '). تأكد من تسجيل الدخول أو إعادة تحميل الصفحة.');
                }
                // attempt to read body for debugging (could be HTML error page or JSON with message)
                return response.text().then(function(text){
                    var snippet = (text || '').slice(0,1000);
                    throw new Error('HTTP ' + response.status + ' - ' + snippet);
                });
            }
            var ct = (response.headers.get('content-type') || '');
            if(!ct.includes('application/json')){
                return response.text().then(function(text){ throw new Error('Non-JSON response: ' + (text||'').slice(0,1000)); });
            }
            return response.json();
        })
        .then(function(data){
            if (data && data.success) {
                loadBumps();
                AlertManager.create('تم إضافة المطب بنجاح', 'success', 2000);
            } else {
                console.warn('addNewBump: unexpected response', data);
            }
        })
        .catch(function(error){
            console.error('Error adding bump:', error);
            // show a helpful message to the user
            var msg = 'فشل إضافة المطب';
            if(error && error.message) msg += ': ' + (error.message.length>200? error.message.slice(0,200)+'...': error.message);
            AlertManager.create(msg, 'error', 4000);
        });
    }

    /**
     * Report Bump
     */
    function reportBump(bumpId, type) {
        fetch('/api/reports', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                speed_bump_id: bumpId,
                report_type: type
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadBumps();
                AlertManager.create('تم إرسال التقرير بنجاح', 'success', 2000);
            }
        })
        .catch(error => {
            console.error('Error reporting bump:', error);
            AlertManager.create('حدث خطأ في إرسال التقرير', 'error', 2000);
        });
    }

    /**
     * Start GPS Tracking
     */
    function startGPSTracking() {
        if (navigator.geolocation) {
            watchId = navigator.geolocation.watchPosition(
                function(position) {
                    userLocation = [position.coords.latitude, position.coords.longitude];

                    // Update user marker
                    if (userMarker) {
                        map.removeLayer(userMarker);
                    }

                    const userIcon = L.divIcon({
                        html: '<div style="background: linear-gradient(135deg, #3b82f6, #1e40af); border: 3px solid white; border-radius: 50%; width: 24px; height: 24px; box-shadow: 0 8px 20px rgba(59, 130, 246, 0.5), 0 4px 12px rgba(0, 0, 0, 0.3), inset 0 -2px 4px rgba(0, 0, 0, 0.2), inset 0 2px 4px rgba(255, 255, 255, 0.4);"></div>',
                        className: 'user-marker',
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    });

                    userMarker = L.marker(userLocation, { icon: userIcon })
                        .addTo(map)
                        .bindPopup('📍 موقعك الحالي');

                    // Check for nearby bumps
                    checkNearbyBumps();

                    // Center map if tracking
                    if (isTracking) {
                        map.setView(userLocation, 16);
                    }
                },
                function(error) {
                    console.error('GPS Error:', error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 5000,
                    maximumAge: 0
                }
            );
        }
    }

    /**
     * Check Nearby Bumps
     */
    function checkNearbyBumps() {
        const now = Date.now();
        if (now - lastAlertTime < ALERT_COOLDOWN) return;

        bumpMarkers.forEach(marker => {
            const markerLat = marker.getLatLng().lat;
            const markerLng = marker.getLatLng().lng;
            const distance = calculateDistance(userLocation[0], userLocation[1], markerLat, markerLng);

            if (distance < alertDistance) {
                lastAlertTime = now;
                showNearbyAlert(distance);
            }
        });
    }

    /**
     * Calculate Distance (Haversine Formula)
     */
    function calculateDistance(lat1, lng1, lat2, lng2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLng / 2) * Math.sin(dLng / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c * 1000;
    }

    /**
     * Show Nearby Alert
     */
    function showNearbyAlert(distance) {
        const alertBox = document.getElementById('alert-box');
        alertBox.innerHTML = `
            <div class="alert alert-warning">
                <span class="alert-icon">⚠️</span>
                <div class="alert-content">
                    <div class="alert-title">تنبيه!</div>
                    <div>مطب سرعة على بعد ${Math.round(distance)} متر</div>
                </div>
            </div>
        `;
        alertBox.style.display = 'block';

        setTimeout(() => {
            alertBox.style.display = 'none';
        }, 5000);
    }

    /**
     * Update Statistics
     */
    function updateStats() {
        const totalBumps = bumpMarkers.length;
        const verifiedBumps = bumpMarkers.filter(m => {
            return m.getPopup().getContent().includes('مؤكد');
        }).length;

        document.getElementById('total-bumps').textContent = totalBumps;
        document.getElementById('verified-bumps').textContent = verifiedBumps;
        document.getElementById('unverified-bumps').textContent = totalBumps - verifiedBumps;
    }

    // Initialize map when page loads
    document.addEventListener('DOMContentLoaded', initMap);
</script>
@endsection
