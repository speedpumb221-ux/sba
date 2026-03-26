@extends('layouts.app')

@section('title', __('messages.Map'))

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css" />
<style>
    .leaflet-control-container .leaflet-routing-container-hide {
        display: none;
    }
    .bump-marker {
        background-color: #ef4444;
        border: 2px solid white;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .bump-marker.verified {
        background-color: #10b981;
    }
    .user-marker {
        background-color: #3b82f6;
        border: 3px solid white;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    /* Keep map controls and popups always in light style (map should not follow global dark mode) */
    .leaflet-container {
        background: #ffffff;
        color: #111827;
    }
    .leaflet-control-container .leaflet-control {
        background: #ffffff;
        color: #111827;
        border-color: #e5e7eb;
    }
    .leaflet-popup-content-wrapper {
        background: #ffffff;
        color: #111827;
        border: 1px solid #e5e7eb;
    }
</style>
@endsection

@section('content')
<div style="position: relative; height: calc(100vh - 200px); margin: 0 -16px; margin-bottom: 24px;">
    <div id="map" style="width: 100%; height: 100%;"></div>
    
    <!-- Alert Box -->
    <div id="alert-box" style="position: absolute; top: 16px; right: 16px; background: white; padding: 16px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 300px; display: none; z-index: 10; direction: rtl;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <h3 style="margin: 0; color: #ef4444;">⚠️ <span data-i18n="warning.title">{{ __('messages.Warning') }}</span>!</h3>
            <button onclick="closeAlert()" style="background: none; border: none; font-size: 20px; cursor: pointer;">✕</button>
        </div>
        <div id="alert-content"></div>
    </div>

    <!-- Stats Box -->
    <div id="stats-box" style="position: absolute; bottom: 16px; right: 16px; background: white; padding: 12px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); min-width: 200px; z-index: 10; direction: rtl;">
        <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 8px;">
            <strong data-i18n="stats.statistics">{{ __('messages.Statistics') }}</strong>:
        </div>
        <div style="font-size: 12px; margin-bottom: 4px;">
            📍 <span data-i18n="stats.speed_bumps">{{ __('messages.Speed Bumps') }}</span>: <span id="total-bumps">0</span>
        </div>
        <div style="font-size: 12px; margin-bottom: 4px;">
            ✅ <span data-i18n="stats.verified">{{ __('messages.Verified') }}</span>: <span id="verified-bumps">0</span>
        </div>
        <div style="font-size: 12px;">
            📊 <span data-i18n="stats.alerts">{{ __('messages.Alerts') }}</span>: <span id="alert-count">0</span>
        </div>
    </div>
</div>

<!-- Controls -->
<div class="card">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;">
        <div>
            <label for="alert-distance" style="font-size: 12px; color: var(--text-secondary);" data-i18n="label.alert_distance">{{ __('messages.Alert Distance') }}</label>
            <select id="alert-distance" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
                <option value="50">50 <span data-i18n="label.meters">{{ __('messages.meters') }}</span></option>
                <option value="100" selected>100 <span data-i18n="label.meters">{{ __('messages.meters') }}</span></option>
                <option value="200">200 <span data-i18n="label.meters">{{ __('messages.meters') }}</span></option>
            </select>
        </div>
        <div style="display: flex; gap: 8px; align-items: flex-end;">
            <button id="start-tracking" class="btn btn-primary" style="flex: 1;" data-i18n="action.start_tracking">▶️ {{ __('messages.Start Tracking') }}</button>
            <button id="stop-tracking" class="btn btn-secondary" style="flex: 1;" data-i18n="action.stop">⏹️ {{ __('messages.Stop') }}</button>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
<script>
    let map;
    let userMarker;
    let bumpMarkers = [];
    let watchId;
    let isTracking = false;
    let alertDistance = 100;
    let userLocation = [24.7136, 46.6753]; // Riyadh default [lat, lng]
    let lastAlertTime = 0;
    const ALERT_COOLDOWN = 3000; // 3 seconds

    // Initialize Map
    function initMap() {
        map = L.map('map').setView(userLocation, 13);

        // Always use the standard OpenStreetMap light tiles for consistency
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);

        // Load bumps from server
        loadBumps();

        // Start GPS tracking
        startGPSTracking();

        // Update stats
        updateStats();

        // Add click event to add new bumps
        map.on('click', function(e) {
            if (confirm('{{ __("messages.Add a bump") }} هنا؟')) {
                addNewBump(e.latlng.lat, e.latlng.lng);
            }
        });
    }

    // Load bumps from API
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
                // Clear existing markers
                bumpMarkers.forEach(marker => map.removeLayer(marker));
                bumpMarkers = [];

                data.bumps.forEach(bump => {
                    addBumpMarker(bump);
                });

                updateStats();
            }
        })
        .catch(error => console.error('Error loading bumps:', error));
    }

    // Add bump marker to map
    function addBumpMarker(bump) {
        const iconHtml = bump.is_verified ? '📍' : '❓';
        const markerColor = bump.is_verified ? '#10b981' : '#ef4444';

        const icon = L.divIcon({
            html: `<div style="background-color: ${markerColor}; border: 2px solid white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">${iconHtml}</div>`,
            className: 'bump-marker',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });

        const marker = L.marker([parseFloat(bump.latitude), parseFloat(bump.longitude)], { icon })
            .addTo(map)
            .bindPopup(`
                <div style="direction: rtl; text-align: right; padding: 8px;">
                    <h4 style="margin: 0 0 8px 0;">مطب سرعة</h4>
                    <p style="margin: 4px 0;"><strong>الحالة:</strong> ${bump.is_verified ? 'مؤكد' : 'غير مؤكد'}</p>
                    <p style="margin: 4px 0;"><strong>التقارير:</strong> ${bump.reports_count || 0}</p>
                    ${bump.description ? `<p style="margin: 4px 0;"><strong>الوصف:</strong> ${bump.description}</p>` : ''}
                    <div style="margin-top: 8px;">
                        <button onclick="reportBump(${bump.id}, 'confirm')" class="btn btn-success" style="font-size: 12px; padding: 4px 8px;">تأكيد</button>
                        <button onclick="reportBump(${bump.id}, 'false_positive')" class="btn btn-danger" style="font-size: 12px; padding: 4px 8px; margin-left: 4px;">خطأ</button>
                    </div>
                </div>
            `);

        bumpMarkers.push(marker);
    }

    // Add new bump
    function addNewBump(lat, lng) {
        fetch('/api/bumps', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                latitude: lat,
                longitude: lng,
                description: 'مطب جديد'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadBumps(); // Reload all bumps
                alert('{{ __("messages.Success") }}: {{ __("messages.Speed bump added successfully") }}');
            }
        })
        .catch(error => console.error('Error adding bump:', error));
    }

    // Report bump
    function reportBump(bumpId, type) {
        fetch('/api/reports', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
                loadBumps(); // Reload bumps to update status
                alert('{{ __("messages.Success") }}: {{ __("messages.Report submitted") }}');
            }
        })
        .catch(error => console.error('Error reporting bump:', error));
    }

    // GPS Tracking
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
                        html: '<div style="background-color: #3b82f6; border: 3px solid white; border-radius: 50%; width: 20px; height: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></div>',
                        className: 'user-marker',
                        iconSize: [20, 20],
                        iconAnchor: [10, 10]
                    });

                    userMarker = L.marker(userLocation, { icon: userIcon })
                        .addTo(map)
                        .bindPopup('{{ __("messages.Your location") }}');

                    // Check for nearby bumps
                    checkNearbyBumps();

                    // Center map on user if tracking is active
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

    // Check for nearby bumps and show alerts
    function checkNearbyBumps() {
        const now = Date.now();
        if (now - lastAlertTime < ALERT_COOLDOWN) return;

        bumpMarkers.forEach(marker => {
            const bumpLatLng = marker.getLatLng();
            const distance = map.distance(userLocation, [bumpLatLng.lat, bumpLatLng.lng]);

            if (distance <= alertDistance) {
                showAlert('{{ __("messages.Warning") }}: {{ __("messages.Speed bump ahead") }}! (' + Math.round(distance) + ' {{ __("messages.meters") }})');
                lastAlertTime = now;
                updateAlertCount();
            }
        });
    }

    // Show alert
    function showAlert(message) {
        document.getElementById('alert-content').innerHTML = message;
        document.getElementById('alert-box').style.display = 'block';

        // Auto hide after 5 seconds
        setTimeout(() => {
            closeAlert();
        }, 5000);
    }

    // Close alert
    function closeAlert() {
        document.getElementById('alert-box').style.display = 'none';
    }

    // Update statistics
    function updateStats() {
        fetch('/api/stats', {
            headers: {
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('total-bumps').textContent = data.stats.total_bumps || 0;
                document.getElementById('verified-bumps').textContent = data.stats.verified_bumps || 0;
                document.getElementById('alert-count').textContent = data.stats.alert_count || 0;
            }
        })
        .catch(error => console.error('Error loading stats:', error));
    }

    // Update alert count
    function updateAlertCount() {
        const current = parseInt(document.getElementById('alert-count').textContent) || 0;
        document.getElementById('alert-count').textContent = current + 1;
    }

    // Event listeners
    document.getElementById('start-tracking').addEventListener('click', function() {
        isTracking = true;
        this.disabled = true;
        document.getElementById('stop-tracking').disabled = false;
        if (userLocation) {
            map.setView(userLocation, 16);
        }
    });

    document.getElementById('stop-tracking').addEventListener('click', function() {
        isTracking = false;
        this.disabled = true;
        document.getElementById('start-tracking').disabled = false;
    });

    document.getElementById('alert-distance').addEventListener('change', function() {
        alertDistance = parseInt(this.value);
    });

    // Initialize map when page loads
    document.addEventListener('DOMContentLoaded', initMap);

    // Refresh bumps every 30 seconds
    setInterval(loadBumps, 30000);
</script>
@endsection
