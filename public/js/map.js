// Map functionality using Leaflet.js
class SpeedBumpMap {
    constructor() {
        this.map = null;
        this.userMarker = null;
        this.bumpMarkers = [];
        this.clusterGroup = null;
        this.alertedMarkers = []; // store bump ids that we've alerted for
        this.watchId = null;
        this.isTracking = false;
        this.alertDistance = 100;
        this.userLocation = [24.7136, 46.6753]; // Riyadh default [lat, lng]
        this.lastAlertTime = 0;
        this.ALERT_COOLDOWN = 3000; // 3 seconds
        this.REARM_DISTANCE = 50; // meters to re-allow alerts after leaving
        this.lastBumpLoadAt = 0;
        this.BUMP_LOAD_INTERVAL_MS = 15000; // minimum time between loads
        this.BUMP_LOAD_MOVE_THRESHOLD = 100; // meters moved to force reload
        this.lastBumpCenter = null;
    }

    // Load bumps near a center (latitude, longitude) within radius meters
    async loadBumps(lat, lng, radius = 500) {
        const now = Date.now();
        if (now - this.lastBumpLoadAt < this.BUMP_LOAD_INTERVAL_MS) {
            return; // throttle frequent loads
        }

        this.lastBumpLoadAt = now;
        this.lastBumpCenter = [lat, lng];

        try {
            const res = await fetch('/api/bumps/nearby', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ latitude: lat, longitude: lng, radius })
            });

            if (!res.ok) {
                console.warn('Failed to load nearby bumps', res.status);
                return;
            }

            const data = await res.json();

            // remove existing markers from cluster group
            if (this.clusterGroup) {
                this.clusterGroup.clearLayers();
            } else {
                this.bumpMarkers.forEach(m => this.map.removeLayer(m));
            }
            this.bumpMarkers = [];

            const bumps = data.nearby || data.bumps || [];
            bumps.forEach(b => this.addBumpMarker(b));
        } catch (err) {
            console.error('Error loading bumps', err);
        }
    }

    // Decide whether to reload bumps based on movement or time
    maybeLoadBumps() {
        const [lat, lng] = this.userLocation;
        if (!this.lastBumpCenter) {
            this.loadBumps(lat, lng);
            return;
        }

        const moved = this.haversineDistance(lat, lng, this.lastBumpCenter[0], this.lastBumpCenter[1]);
        if (moved >= this.BUMP_LOAD_MOVE_THRESHOLD) {
            this.loadBumps(lat, lng);
            return;
        }

        const now = Date.now();
        if (now - this.lastBumpLoadAt >= this.BUMP_LOAD_INTERVAL_MS) {
            this.loadBumps(lat, lng);
        }
    }

    init() {
        this.map = L.map('map').setView(this.userLocation, 13);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(this.map);

        // Create a marker cluster group for performance with many markers
        this.clusterGroup = L.markerClusterGroup({
            chunkedLoading: true,
            removeOutsideVisibleBounds: true,
            showCoverageOnHover: false,
            maxClusterRadius: 60
        });
        this.map.addLayer(this.clusterGroup);

        // Load bumps near initial user location
        this.loadBumps(this.userLocation[0], this.userLocation[1], 500);

        // Start GPS tracking
        this.startGPSTracking();

        // Update stats
        this.updateStats();

        // Add click event to add new bumps
        this.map.on('click', (e) => {
            if (confirm('{{ __("messages.Add a bump") }} هنا؟')) {
                this.addNewBump(e.latlng.lat, e.latlng.lng);
            }
        });

        // Setup controls
        this.setupControls();

        // Refresh bumps every 30 seconds (using current user location)
        setInterval(() => this.maybeLoadBumps(), 30000);
    }

    setupControls() {
        document.getElementById('start-tracking').addEventListener('click', () => {
            this.isTracking = true;
            document.getElementById('start-tracking').disabled = true;
            document.getElementById('stop-tracking').disabled = false;
            if (this.userLocation) {
                this.map.setView(this.userLocation, 16);
            }
        });

        document.getElementById('stop-tracking').addEventListener('click', () => {
            this.isTracking = false;
            document.getElementById('stop-tracking').disabled = true;
            document.getElementById('start-tracking').disabled = false;
        });

        document.getElementById('alert-distance').addEventListener('change', (e) => {
            this.alertDistance = parseInt(e.target.value);
        });
    }

    loadBumps() {
        // Deprecated: use loadBumps(lat, lng, radius)
        this.loadBumps(this.userLocation[0], this.userLocation[1], 500);
    }

    loadBumps(lat, lng, radius = 500) {
        // throttle by time
        const now = Date.now();
        if (now - this.lastBumpLoadAt < this.BUMP_LOAD_INTERVAL_MS) return;

        // remember center
        this.lastBumpCenter = [lat, lng];
        this.lastBumpLoadAt = now;

        fetch('/api/bumps/nearby', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ latitude: lat, longitude: lng, radius: radius })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear existing markers from cluster group for performance
                if (this.clusterGroup) {
                    this.clusterGroup.clearLayers();
                } else {
                    this.bumpMarkers.forEach(marker => this.map.removeLayer(marker));
                }
                this.bumpMarkers = [];

                // API returns `nearby` array
                const bumps = data.nearby || data.bumps || [];
                bumps.forEach(bump => {
                    this.addBumpMarker(bump);
                });

                this.updateStats();
            }
        })
        .catch(error => console.error('Error loading bumps:', error));
    }

    maybeLoadBumps() {
        if (!this.userLocation) return;
        const [lat, lng] = this.userLocation;

        if (!this.lastBumpCenter) {
            this.loadBumps(lat, lng, 500);
            return;
        }

        const dist = this.map.distance(this.lastBumpCenter, [lat, lng]);
        const now = Date.now();
        if (dist > this.BUMP_LOAD_MOVE_THRESHOLD || (now - this.lastBumpLoadAt) > this.BUMP_LOAD_INTERVAL_MS) {
            this.loadBumps(lat, lng, 500);
        }
    }

    addBumpMarker(bump) {
        // choose color by source / confirmed state
        let markerColor = '#6b7280'; // default gray
        let iconHtml = '📍';

        if (bump.is_verified) {
            markerColor = '#ef4444'; // confirmed = red
            iconHtml = '📍';
        } else if (bump.source === 'predicted') {
            markerColor = '#f59e0b'; // predicted = orange
            iconHtml = '⚠️';
        } else if (bump.source === 'user') {
            markerColor = '#3b82f6'; // user = blue
            iconHtml = '📌';
        } else if (bump.source === 'osm') {
            markerColor = '#6ee7b7'; // osm = light green
            iconHtml = '📍';
        }

        const icon = L.divIcon({
            html: `<div style="background-color: ${markerColor}; border: 2px solid white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">${iconHtml}</div>`,
            className: 'bump-marker',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });

        const marker = L.marker([parseFloat(bump.latitude), parseFloat(bump.longitude)], { icon })
            .bindPopup(`
                <div style="direction: rtl; text-align: right; padding: 8px;">
                    <h4 style="margin: 0 0 8px 0;">مطب سرعة</h4>
                    <p style="margin: 4px 0;"><strong>المصدر:</strong> ${bump.source || 'unknown'}</p>
                    <p style="margin: 4px 0;"><strong>الثقة:</strong> ${bump.confidence || 'N/A'}</p>
                    <p style="margin: 4px 0;"><strong>تاريخ الإنشاء:</strong> ${bump.created_at || '—'}</p>
                    <p style="margin: 4px 0;"><strong>التقارير:</strong> ${bump.reports_count || 0}</p>
                    ${bump.description ? `<p style="margin: 4px 0;"><strong>الوصف:</strong> ${bump.description}</p>` : ''}
                    <div style="margin-top: 8px;">
                        <button onclick="mapInstance.reportBump(${bump.id}, 'confirm')" class="btn btn-success" style="font-size: 12px; padding: 4px 8px;">تأكيد</button>
                        <button onclick="mapInstance.reportBump(${bump.id}, 'false_positive')" class="btn btn-danger" style="font-size: 12px; padding: 4px 8px; margin-left: 4px;">خطأ</button>
                    </div>
                </div>
            `);

        // attach bump id to marker for alert tracking
        marker.bumpId = bump.id;
        // normalize confidence level (support numeric or string)
        let confLevel = 'medium';
        if (bump.confidence_level) {
            confLevel = bump.confidence_level;
        } else if (typeof bump.confidence !== 'undefined') {
            const n = Number(bump.confidence);
            if (!isNaN(n)) {
                confLevel = n >= 80 ? 'high' : (n >= 60 ? 'medium' : 'low');
            } else {
                confLevel = String(bump.confidence);
            }
        }
        marker.confidence = confLevel;
        marker.bumpData = bump;

        // add to cluster group if available for performance
        if (this.clusterGroup) {
            this.clusterGroup.addLayer(marker);
        } else {
            marker.addTo(this.map);
        }

        this.bumpMarkers.push(marker);
    }

    addNewBump(lat, lng) {
        fetch('/api/bumps', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                latitude: lat,
                longitude: lng,
                description: 'مطب جديد'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.loadBumps(); // Reload all bumps
                alert('{{ __("messages.Success") }}: {{ __("messages.Speed bump added successfully") }}');
            }
        })
        .catch(error => console.error('Error adding bump:', error));
    }

    reportBump(bumpId, type) {
        fetch('/api/reports', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                speed_bump_id: bumpId,
                report_type: type
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.loadBumps(); // Reload bumps to update status
                alert('{{ __("messages.Success") }}: {{ __("messages.Report submitted") }}');
            }
        })
        .catch(error => console.error('Error reporting bump:', error));
    }

    startGPSTracking() {
        if (navigator.geolocation) {
            this.watchId = navigator.geolocation.watchPosition(
                (position) => {
                    this.userLocation = [position.coords.latitude, position.coords.longitude];

                    // Update user marker
                    if (this.userMarker) {
                        this.map.removeLayer(this.userMarker);
                    }

                    const userIcon = L.divIcon({
                        html: '<div style="background-color: #3b82f6; border: 3px solid white; border-radius: 50%; width: 20px; height: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></div>',
                        className: 'user-marker',
                        iconSize: [20, 20],
                        iconAnchor: [10, 10]
                    });

                    this.userMarker = L.marker(this.userLocation, { icon: userIcon })
                        .addTo(this.map)
                        .bindPopup('{{ __("messages.Your location") }}');

                    // Check for nearby bumps
                    this.checkNearbyBumps();

                    // Possibly reload bump data when user moves
                    this.maybeLoadBumps();

                    // Center map on user if tracking is active
                    if (this.isTracking) {
                        this.map.setView(this.userLocation, 16);
                    }
                },
                (error) => {
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

    checkNearbyBumps() {
        const now = Date.now();
        if (now - this.lastAlertTime < this.ALERT_COOLDOWN) return;

        this.bumpMarkers.forEach(marker => {
            const bumpLatLng = marker.getLatLng();
            // use Haversine distance for consistency
            const distance = this.haversineDistance(this.userLocation[0], this.userLocation[1], bumpLatLng.lat, bumpLatLng.lng);

            const bumpId = marker.bumpId;

            // If we've already alerted for this bump, only rearm if user moved away more than REARM_DISTANCE
            if (this.alertedMarkers.includes(bumpId)) {
                if (distance > this.REARM_DISTANCE) {
                    // user moved away enough to allow future alerts for this bump
                    this.alertedMarkers = this.alertedMarkers.filter(id => id !== bumpId);
                }
                return; // already alerted and not rearmed yet
            }

            // If within alert distance and not alerted before, trigger alert
            if (distance <= this.alertDistance) {
                this.showAlert('{{ __("messages.Warning") }}: {{ __("messages.Speed bump ahead") }}! (' + Math.round(distance) + ' {{ __("messages.meters") }})');
                this.playAlertSound(marker.confidence);
                // vibration for mobile devices
                if (navigator.vibrate) {
                    if (marker.confidence === 'high') {
                        navigator.vibrate([200, 100, 200]);
                    } else if (marker.confidence === 'medium') {
                        navigator.vibrate(150);
                    } else {
                        navigator.vibrate(70);
                    }
                }
                this.lastAlertTime = now;
                this.updateAlertCount();
                this.alertedMarkers.push(bumpId);
            }
        });
    }

    // Haversine formula to compute distance in meters
    haversineDistance(lat1, lon1, lat2, lon2) {
        const toRad = (v) => v * Math.PI / 180;
        const R = 6371000; // meters
        const dLat = toRad(lat2 - lat1);
        const dLon = toRad(lon2 - lon1);
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return Math.round(R * c);
    }

    // Play a short alert beep using Web Audio API with strength by confidence
    playAlertSound(level = 'medium') {
        try {
            if (!this._audioCtx) {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                this._audioCtx = new AudioCtx();
            }

            const ctx = this._audioCtx;
            const o = ctx.createOscillator();
            const g = ctx.createGain();
            o.type = 'sine';

            // choose frequency and peak gain by level
            let freq = 660; // medium
            let peak = 0.15;
            if (level === 'high') {
                freq = 1000;
                peak = 0.45;
            } else if (level === 'medium') {
                freq = 660;
                peak = 0.18;
            } else {
                freq = 440;
                peak = 0.08;
            }

            o.frequency.value = freq;
            g.gain.value = 0.001;
            o.connect(g);
            g.connect(ctx.destination);

            const now = ctx.currentTime;
            g.gain.setValueAtTime(0.001, now);
            g.gain.exponentialRampToValueAtTime(Math.max(0.001, peak), now + 0.02);
            o.start(now);
            g.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
            o.stop(now + 0.36);
        } catch (e) {
            // Fallback: try playing a simple alert using DOM Audio if WebAudio unavailable
            try {
                const a = new Audio();
                a.src = 'data:audio/wav;base64,UklGRiQAAABXQVZFZm10IBAAAAABAAEAESsAACJWAAACABAAZGF0YRAAAAAA';
                a.play().catch(() => {});
            } catch (ex) {
                // ignore
            }
        }
    }

    showAlert(message) {
        document.getElementById('alert-content').innerHTML = message;
        document.getElementById('alert-box').style.display = 'block';

        // Auto hide after 5 seconds
        setTimeout(() => {
            this.closeAlert();
        }, 5000);
    }

    closeAlert() {
        document.getElementById('alert-box').style.display = 'none';
    }

    updateStats() {
        fetch('/api/stats', {
            headers: {
                'Accept': 'application/json'
            }
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

    updateAlertCount() {
        const current = parseInt(document.getElementById('alert-count').textContent) || 0;
        document.getElementById('alert-count').textContent = current + 1;
    }
}

// Global instance
let mapInstance;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    mapInstance = new SpeedBumpMap();
    mapInstance.init();
});

// Global functions for popup buttons
function closeAlert() {
    mapInstance.closeAlert();
}